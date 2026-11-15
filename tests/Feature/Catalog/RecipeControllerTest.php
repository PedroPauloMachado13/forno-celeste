<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\Catalog\MeasurementUnit;
use App\Enums\Catalog\RecipeType;
use Database\Factories\IngredientFactory;
use Database\Factories\RecipeFactory;
use Database\Factories\RecipeIngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200(): void
    {
        RecipeFactory::new()->count(2)->create();

        $response = $this->get(route('receitas.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Recipes/Index')
            ->has('recipes')
        );
    }

    public function test_store_creates_recipe_with_ingredients(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();

        $response = $this->post(route('receitas.store'), [
            'name' => 'Massa Base de Pao',
            'type' => RecipeType::Base->value,
            'yield_quantity' => 10,
            'packaging_cost_per_unit_cents' => 0,
            'is_experimental' => false,
            'ingredients' => [
                [
                    'ingredient_id' => $ingredient->id,
                    'quantity' => '500',
                    'unit' => MeasurementUnit::Grams->value,
                    'note' => null,
                ],
            ],
        ]);

        $response->assertRedirect(route('receitas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recipes', [
            'name' => 'Massa Base de Pao',
            'type' => RecipeType::Base->value,
        ]);

        $this->assertDatabaseHas('recipe_ingredients', [
            'ingredient_id' => $ingredient->id,
            'quantity' => '500.0000',
        ]);
    }

    public function test_store_requires_at_least_one_ingredient(): void
    {
        $response = $this->post(route('receitas.store'), [
            'name' => 'Receita Vazia',
            'type' => RecipeType::Base->value,
            'yield_quantity' => 10,
            'ingredients' => [],
        ]);

        $response->assertSessionHasErrors(['ingredients']);
    }

    public function test_update_modifies_recipe(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create();
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 100.0,
        ]);

        $newIngredient = IngredientFactory::new()->grams()->create();

        $response = $this->put(route('receitas.update', $recipe), [
            'name' => 'Receita Atualizada',
            'type' => RecipeType::Base->value,
            'yield_quantity' => 20,
            'packaging_cost_per_unit_cents' => 10,
            'is_experimental' => false,
            'ingredients' => [
                [
                    'ingredient_id' => $newIngredient->id,
                    'quantity' => '300',
                    'unit' => MeasurementUnit::Grams->value,
                    'note' => 'updated',
                ],
            ],
        ]);

        $response->assertRedirect(route('receitas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'name' => 'Receita Atualizada',
            'yield_quantity' => 20,
        ]);
    }

    public function test_destroy_deletes_recipe(): void
    {
        $recipe = RecipeFactory::new()->create();

        $response = $this->delete(route('receitas.destroy', $recipe));

        $response->assertRedirect(route('receitas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id,
        ]);
    }

    public function test_show_returns_200_with_recipe_data(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $response = $this->get(route('receitas.show', $recipe));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Recipes/Show')
            ->has('recipe')
            ->has('recipe_cost_cents')
        );
    }
}
