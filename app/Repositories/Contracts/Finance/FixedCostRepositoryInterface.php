<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Finance;

use App\DTOs\Finance\StoreFixedCostData;
use App\DTOs\Finance\UpdateFixedCostData;
use App\Enums\Finance\FixedCostType;
use App\Models\FixedCost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FixedCostRepositoryInterface
{
    /** @return LengthAwarePaginator<int, FixedCost> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): FixedCost;

    public function create(StoreFixedCostData $data): FixedCost;

    public function update(FixedCost $fixedCost, UpdateFixedCostData $data): FixedCost;

    public function delete(FixedCost $fixedCost): void;

    /** @return Collection<int, FixedCost> */
    public function allActive(): Collection;

    /** @return Collection<int, FixedCost> */
    public function allActiveByType(FixedCostType $type): Collection;
}
