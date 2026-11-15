<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Sales;

use App\DTOs\Sales\StoreDailyClosingData;
use App\DTOs\Sales\UpdateDailyClosingData;
use App\Models\DailyClosing;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DailyClosingRepositoryInterface
{
    /** @return LengthAwarePaginator<int, DailyClosing> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): DailyClosing;

    public function findLatestBefore(CarbonImmutable $date): ?DailyClosing;

    /**
     * @param  array<string, int>  $computedFields
     * @param  list<array{product_id: int, quantity: int, unit_cost_cents: int, total_cost_cents: int}>  $wasteLines
     */
    public function createWithRelations(StoreDailyClosingData $data, array $computedFields, array $wasteLines): DailyClosing;

    /**
     * @param  array<string, int>  $computedFields
     * @param  list<array{product_id: int, quantity: int, unit_cost_cents: int, total_cost_cents: int}>  $wasteLines
     */
    public function updateWithRelations(DailyClosing $closing, UpdateDailyClosingData $data, array $computedFields, array $wasteLines): DailyClosing;

    /** @param array<string, int> $computedFields */
    public function updateComputedFields(DailyClosing $closing, array $computedFields): void;

    public function delete(DailyClosing $closing): void;

    /** @return Collection<int, DailyClosing> */
    public function findAllAfter(CarbonImmutable $date): Collection;

    /**
     * @return Collection<int, array{date: string, card_sales_cents: int, cash_sales_cents: int, total_revenue_cents: int}>
     */
    public function dailyRevenueBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;

    /**
     * @return Collection<int, array{date: string, cash_drawer_cents: int}>
     */
    public function cashDrawerBalanceBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;

    /**
     * @return array{total_revenue_cents: int, total_card_sales_cents: int, total_cash_sales_cents: int, total_waste_cents: int, closing_count: int}
     */
    public function periodSummary(CarbonImmutable $from, CarbonImmutable $to): array;

    /**
     * Returns the days with highest and lowest total_revenue_cents in the range.
     *
     * @return array{best: ?array{date: string, total_revenue_cents: int}, worst: ?array{date: string, total_revenue_cents: int}}
     */
    public function bestAndWorstDays(CarbonImmutable $from, CarbonImmutable $to): array;
}
