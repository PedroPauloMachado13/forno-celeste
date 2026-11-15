<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\DTOs\Finance\StoreBillData;
use App\DTOs\Finance\UpdateBillData;
use App\Models\Bill;
use App\Repositories\Contracts\Finance\BillRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BillService
{
    public function __construct(
        private readonly BillRepositoryInterface $bills,
    ) {}

    /** @return LengthAwarePaginator<int, Bill> */
    public function paginate(int $perPage = 20, ?string $referenceMonth = null): LengthAwarePaginator
    {
        return $this->bills->paginate($perPage, $referenceMonth);
    }

    public function find(int $id): Bill
    {
        return $this->bills->findOrFail($id);
    }

    public function create(StoreBillData $data): Bill
    {
        return $this->bills->create($data);
    }

    public function update(Bill $bill, UpdateBillData $data): Bill
    {
        return $this->bills->update($bill, $data);
    }

    public function delete(Bill $bill): void
    {
        $this->bills->delete($bill);
    }

    public function markAsPaid(Bill $bill): Bill
    {
        return $this->bills->markAsPaid($bill, CarbonImmutable::now());
    }

    public function markAsUnpaid(Bill $bill): Bill
    {
        return $this->bills->markAsUnpaid($bill);
    }

    /** @return \Illuminate\Support\Collection<int, Bill> */
    public function upcomingUnpaid(int $limit = 20): \Illuminate\Support\Collection
    {
        return $this->bills->upcomingUnpaid($limit);
    }
}
