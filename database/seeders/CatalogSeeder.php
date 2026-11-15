<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Catalog\RecipeType;
use App\Models\Ingredient;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

final class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIngredients();
        $this->seedPackagings();
        $this->seedRecipes();
        $this->seedCategoriesAndProducts();
    }

    private function seedIngredients(): void
    {
        $ingredients = json_decode(
            File::get(base_path('docs/seed-data/ingredients.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($ingredients as $item) {
            Ingredient::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'package_quantity' => $item['package_quantity'],
                    'unit' => $item['unit'],
                    'package_cost_cents' => (int) round($item['cost'] * 100),
                ],
            );
        }
    }

    private function seedPackagings(): void
    {
        $packagings = [
            ['name' => 'Embalagem Cookie', 'cost_cents' => 29],
            ['name' => 'Embalagem Focaccia', 'cost_cents' => 39],
            ['name' => 'Embalagem Pao Doce', 'cost_cents' => 47],
        ];

        foreach ($packagings as $item) {
            Packaging::query()->updateOrCreate(
                ['name' => $item['name']],
                ['cost_cents' => $item['cost_cents']],
            );
        }
    }

    private function seedRecipes(): void
    {
        $data = json_decode(
            File::get(base_path('docs/seed-data/recipes.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($data['base_recipes'] as $recipeData) {
            $this->createRecipe($recipeData, RecipeType::Base);
        }

        foreach ($data['sub_recipes'] as $recipeData) {
            $this->createRecipe($recipeData, RecipeType::Sub);
        }
    }

    private function createRecipe(array $recipeData, RecipeType $type): void
    {
        $recipe = Recipe::query()->updateOrCreate(
            ['name' => $recipeData['name']],
            [
                'type' => $type,
                'yield_quantity' => $recipeData['yield'],
                'is_experimental' => $recipeData['is_experimental'] ?? false,
            ],
        );

        // Delete existing ingredients and re-create for idempotency
        $recipe->ingredients()->delete();

        foreach ($recipeData['ingredients'] as $line) {
            $ingredient = Ingredient::query()->where('name', $line['name'])->first();

            if ($ingredient === null) {
                continue;
            }

            $recipe->ingredients()->create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $line['quantity'],
                'unit' => $line['unit'],
                'note' => $line['note'] ?? null,
            ]);
        }
    }

    private function seedCategoriesAndProducts(): void
    {
        $categories = [
            ['name' => 'Cookies', 'sort_order' => 1],
            ['name' => 'Pães Salgados', 'sort_order' => 2],
            ['name' => 'Pães Doces', 'sort_order' => 3],
        ];

        foreach ($categories as $cat) {
            ProductCategory::query()->firstOrCreate(
                ['name' => $cat['name']],
                ['sort_order' => $cat['sort_order']],
            );
        }

        $data = json_decode(
            File::get(base_path('docs/seed-data/recipes.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        foreach ($data['products'] as $productData) {
            $category = ProductCategory::query()->where('name', $productData['category'])->first();
            $baseRecipe = Recipe::query()->where('name', $productData['base_recipe'])->first();
            $packaging = Packaging::query()->where('name', $productData['packaging'])->first();

            if ($category === null || $baseRecipe === null || $packaging === null) {
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $category->id,
                    'recipe_id' => $baseRecipe->id,
                    'packaging_id' => $packaging->id,
                    'sale_price_cents' => (int) round($productData['sale_price'] * 100),
                ],
            );

            // Sync sub-recipes
            $subRecipeIds = [];
            foreach ($productData['sub_recipes'] as $subRecipeName) {
                $subRecipe = Recipe::query()->where('name', $subRecipeName)->first();
                if ($subRecipe !== null) {
                    $subRecipeIds[] = $subRecipe->id;
                }
            }

            $product->subRecipes()->sync($subRecipeIds);
        }
    }
}
