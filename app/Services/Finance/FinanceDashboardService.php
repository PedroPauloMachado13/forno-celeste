<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Enums\Finance\FixedCostType;
use App\Repositories\Contracts\Finance\BillRepositoryInterface;
use App\Repositories\Contracts\Finance\FixedCostRepositoryInterface;
use App\Repositories\Contracts\Finance\HolidayRepositoryInterface;
use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use Carbon\CarbonImmutable;

final class FinanceDashboardService
{
    public function __construct(
        private readonly DailyClosingRepositoryInterface $closings,
        private readonly BillRepositoryInterface $bills,
        private readonly FixedCostRepositoryInterface $fixedCosts,
        private readonly HolidayRepositoryInterface $holidays,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getSummary(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $from ??= CarbonImmutable::now()->startOfMonth();
        $to ??= CarbonImmutable::now();

        $periodSummary = $this->closings->periodSummary($from, $to);

        $billsTotalCents = $this->bills->totalAmountBetween($from, $to);
        $wasteCents = $periodSummary['total_waste_cents'];
        $cardFeeCents = $this->calculateCardFeeCents($periodSummary['total_card_sales_cents']);
        $costsTotalCents = $billsTotalCents + $wasteCents + $cardFeeCents;

        $revenueTotalCents = $periodSummary['total_revenue_cents'];
        $profitCents = $revenueTotalCents - $costsTotalCents;
        $profitPercent = $revenueTotalCents > 0
            ? round(($profitCents / $revenueTotalCents) * 100, 2)
            : null;

        $closingCount = $periodSummary['closing_count'];
        $breakEven = $this->calculateBreakEven($revenueTotalCents, $costsTotalCents, $closingCount);
        $comparison = $this->calculateComparison($from, $to, $revenueTotalCents, $costsTotalCents);
        $bestWorst = $this->closings->bestAndWorstDays($from, $to);
        $holidays = $this->holidays->findBetween($from, $to);

        // Forecast / predictability
        $pendingBillsTotal = $this->bills->totalUnpaidAmount();
        $dailyAvg = $breakEven['daily_avg_revenue_cents'];
        $daysToCover = $dailyAvg > 0 ? (int) ceil($pendingBillsTotal / $dailyAvg) : null;
        $revenueNeeded = max(0, $pendingBillsTotal - max(0, $profitCents));

        $upcomingBills = $this->bills->upcomingUnpaid();

        return [
            'revenue' => [
                'total_cents' => $revenueTotalCents,
                'card_sales_cents' => $periodSummary['total_card_sales_cents'],
                'cash_sales_cents' => $periodSummary['total_cash_sales_cents'],
                'closing_count' => $closingCount,
            ],
            'costs' => [
                'bills_total_cents' => $billsTotalCents,
                'waste_cents' => $wasteCents,
                'card_fee_cents' => $cardFeeCents,
                'total_cents' => $costsTotalCents,
                'by_type' => $this->bills->totalsByTypeBetween($from, $to)->toArray(),
                'breakdown' => $this->bills->costBreakdownBetween($from, $to)->toArray(),
            ],
            'profit_cents' => $profitCents,
            'profit_percent' => $profitPercent,
            'break_even' => $breakEven,
            'comparison' => $comparison,
            'best_day' => $bestWorst['best'] ?? null,
            'worst_day' => $bestWorst['worst'] ?? null,
            'holidays_in_period' => $holidays,
            'forecast' => [
                'pending_bills_total_cents' => $pendingBillsTotal,
                'daily_avg_revenue_cents' => $dailyAvg,
                'days_to_cover_bills' => $daysToCover,
                'revenue_needed_cents' => $revenueNeeded,
            ],
            'upcoming_bills' => $upcomingBills,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    /**
     * Calculate card fee based on active percentage-type fixed costs.
     */
    private function calculateCardFeeCents(int $cardSalesCents): int
    {
        $percentageCosts = $this->fixedCosts->allActiveByType(FixedCostType::Percentage);

        $totalFeeCents = 0;

        foreach ($percentageCosts as $cost) {
            $percentage = (float) $cost->percentage_value;
            $totalFeeCents += (int) round($cardSalesCents * $percentage / 100);
        }

        return $totalFeeCents;
    }

    /**
     * @return array{reached: bool, days_remaining: int, daily_avg_revenue_cents: int}
     */
    private function calculateBreakEven(int $revenueTotalCents, int $costsTotalCents, int $closingCount): array
    {
        $remaining = $costsTotalCents - $revenueTotalCents;
        $reached = $remaining <= 0;

        $dailyAvg = $closingCount > 0
            ? (int) round($revenueTotalCents / $closingCount)
            : 0;

        $daysRemaining = 0;
        if (! $reached && $dailyAvg > 0) {
            $daysRemaining = (int) ceil($remaining / $dailyAvg);
        }

        return [
            'reached' => $reached,
            'days_remaining' => $daysRemaining,
            'daily_avg_revenue_cents' => $dailyAvg,
        ];
    }

    /**
     * @return array{current: array{revenue_cents: int, costs_cents: int, profit_cents: int}, previous: array{revenue_cents: int, costs_cents: int, profit_cents: int}, revenue_change_percent: ?float, profit_change_percent: ?float}
     */
    private function calculateComparison(CarbonImmutable $from, CarbonImmutable $to, int $currentRevenue, int $currentCosts): array
    {
        $durationDays = $from->diffInDays($to);

        $previousTo = $from->subDay();
        $previousFrom = $previousTo->subDays($durationDays);

        $currentProfit = $currentRevenue - $currentCosts;

        $previousSummary = $this->closings->periodSummary($previousFrom, $previousTo);
        $previousBills = $this->bills->totalAmountBetween($previousFrom, $previousTo);
        $previousCardFee = $this->calculateCardFeeCents($previousSummary['total_card_sales_cents']);
        $previousCosts = $previousBills + $previousSummary['total_waste_cents'] + $previousCardFee;
        $previousRevenue = $previousSummary['total_revenue_cents'];
        $previousProfit = $previousRevenue - $previousCosts;

        $revenueChangePercent = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 2)
            : null;

        $profitChangePercent = $previousProfit !== 0
            ? round((($currentProfit - $previousProfit) / abs($previousProfit)) * 100, 2)
            : null;

        return [
            'current' => [
                'revenue_cents' => $currentRevenue,
                'costs_cents' => $currentCosts,
                'profit_cents' => $currentProfit,
            ],
            'previous' => [
                'revenue_cents' => $previousRevenue,
                'costs_cents' => $previousCosts,
                'profit_cents' => $previousProfit,
            ],
            'revenue_change_percent' => $revenueChangePercent,
            'profit_change_percent' => $profitChangePercent,
        ];
    }
}
