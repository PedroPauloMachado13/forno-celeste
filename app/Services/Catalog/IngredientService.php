<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\StoreIngredientData;
use App\DTOs\Catalog\UpdateIngredientData;
use App\Models\Ingredient;
use App\Repositories\Contracts\Catalog\IngredientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IngredientService
{
    public function __construct(
        private readonly IngredientRepositoryInterface $ingredients,
    ) {}

    /** @return LengthAwarePaginator<int, Ingredient> */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->ingredients->paginate($perPage);
    }

    public function find(int $id): Ingredient
    {
        return $this->ingredients->findOrFail($id);
    }

    public function create(StoreIngredientData $data): Ingredient
    {
        return $this->ingredients->create($data);
    }

    public function update(Ingredient $ingredient, UpdateIngredientData $data): Ingredient
    {
        return $this->ingredients->update($ingredient, $data);
    }

    public function delete(Ingredient $ingredient): void
    {
        $this->ingredients->delete($ingredient);
    }

    /**
     * Calculate the cost per unit (cents per measurement unit).
     *
     * Example: if a 1000g package costs 560 cents, cost per gram = 560 / 1000 = 0.56 cents/gram.
     */
    public function costPerUnit(Ingredient $ingredient): float
    {
        $packageQuantity = (float) $ingredient->package_quantity;

        if ($packageQuantity <= 0.0) {
            return 0.0;
        }

        return $ingredient->package_cost_cents / $packageQuantity;
    }
}
