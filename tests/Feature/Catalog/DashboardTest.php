<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use Database\Factories\IngredientFactory;
use Database\Factories\PackagingFactory;
use Database\Factories\ProductFactory;
use Database\Factories\RecipeFactory;
use Database\Factories\RecipeIngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_200_with_expected_props(): void
    {
        $ingredient = IngredientFactory::new()->grams(1000.0, 1000)->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('cmv')
            ->has('month_revenue_cents')
            ->has('month_profit_cents')
            ->has('pending_bills_total_cents')
            ->has('forecast')
            ->has('upcoming_bills')
        );
    }

    public function test_dashboard_returns_cmv_data_for_products(): void
    {
        $ingredient = IngredientFactory::new()->grams()->create();
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 500.0,
        ]);

        ProductFactory::new()->count(3)->create(['recipe_id' => $recipe->id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('cmv.products', 3)
        );
    }

    public function test_dashboard_works_with_empty_database(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('cmv.average_percent', null)
            ->where('month_revenue_cents', 0)
            ->where('month_closing_count', 0)
            ->where('pending_bills_total_cents', 0)
        );
    }
}
