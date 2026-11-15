<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\DTOs\Finance\StoreHolidayData;
use App\DTOs\Finance\UpdateHolidayData;
use App\Models\Holiday;
use App\Repositories\Contracts\Finance\HolidayRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class HolidayService
{
    public function __construct(
        private readonly HolidayRepositoryInterface $holidays,
    ) {}

    /** @return LengthAwarePaginator<int, Holiday> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->holidays->paginate($perPage);
    }

    public function create(StoreHolidayData $data): Holiday
    {
        return $this->holidays->create($data);
    }

    public function update(Holiday $holiday, UpdateHolidayData $data): Holiday
    {
        return $this->holidays->update($holiday, $data);
    }

    public function delete(Holiday $holiday): void
    {
        $this->holidays->delete($holiday);
    }

    /** @return Collection<int, Holiday> */
    public function findBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $this->holidays->findBetween($from, $to);
    }
}
