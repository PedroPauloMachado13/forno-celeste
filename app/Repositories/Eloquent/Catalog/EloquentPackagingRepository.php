<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Catalog;

use App\DTOs\Catalog\StorePackagingData;
use App\DTOs\Catalog\UpdatePackagingData;
use App\Models\Packaging;
use App\Repositories\Contracts\Catalog\PackagingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentPackagingRepository implements PackagingRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Packaging::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @return Collection<int, Packaging> */
    public function all(): Collection
    {
        return Packaging::query()
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): Packaging
    {
        return Packaging::query()->findOrFail($id);
    }

    public function create(StorePackagingData $data): Packaging
    {
        return Packaging::query()->create($data->toArray());
    }

    public function update(Packaging $packaging, UpdatePackagingData $data): Packaging
    {
        $packaging->update($data->toArray());

        return $packaging;
    }

    public function delete(Packaging $packaging): void
    {
        $packaging->delete();
    }
}
