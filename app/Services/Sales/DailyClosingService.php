<?php

declare(strict_types=1);

namespace App\Services\Sales;

use App\DTOs\Sales\CashWithdrawalLineData;
use App\DTOs\Sales\StoreDailyClosingData;
use App\DTOs\Sales\UpdateDailyClosingData;
use App\DTOs\Sales\WasteLineData;
use App\Models\DailyClosing;
use App\Repositories\Contracts\Catalog\ProductRepositoryInterface;
use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use App\Services\Catalog\CmvCalculatorService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class DailyClosingService
{
    public function __construct(
        private readonly DailyClosingRepositoryInterface $closings,
        private readonly ProductRepositoryInterface $products,
        private readonly CmvCalculatorService $cmvCalculator,
    ) {}

    /** @return LengthAwarePaginator<int, DailyClosing> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->closings->paginate($perPage);
    }

    public function findWithRelations(int $id): DailyClosing
    {
        return $this->closings->findOrFail($id);
    }

    /**
     * Get the opening cash for a given date (previous closing's cash_drawer_cents, or 0).
     */
    public function getOpeningCash(CarbonImmutable $date): int
    {
        $previous = $this->closings->findLatestBefore($date);

        return $previous?->cash_drawer_cents ?? 0;
    }

    /**
     * Get the data needed for the create form: opening cash, default date, and products list with CMV.
     *
     * @return array{opening_cash_cents: int, default_date: string, products: array}
     */
    public function getCreateFormData(): array
    {
        $today = CarbonImmutable::today();

        return [
            'opening_cash_cents' => $this->getOpeningCash($today),
            'default_date' => $today->toDateString(),
            'products' => $this->getProductsWithCmv(),
        ];
    }

    /**
     * Get all products with their CMV for waste line dropdowns.
     *
     * @return list<array{id: int, name: string, cmv_cents: int}>
     */
    public function getProductsWithCmv(): array
    {
        return $this->products->listAllWithRecipes()
            ->map(fn ($product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'cmv_cents' => $this->cmvCalculator->calculateForProduct($product)->cmvCents,
            ])
            ->all();
    }

    public function create(StoreDailyClosingData $data): DailyClosing
    {
        return DB::transaction(function () use ($data): DailyClosing {
            $wasteLines = $this->buildWasteLines($data->wastes);
            $totalWasteCents = array_sum(array_column($wasteLines, 'total_cost_cents'));

            $computedFields = $this->computeFields(
                openingCash: $this->getOpeningCash($data->closingDate),
                cashDrawerCents: $data->cashDrawerCents,
                cardSalesCents: $data->cardSalesCents,
                withdrawals: $data->withdrawals,
                totalWasteCents: $totalWasteCents,
            );

            $closing = $this->closings->createWithRelations($data, $computedFields, $wasteLines);

            $this->recalculateForward($data->closingDate);

            return $closing;
        });
    }

    public function update(DailyClosing $closing, UpdateDailyClosingData $data): DailyClosing
    {
        return DB::transaction(function () use ($closing, $data): DailyClosing {
            $wasteLines = $this->buildWasteLines($data->wastes);
            $totalWasteCents = array_sum(array_column($wasteLines, 'total_cost_cents'));

            // Opening cash stays the same since date is immutable
            $computedFields = $this->computeFields(
                openingCash: $closing->opening_cash_cents,
                cashDrawerCents: $data->cashDrawerCents,
                cardSalesCents: $data->cardSalesCents,
                withdrawals: $data->withdrawals,
                totalWasteCents: $totalWasteCents,
            );

            $updated = $this->closings->updateWithRelations($closing, $data, $computedFields, $wasteLines);

            $this->recalculateForward($closing->closing_date);

            return $updated;
        });
    }

    public function delete(DailyClosing $closing): void
    {
        $closingDate = $closing->closing_date;

        DB::transaction(function () use ($closing, $closingDate): void {
            $this->closings->delete($closing);

            $this->recalculateForward($closingDate);
        });
    }

    /**
     * Build waste line arrays with snapshotted CMV costs.
     *
     * @param  list<WasteLineData>  $wastes
     * @return list<array{product_id: int, quantity: int, unit_cost_cents: int, total_cost_cents: int}>
     */
    private function buildWasteLines(array $wastes): array
    {
        $lines = [];

        foreach ($wastes as $waste) {
            $product = $this->products->findWithRecipes($waste->productId);
            $cmvResult = $this->cmvCalculator->calculateForProduct($product);
            $unitCostCents = $cmvResult->cmvCents;
            $totalCostCents = $unitCostCents * $waste->quantity;

            $lines[] = [
                'product_id' => $waste->productId,
                'quantity' => $waste->quantity,
                'unit_cost_cents' => $unitCostCents,
                'total_cost_cents' => $totalCostCents,
            ];
        }

        return $lines;
    }

    /**
     * Compute the derived monetary fields for a closing.
     *
     * @param  list<CashWithdrawalLineData>  $withdrawals
     * @return array<string, int>
     */
    private function computeFields(int $openingCash, int $cashDrawerCents, int $cardSalesCents, array $withdrawals, int $totalWasteCents = 0): array
    {
        $totalWithdrawals = array_sum(array_map(
            static fn (CashWithdrawalLineData $w): int => $w->amountCents,
            $withdrawals,
        ));

        $cashSalesCents = $cashDrawerCents - $openingCash + $totalWithdrawals;
        $totalRevenueCents = $cardSalesCents + $cashSalesCents;

        return [
            'opening_cash_cents' => $openingCash,
            'cash_sales_cents' => $cashSalesCents,
            'total_revenue_cents' => $totalRevenueCents,
            'total_waste_cents' => $totalWasteCents,
        ];
    }

    /**
     * Recalculate all closings after the given date.
     * Their opening_cash changes when a previous closing's cash_drawer changes.
     */
    private function recalculateForward(CarbonImmutable $afterDate): void
    {
        $subsequentClosings = $this->closings->findAllAfter($afterDate);

        if ($subsequentClosings->isEmpty()) {
            return;
        }

        // Seed the carry with the closing just before the first subsequent one
        $previousClosing = $this->closings->findLatestBefore($subsequentClosings->first()->closing_date);
        $previousCashDrawerCents = $previousClosing?->cash_drawer_cents ?? 0;

        foreach ($subsequentClosings as $closing) {
            $withdrawalLines = $closing->withdrawals->map(
                fn ($w): CashWithdrawalLineData => new CashWithdrawalLineData(
                    amountCents: $w->amount_cents,
                    description: $w->description,
                ),
            )->all();

            $computedFields = $this->computeFields(
                openingCash: $previousCashDrawerCents,
                cashDrawerCents: $closing->cash_drawer_cents,
                cardSalesCents: $closing->card_sales_cents,
                withdrawals: $withdrawalLines,
                totalWasteCents: $closing->total_waste_cents,
            );

            $this->closings->updateComputedFields($closing, $computedFields);

            // Carry forward for the next iteration
            $previousCashDrawerCents = $closing->cash_drawer_cents;
        }
    }
}
