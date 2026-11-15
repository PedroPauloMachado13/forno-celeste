<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Database\Factories\IngredientFactory;
use Database\Factories\PackagingFactory;
use Database\Factories\ProductCategoryFactory;
use Database\Factories\ProductFactory;
use Database\Factories\RecipeFactory;
use Database\Factories\RecipeIngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        ProductFactory::new()->create(['recipe_id' => $recipe->id]);

        $response = $this->get(route('produtos.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Index')
            ->has('products')
        );
    }

    public function test_store_creates_product_with_sub_recipes(): void
    {
        $category = ProductCategoryFactory::new()->create();
        $ingredient = IngredientFactory::new()->grams()->create();

        $baseRecipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $baseRecipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $subRecipe = RecipeFactory::new()->sub()->create(['yield_quantity' => 5]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $subRecipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 100.0,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 50]);

        $response = $this->post(route('produtos.store'), [
            'name' => 'Pao de Queijo',
            'category_id' => $category->id,
            'recipe_id' => $baseRecipe->id,
            'packaging_id' => $packaging->id,
            'sale_price_cents' => 1200,
            'sub_recipes' => [
                ['recipe_id' => $subRecipe->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('produtos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Pao de Queijo',
            'category_id' => $category->id,
            'recipe_id' => $baseRecipe->id,
            'sale_price_cents' => 1200,
        ]);

        $this->assertDatabaseHas('product_sub_recipes', [
            'recipe_id' => $subRecipe->id,
            'quantity' => 2,
        ]);
    }

    public function test_show_returns_200_with_cmv_data(): void
    {
        $ingredient = IngredientFactory::new()->grams(1000.0, 1000)->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);

        $response = $this->get(route('produtos.show', $product));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Products/Show')
            ->has('product')
            ->has('pricing_scenarios')
        );
    }

    public function test_update_modifies_product(): void
    {
        $category = ProductCategoryFactory::new()->create();
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'category_id' => $category->id,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 100]);

        $response = $this->put(route('produtos.update', $product), [
            'name' => 'Produto Atualizado',
            'category_id' => $category->id,
            'recipe_id' => $recipe->id,
            'packaging_id' => $packaging->id,
            'sale_price_cents' => 2000,
            'sub_recipes' => [],
        ]);

        $response->assertRedirect(route('produtos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produto Atualizado',
            'sale_price_cents' => 2000,
        ]);
    }

    public function test_destroy_deletes_product(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $product = ProductFactory::new()->create(['recipe_id' => $recipe->id]);

        $response = $this->delete(route('produtos.destroy', $product));

        $response->assertRedirect(route('produtos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('produtos.store'), []);

        $response->assertSessionHasErrors(['name', 'category_id', 'recipe_id', 'packaging_id', 'sale_price_cents']);
    }

    public function test_store_validates_recipe_is_base_type(): void
    {
        $category = ProductCategoryFactory::new()->create();
        $subRecipe = RecipeFactory::new()->sub()->create();

        $response = $this->post(route('produtos.store'), [
            'name' => 'Produto Invalido',
            'category_id' => $category->id,
            'recipe_id' => $subRecipe->id,
            'sale_price_cents' => 1000,
        ]);

        $response->assertSessionHasErrors(['recipe_id']);
    }
}
