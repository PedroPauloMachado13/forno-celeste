<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Finance;

use App\DTOs\Finance\StoreFixedCostData;
use App\DTOs\Finance\UpdateFixedCostData;
use App\Enums\Finance\FixedCostType;
use App\Models\FixedCost;
use App\Repositories\Contracts\Finance\FixedCostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentFixedCostRepository implements FixedCostRepositoryInterface
{
    /** @return LengthAwarePaginator<int, FixedCost> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return FixedCost::query()
            ->withCount('bills')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): FixedCost
    {
        return FixedCost::query()->findOrFail($id);
    }

    public function create(StoreFixedCostData $data): FixedCost
    {
        return FixedCost::query()->create($data->toArray());
    }

    public function update(FixedCost $fixedCost, UpdateFixedCostData $data): FixedCost
    {
        $fixedCost->update($data->toArray());

        return $fixedCost;
    }

    public function delete(FixedCost $fixedCost): void
    {
        $fixedCost->delete();
    }

    /** @return Collection<int, FixedCost> */
    public function allActive(): Collection
    {
        return FixedCost::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, FixedCost> */
    public function allActiveByType(FixedCostType $type): Collection
    {
        return FixedCost::query()
            ->active()
            ->where('type', $type)
            ->orderBy('name')
            ->get();
    }
}
