<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\StorePackagingData;
use App\DTOs\Catalog\UpdatePackagingData;
use App\Exceptions\Catalog\PackagingInUseException;
use App\Models\Packaging;
use App\Repositories\Contracts\Catalog\PackagingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class PackagingService
{
    public function __construct(
        private readonly PackagingRepositoryInterface $packagings,
    ) {}

    /** @return LengthAwarePaginator<int, Packaging> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->packagings->paginate($perPage);
    }

    /** @return Collection<int, Packaging> */
    public function all(): Collection
    {
        return $this->packagings->all();
    }

    public function find(int $id): Packaging
    {
        return $this->packagings->findOrFail($id);
    }

    public function create(StorePackagingData $data): Packaging
    {
        return $this->packagings->create($data);
    }

    public function update(Packaging $packaging, UpdatePackagingData $data): Packaging
    {
        return $this->packagings->update($packaging, $data);
    }

    public function delete(Packaging $packaging): void
    {
        if ($packaging->products()->exists()) {
            throw PackagingInUseException::for($packaging);
        }

        $this->packagings->delete($packaging);
    }
}
