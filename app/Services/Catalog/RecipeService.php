<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\StoreRecipeData;
use App\DTOs\Catalog\UpdateRecipeData;
use App\Enums\Catalog\RecipeType;
use App\Models\Recipe;
use App\Repositories\Contracts\Catalog\RecipeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class RecipeService
{
    public function __construct(
        private readonly RecipeRepositoryInterface $recipes,
    ) {}

    /** @return LengthAwarePaginator<int, Recipe> */
    public function paginate(?RecipeType $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->recipes->paginate($type, $perPage);
    }

    public function find(int $id): Recipe
    {
        return $this->recipes->findOrFail($id);
    }

    public function create(StoreRecipeData $data): Recipe
    {
        return DB::transaction(
            fn (): Recipe => $this->recipes->createWithIngredients($data),
        );
    }

    public function update(Recipe $recipe, UpdateRecipeData $data): Recipe
    {
        return DB::transaction(
            fn (): Recipe => $this->recipes->updateWithIngredients($recipe, $data),
        );
    }

    public function delete(Recipe $recipe): void
    {
        $this->recipes->delete($recipe);
    }
}
