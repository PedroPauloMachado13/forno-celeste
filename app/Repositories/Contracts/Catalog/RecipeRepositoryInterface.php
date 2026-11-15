<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Catalog;

use App\DTOs\Catalog\StoreRecipeData;
use App\DTOs\Catalog\UpdateRecipeData;
use App\Enums\Catalog\RecipeType;
use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RecipeRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Recipe> */
    public function paginate(?RecipeType $type = null, int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): Recipe;

    public function createWithIngredients(StoreRecipeData $data): Recipe;

    public function updateWithIngredients(Recipe $recipe, UpdateRecipeData $data): Recipe;

    public function delete(Recipe $recipe): void;
}
