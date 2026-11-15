<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Finance;

use App\DTOs\Finance\StoreHolidayData;
use App\DTOs\Finance\UpdateHolidayData;
use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface HolidayRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Holiday> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): Holiday;

    public function create(StoreHolidayData $data): Holiday;

    public function update(Holiday $holiday, UpdateHolidayData $data): Holiday;

    public function delete(Holiday $holiday): void;

    /** @return Collection<int, Holiday> */
    public function findBetween(CarbonImmutable $from, CarbonImmutable $to): Collection;
}
