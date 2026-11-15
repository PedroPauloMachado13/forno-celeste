<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Catalog;

use App\DTOs\Catalog\StoreRecipeData;
use App\DTOs\Catalog\UpdateRecipeData;
use App\Enums\Catalog\RecipeType;
use App\Models\Recipe;
use App\Repositories\Contracts\Catalog\RecipeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentRecipeRepository implements RecipeRepositoryInterface
{
    public function paginate(?RecipeType $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return Recipe::query()
            ->with('ingredients.ingredient')
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Recipe
    {
        return Recipe::query()
            ->with('ingredients.ingredient')
            ->findOrFail($id);
    }

    public function createWithIngredients(StoreRecipeData $data): Recipe
    {
        $recipe = Recipe::query()->create($data->toRecipeArray());

        $recipe->ingredients()->createMany($data->toIngredientLinesArray());

        return $recipe->load('ingredients.ingredient');
    }

    public function updateWithIngredients(Recipe $recipe, UpdateRecipeData $data): Recipe
    {
        $recipe->update($data->toRecipeArray());

        $recipe->ingredients()->delete();
        $recipe->ingredients()->createMany($data->toIngredientLinesArray());

        return $recipe->load('ingredients.ingredient');
    }

    public function delete(Recipe $recipe): void
    {
        $recipe->delete();
    }
}
