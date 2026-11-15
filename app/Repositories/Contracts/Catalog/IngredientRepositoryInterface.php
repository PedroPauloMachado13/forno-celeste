<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Catalog;

use App\DTOs\Catalog\StoreIngredientData;
use App\DTOs\Catalog\UpdateIngredientData;
use App\Models\Ingredient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IngredientRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Ingredient> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): Ingredient;

    public function create(StoreIngredientData $data): Ingredient;

    public function update(Ingredient $ingredient, UpdateIngredientData $data): Ingredient;

    public function delete(Ingredient $ingredient): void;
}
