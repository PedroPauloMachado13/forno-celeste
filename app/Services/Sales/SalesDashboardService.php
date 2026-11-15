<?php

declare(strict_types=1);

namespace App\Services\Sales;

use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class SalesDashboardService
{
    public function __construct(
        private readonly DailyClosingRepositoryInterface $closings,
    ) {}

    /**
     * Get dashboard summary data for the given period.
     *
     * @return array{
     *     daily_revenue: Collection,
     *     cash_drawer_balance: Collection,
     *     summary: array{total_revenue_cents: int, total_card_sales_cents: int, total_cash_sales_cents: int, total_waste_cents: int, closing_count: int},
     *     from: string,
     *     to: string,
     * }
     */
    public function getSummary(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $from ??= CarbonImmutable::now()->startOfMonth();
        $to ??= CarbonImmutable::now();

        return [
            'daily_revenue' => $this->closings->dailyRevenueBetween($from, $to),
            'cash_drawer_balance' => $this->closings->cashDrawerBalanceBetween($from, $to),
            'summary' => $this->closings->periodSummary($from, $to),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }
}
