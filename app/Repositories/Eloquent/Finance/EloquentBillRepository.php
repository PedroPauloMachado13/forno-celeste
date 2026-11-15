<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Finance;

use App\DTOs\Finance\StoreBillData;
use App\DTOs\Finance\UpdateBillData;
use App\Models\Bill;
use App\Repositories\Contracts\Finance\BillRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentBillRepository implements BillRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Bill> */
    public function paginate(int $perPage = 20, ?string $referenceMonth = null): LengthAwarePaginator
    {
        $query = Bill::query()->with('fixedCost');

        if ($referenceMonth) {
            $query->where('reference_month', $referenceMonth);
        }

        return $query
            ->orderByRaw('due_date IS NULL, due_date DESC')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Bill
    {
        return Bill::query()
            ->with('fixedCost')
            ->findOrFail($id);
    }

    public function create(StoreBillData $data): Bill
    {
        return Bill::query()->create($data->toArray());
    }

    public function update(Bill $bill, UpdateBillData $data): Bill
    {
        $bill->update($data->toArray());

        return $bill;
    }

    public function delete(Bill $bill): void
    {
        $bill->delete();
    }

    public function markAsPaid(Bill $bill, CarbonImmutable $paidAt): Bill
    {
        $bill->update(['paid_at' => $paidAt]);

        return $bill;
    }

    public function markAsUnpaid(Bill $bill): Bill
    {
        $bill->update(['paid_at' => null]);

        return $bill;
    }

    public function countDueSoon(): int
    {
        $today = CarbonImmutable::today();
        $sevenDaysFromNow = $today->addDays(7);

        return Bill::query()
            ->whereNull('paid_at')
            ->whereBetween('due_date', [$today->toDateString(), $sevenDaysFromNow->toDateString()])
            ->count();
    }

    public function countOverdue(): int
    {
        return Bill::query()
            ->whereNull('paid_at')
            ->where('due_date', '<', CarbonImmutable::today()->toDateString())
            ->count();
    }

    public function totalAmountBetween(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) Bill::query()
            ->where(fn ($q) => $q
                ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                ->orWhere(fn ($q2) => $q2
                    ->whereNull('due_date')
                    ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$from->toDateString(), $to->toDateString()])
                )
            )
            ->sum('amount_cents');
    }

    /** @return Collection<int, Bill> */
    public function upcomingUnpaid(int $limit = 10): Collection
    {
        return Bill::query()
            ->with('fixedCost')
            ->whereNull('paid_at')
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit($limit)
            ->get();
    }

    public function totalUnpaidAmount(): int
    {
        return (int) Bill::query()
            ->whereNull('paid_at')
            ->sum('amount_cents');
    }

    /** @return Collection<int, array{date: string, total_cents: int}> */
    public function dailyBillsBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Bill::query()
            ->where(fn ($q) => $q
                ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                ->orWhere(fn ($q2) => $q2
                    ->whereNull('due_date')
                    ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$from->toDateString(), $to->toDateString()])
                )
            )
            ->selectRaw('DATE(COALESCE(due_date, paid_at, created_at)) as plot_date, SUM(amount_cents) as total_cents')
            ->groupBy('plot_date')
            ->orderBy('plot_date')
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->plot_date,
                'total_cents' => (int) $row->total_cents,
            ]);
    }

    /** @return Collection<int, array{bill_type: string, total_cents: int}> */
    public function totalsByTypeBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Bill::query()
            ->where(fn ($q) => $q
                ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                ->orWhere(fn ($q2) => $q2
                    ->whereNull('due_date')
                    ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$from->toDateString(), $to->toDateString()])
                )
            )
            ->selectRaw('bill_type, SUM(amount_cents) as total_cents')
            ->groupBy('bill_type')
            ->get()
            ->map(fn ($row): array => [
                'bill_type' => $row->bill_type->value,
                'label' => $row->bill_type->label(),
                'total_cents' => (int) $row->total_cents,
            ]);
    }

    /** @return Collection<int, array{key: string, label: string, total_cents: int}> */
    public function costBreakdownBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $dateFilter = fn ($q) => $q
            ->whereBetween('bills.due_date', [$from->toDateString(), $to->toDateString()])
            ->orWhere(fn ($q2) => $q2
                ->whereNull('bills.due_date')
                ->whereBetween(DB::raw('DATE(COALESCE(bills.paid_at, bills.created_at))'), [$from->toDateString(), $to->toDateString()])
            );

        // Fixed-cost bills: group by fixed_cost name
        $fixedCostBills = Bill::query()
            ->where($dateFilter)
            ->whereNotNull('bills.fixed_cost_id')
            ->join('fixed_costs', 'bills.fixed_cost_id', '=', 'fixed_costs.id')
            ->selectRaw('fixed_costs.name as label, SUM(bills.amount_cents) as total_cents')
            ->groupBy('fixed_costs.name')
            ->orderByDesc('total_cents')
            ->get()
            ->map(fn ($row): array => [
                'key' => 'fc_' . mb_strtolower(str_replace(' ', '_', $row->label)),
                'label' => $row->label,
                'total_cents' => (int) $row->total_cents,
                'category' => 'fixed',
            ]);

        // Fixed-type bills WITHOUT a fixed_cost_id (orphan fixed bills)
        $orphanFixed = Bill::query()
            ->where($dateFilter)
            ->where('bill_type', 'fixed')
            ->whereNull('fixed_cost_id')
            ->selectRaw('SUM(amount_cents) as total_cents')
            ->value('total_cents');

        // Non-fixed bills: group by bill_type
        $otherBills = Bill::query()
            ->where($dateFilter)
            ->where('bill_type', '!=', 'fixed')
            ->selectRaw('bill_type, SUM(amount_cents) as total_cents')
            ->groupBy('bill_type')
            ->orderByDesc('total_cents')
            ->get()
            ->map(fn ($row): array => [
                'key' => 'type_' . $row->bill_type->value,
                'label' => $row->bill_type->label(),
                'total_cents' => (int) $row->total_cents,
                'category' => 'variable',
            ]);

        $result = $fixedCostBills->merge($otherBills);

        if ($orphanFixed && (int) $orphanFixed > 0) {
            $result->push([
                'key' => 'fc_outros_fixos',
                'label' => 'Outros Fixos',
                'total_cents' => (int) $orphanFixed,
                'category' => 'fixed',
            ]);
        }

        return $result->sortByDesc('total_cents')->values();
    }
}
