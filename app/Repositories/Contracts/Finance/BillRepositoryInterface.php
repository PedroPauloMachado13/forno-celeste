<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Finance;

use App\DTOs\Finance\StoreBillData;
use App\DTOs\Finance\UpdateBillData;
use App\Models\Bill;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BillRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Bill> */
    public function paginate(int $perPage = 20, ?string $referenceMonth = null): LengthAwarePaginator;

    public function findOrFail(int $id): Bill;

    public function create(StoreBillData $data): Bill;

    public function update(Bill $bill, UpdateBillData $data): Bill;

    public function delete(Bill $bill): void;

    public function markAsPaid(Bill $bill, CarbonImmutable $paidAt): Bill;

    public function markAsUnpaid(Bill $bill): Bill;

    /**
     * Count bills that are due soon (within next 7 days) and not yet paid.
     */
    public function countDueSoon(): int;

    /**
     * Count bills that are overdue (past due_date) and not yet paid.
     */
    public function countOverdue(): int;

    /**
     * Sum of amount_cents for bills with due_date in the given period.
     */
    public function totalAmountBetween(CarbonImmutable $from, CarbonImmutable $to): int;

    /**
     * Next N unpaid bills ordered by due date.
     *
     * @return Collection<int, Bill>
     */
    public function upcomingUnpaid(int $limit = 10): Collection;

    /**
     * Total amount of all unpaid bills.
     */
    public function totalUnpaidAmount(): int;

    /**
     * Daily bill totals grouped by due_date for a given period.
     *
     * @return Collection<int, array{date: string, total_cents: int}>
     */
    public function dailyBillsBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;

    /**
     * Bill totals grouped by bill_type for a given period.
     *
     * @return Collection<int, array{bill_type: string, total_cents: int}>
     */
    public function totalsByTypeBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;

    /**
     * Cost breakdown: fixed-cost bills grouped by fixed_cost name,
     * non-fixed bills grouped by bill_type.
     *
     * @return Collection<int, array{key: string, label: string, total_cents: int}>
     */
    public function costBreakdownBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;
}
