<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\DTOs\Finance\StoreFixedCostData;
use App\DTOs\Finance\UpdateFixedCostData;
use App\Models\FixedCost;
use App\Repositories\Contracts\Finance\FixedCostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class FixedCostService
{
    public function __construct(
        private readonly FixedCostRepositoryInterface $fixedCosts,
    ) {}

    /** @return LengthAwarePaginator<int, FixedCost> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->fixedCosts->paginate($perPage);
    }

    /** @return Collection<int, FixedCost> */
    public function allActive(): Collection
    {
        return $this->fixedCosts->allActive();
    }

    public function create(StoreFixedCostData $data): FixedCost
    {
        return $this->fixedCosts->create($data);
    }

    public function update(FixedCost $fixedCost, UpdateFixedCostData $data): FixedCost
    {
        return $this->fixedCosts->update($fixedCost, $data);
    }

    public function delete(FixedCost $fixedCost): void
    {
        $this->fixedCosts->delete($fixedCost);
    }
}
