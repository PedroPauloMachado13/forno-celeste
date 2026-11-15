<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Sales;

use App\DTOs\Sales\CashWithdrawalLineData;
use App\DTOs\Sales\StoreDailyClosingData;
use App\DTOs\Sales\UpdateDailyClosingData;
use App\Models\DailyClosing;
use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentDailyClosingRepository implements DailyClosingRepositoryInterface
{
    /** @return LengthAwarePaginator<int, DailyClosing> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return DailyClosing::query()
            ->with(['withdrawals', 'wastes.product'])
            ->orderByDesc('closing_date')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): DailyClosing
    {
        return DailyClosing::query()
            ->with(['withdrawals', 'wastes.product'])
            ->findOrFail($id);
    }

    public function findLatestBefore(CarbonImmutable $date): ?DailyClosing
    {
        return DailyClosing::query()
            ->where('closing_date', '<', $date->toDateString())
            ->orderByDesc('closing_date')
            ->first();
    }

    /**
     * @param  array<string, int>  $computedFields
     * @param  list<array{product_id: int, quantity: int, unit_cost_cents: int, total_cost_cents: int}>  $wasteLines
     */
    public function createWithRelations(StoreDailyClosingData $data, array $computedFields, array $wasteLines): DailyClosing
    {
        $closing = DailyClosing::query()->create([
            'closing_date' => $data->closingDate,
            'card_sales_cents' => $data->cardSalesCents,
            'cash_drawer_cents' => $data->cashDrawerCents,
            ...$computedFields,
        ]);

        if ($data->withdrawals !== []) {
            $closing->withdrawals()->createMany(
                array_map(
                    static fn (CashWithdrawalLineData $line): array => $line->toArray(),
                    $data->withdrawals,
                ),
            );
        }

        if ($wasteLines !== []) {
            $closing->wastes()->createMany($wasteLines);
        }

        return $closing->load(['withdrawals', 'wastes.product']);
    }

    /**
     * @param  array<string, int>  $computedFields
     * @param  list<array{product_id: int, quantity: int, unit_cost_cents: int, total_cost_cents: int}>  $wasteLines
     */
    public function updateWithRelations(DailyClosing $closing, UpdateDailyClosingData $data, array $computedFields, array $wasteLines): DailyClosing
    {
        $closing->update([
            'card_sales_cents' => $data->cardSalesCents,
            'cash_drawer_cents' => $data->cashDrawerCents,
            ...$computedFields,
        ]);

        // Delete old withdrawals and insert new ones
        $closing->withdrawals()->delete();

        if ($data->withdrawals !== []) {
            $closing->withdrawals()->createMany(
                array_map(
                    static fn (CashWithdrawalLineData $line): array => $line->toArray(),
                    $data->withdrawals,
                ),
            );
        }

        // Delete old wastes and insert new ones
        $closing->wastes()->delete();

        if ($wasteLines !== []) {
            $closing->wastes()->createMany($wasteLines);
        }

        return $closing->load(['withdrawals', 'wastes.product']);
    }

    /** @param array<string, int> $computedFields */
    public function updateComputedFields(DailyClosing $closing, array $computedFields): void
    {
        $closing->update($computedFields);
    }

    public function delete(DailyClosing $closing): void
    {
        $closing->delete();
    }

    /** @return Collection<int, DailyClosing> */
    public function findAllAfter(CarbonImmutable $date): Collection
    {
        return DailyClosing::query()
            ->with('withdrawals')
            ->where('closing_date', '>', $date->toDateString())
            ->orderBy('closing_date')
            ->get();
    }

    /** @return Collection<int, array{date: string, card_sales_cents: int, cash_sales_cents: int, total_revenue_cents: int}> */
    public function dailyRevenueBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return DailyClosing::query()
            ->whereBetween('closing_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('closing_date')
            ->get(['closing_date', 'card_sales_cents', 'cash_sales_cents', 'total_revenue_cents'])
            ->map(fn (DailyClosing $c): array => [
                'date' => $c->closing_date->toDateString(),
                'card_sales_cents' => $c->card_sales_cents,
                'cash_sales_cents' => $c->cash_sales_cents,
                'total_revenue_cents' => $c->total_revenue_cents,
            ]);
    }

    /** @return Collection<int, array{date: string, cash_drawer_cents: int}> */
    public function cashDrawerBalanceBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return DailyClosing::query()
            ->whereBetween('closing_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('closing_date')
            ->get(['closing_date', 'cash_drawer_cents'])
            ->map(fn (DailyClosing $c): array => [
                'date' => $c->closing_date->toDateString(),
                'cash_drawer_cents' => $c->cash_drawer_cents,
            ]);
    }

    /** @return array{total_revenue_cents: int, total_card_sales_cents: int, total_cash_sales_cents: int, total_waste_cents: int, closing_count: int} */
    public function periodSummary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $result = DailyClosing::query()
            ->whereBetween('closing_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COALESCE(SUM(total_revenue_cents), 0) as total_revenue_cents')
            ->selectRaw('COALESCE(SUM(card_sales_cents), 0) as total_card_sales_cents')
            ->selectRaw('COALESCE(SUM(cash_sales_cents), 0) as total_cash_sales_cents')
            ->selectRaw('COALESCE(SUM(total_waste_cents), 0) as total_waste_cents')
            ->selectRaw('COUNT(*) as closing_count')
            ->first();

        return [
            'total_revenue_cents' => (int) $result->total_revenue_cents,
            'total_card_sales_cents' => (int) $result->total_card_sales_cents,
            'total_cash_sales_cents' => (int) $result->total_cash_sales_cents,
            'total_waste_cents' => (int) $result->total_waste_cents,
            'closing_count' => (int) $result->closing_count,
        ];
    }

    /** @return array{best: ?array{date: string, total_revenue_cents: int}, worst: ?array{date: string, total_revenue_cents: int}} */
    public function bestAndWorstDays(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $best = DailyClosing::query()
            ->whereBetween('closing_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('total_revenue_cents')
            ->first(['closing_date', 'total_revenue_cents']);

        $worst = DailyClosing::query()
            ->whereBetween('closing_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('total_revenue_cents')
            ->first(['closing_date', 'total_revenue_cents']);

        return [
            'best' => $best ? [
                'date' => $best->closing_date->toDateString(),
                'total_revenue_cents' => $best->total_revenue_cents,
            ] : null,
            'worst' => $worst ? [
                'date' => $worst->closing_date->toDateString(),
                'total_revenue_cents' => $worst->total_revenue_cents,
            ] : null,
        ];
    }
}
