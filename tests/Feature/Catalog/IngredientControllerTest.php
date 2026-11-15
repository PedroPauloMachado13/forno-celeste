<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\Catalog\MeasurementUnit;
use Database\Factories\IngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IngredientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_with_inertia_page(): void
    {
        IngredientFactory::new()->count(3)->create();

        $response = $this->get(route('ingredientes.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Ingredients/Index')
            ->has('ingredients')
        );
    }

    public function test_create_returns_200(): void
    {
        $response = $this->get(route('ingredientes.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Ingredients/Create')
        );
    }

    public function test_store_creates_ingredient_and_redirects(): void
    {
        $response = $this->post(route('ingredientes.store'), [
            'name' => 'Farinha de Trigo',
            'package_quantity' => '1000',
            'unit' => MeasurementUnit::Grams->value,
            'package_cost_cents' => 560,
        ]);

        $response->assertRedirect(route('ingredientes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Farinha de Trigo',
            'package_cost_cents' => 560,
        ]);
    }

    public function test_store_with_invalid_data_returns_validation_errors(): void
    {
        $response = $this->post(route('ingredientes.store'), [
            'name' => '',
            'package_quantity' => '',
            'unit' => 'invalid',
            'package_cost_cents' => -1,
        ]);

        $response->assertSessionHasErrors(['name', 'package_quantity', 'unit', 'package_cost_cents']);
    }

    public function test_store_with_duplicate_name_returns_validation_error(): void
    {
        IngredientFactory::new()->create(['name' => 'Farinha de Trigo']);

        $response = $this->post(route('ingredientes.store'), [
            'name' => 'Farinha de Trigo',
            'package_quantity' => '1000',
            'unit' => MeasurementUnit::Grams->value,
            'package_cost_cents' => 560,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_edit_returns_200(): void
    {
        $ingredient = IngredientFactory::new()->create();

        $response = $this->get(route('ingredientes.edit', $ingredient));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Catalog/Ingredients/Edit')
            ->has('ingredient')
        );
    }

    public function test_update_modifies_ingredient_and_redirects(): void
    {
        $ingredient = IngredientFactory::new()->create();

        $response = $this->put(route('ingredientes.update', $ingredient), [
            'name' => 'Farinha Atualizada',
            'package_quantity' => '2000',
            'unit' => MeasurementUnit::Grams->value,
            'package_cost_cents' => 1100,
        ]);

        $response->assertRedirect(route('ingredientes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Farinha Atualizada',
            'package_cost_cents' => 1100,
        ]);
    }

    public function test_destroy_deletes_ingredient_and_redirects(): void
    {
        $ingredient = IngredientFactory::new()->create();

        $response = $this->delete(route('ingredientes.destroy', $ingredient));

        $response->assertRedirect(route('ingredientes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ingredients', [
            'id' => $ingredient->id,
        ]);
    }
}
