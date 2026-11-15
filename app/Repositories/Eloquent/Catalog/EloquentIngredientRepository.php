<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Catalog;

use App\DTOs\Catalog\StoreIngredientData;
use App\DTOs\Catalog\UpdateIngredientData;
use App\Models\Ingredient;
use App\Repositories\Contracts\Catalog\IngredientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentIngredientRepository implements IngredientRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Ingredient::query()
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Ingredient
    {
        return Ingredient::query()->findOrFail($id);
    }

    public function create(StoreIngredientData $data): Ingredient
    {
        return Ingredient::query()->create($data->toArray());
    }

    public function update(Ingredient $ingredient, UpdateIngredientData $data): Ingredient
    {
        $ingredient->update($data->toArray());

        return $ingredient;
    }

    public function delete(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }
}
