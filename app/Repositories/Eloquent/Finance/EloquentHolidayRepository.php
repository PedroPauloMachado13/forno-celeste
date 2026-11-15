<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Finance;

use App\DTOs\Finance\StoreHolidayData;
use App\DTOs\Finance\UpdateHolidayData;
use App\Models\Holiday;
use App\Repositories\Contracts\Finance\HolidayRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Holiday> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Holiday::query()
            ->orderByDesc('date')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Holiday
    {
        return Holiday::query()->findOrFail($id);
    }

    public function create(StoreHolidayData $data): Holiday
    {
        return Holiday::query()->create($data->toArray());
    }

    public function update(Holiday $holiday, UpdateHolidayData $data): Holiday
    {
        $holiday->update($data->toArray());

        return $holiday;
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
    }

    /** @return Collection<int, Holiday> */
    public function findBetween(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Holiday::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();
    }
}
