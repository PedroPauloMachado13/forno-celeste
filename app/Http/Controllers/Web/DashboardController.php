<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\Finance\FixedCostType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\BillResource;
use App\Repositories\Contracts\Finance\BillRepositoryInterface;
use App\Repositories\Contracts\Finance\FixedCostRepositoryInterface;
use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use App\Services\Catalog\DashboardService;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly DailyClosingRepositoryInterface $closings,
        private readonly BillRepositoryInterface $bills,
        private readonly FixedCostRepositoryInterface $fixedCosts,
    ) {}

    public function __invoke(): Response
    {
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth();
        $thirtyDaysAgo = $now->subDays(30);

        // Existing catalog data (we only need cmv, not full counts)
        $catalogData = $this->dashboardService->getSummary();

        // Monthly financial summary (current month)
        $monthSummary = $this->closings->periodSummary($monthStart, $now);
        $monthBillsTotal = $this->bills->totalAmountBetween($monthStart, $now);

        // Calculate card fee for the month using percentage-based fixed costs
        $percentageCosts = $this->fixedCosts->allActiveByType(FixedCostType::Percentage);
        $cardFeeCents = 0;
        foreach ($percentageCosts as $cost) {
            $cardFeeCents += (int) round($monthSummary['total_card_sales_cents'] * (float) $cost->percentage_value / 100);
        }

        $monthCostsCents = $monthBillsTotal + $monthSummary['total_waste_cents'] + $cardFeeCents;
        $monthRevenueCents = $monthSummary['total_revenue_cents'];
        $monthProfitCents = $monthRevenueCents - $monthCostsCents;
        $monthProfitPercent = $monthRevenueCents > 0
            ? round(($monthProfitCents / $monthRevenueCents) * 100, 1)
            : null;

        // Pending bills
        $pendingBillsTotal = $this->bills->totalUnpaidAmount();
        $upcomingBills = $this->bills->upcomingUnpaid(5);
        $billsOverdueCount = $this->bills->countOverdue();

        // Forecast
        $dailyAvg = $monthSummary['closing_count'] > 0
            ? (int) round($monthRevenueCents / $monthSummary['closing_count'])
            : 0;
        $daysToCover = ($dailyAvg > 0 && $pendingBillsTotal > 0)
            ? (int) ceil($pendingBillsTotal / $dailyAvg)
            : null;
        $revenueNeeded = max(0, $pendingBillsTotal - max(0, $monthProfitCents));

        // Chart data (last 30 days)
        $dailyRevenue = $this->closings->dailyRevenueBetween($thirtyDaysAgo, $now);
        $dailyBills = $this->bills->dailyBillsBetween($thirtyDaysAgo, $now);

        // Last closing info
        $lastClosing = $this->closings->findLatestBefore($now->addDay());

        return Inertia::render('Dashboard', [
            // Monthly snapshot
            'month_revenue_cents' => $monthRevenueCents,
            'month_profit_cents' => $monthProfitCents,
            'month_profit_percent' => $monthProfitPercent,
            'month_closing_count' => $monthSummary['closing_count'],
            'month_card_sales_cents' => $monthSummary['total_card_sales_cents'],
            'month_cash_sales_cents' => $monthSummary['total_cash_sales_cents'],

            // Pending bills
            'pending_bills_total_cents' => $pendingBillsTotal,
            'bills_overdue_count' => $billsOverdueCount,
            'upcoming_bills' => BillResource::collection($upcomingBills),

            // Forecast
            'forecast' => [
                'daily_avg_cents' => $dailyAvg,
                'days_to_cover_bills' => $daysToCover,
                'revenue_needed_cents' => $revenueNeeded,
            ],

            // CMV (keep existing)
            'cmv' => $catalogData['cmv'],

            // Chart data
            'daily_revenue' => $dailyRevenue,
            'daily_bills' => $dailyBills,

            // Last closing
            'last_closing' => $lastClosing ? [
                'date' => $lastClosing->closing_date->toDateString(),
                'total_revenue_cents' => $lastClosing->total_revenue_cents,
                'cash_drawer_cents' => $lastClosing->cash_drawer_cents,
            ] : null,

            // Month name for display
            'current_month' => $now->translatedFormat('F Y'),
        ]);
    }
}
