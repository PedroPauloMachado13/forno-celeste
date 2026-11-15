<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Catalog;

use App\DTOs\Catalog\StorePackagingData;
use App\DTOs\Catalog\UpdatePackagingData;
use App\Models\Packaging;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PackagingRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Packaging> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /** @return Collection<int, Packaging> */
    public function all(): Collection;

    public function findOrFail(int $id): Packaging;

    public function create(StorePackagingData $data): Packaging;

    public function update(Packaging $packaging, UpdatePackagingData $data): Packaging;

    public function delete(Packaging $packaging): void;
}
