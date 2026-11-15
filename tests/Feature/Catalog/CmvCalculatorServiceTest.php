<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Enums\Catalog\CmvHealthStatus;
use App\Enums\Catalog\MeasurementUnit;
use App\Services\Catalog\CmvCalculatorService;
use Database\Factories\IngredientFactory;
use Database\Factories\PackagingFactory;
use Database\Factories\ProductFactory;
use Database\Factories\RecipeFactory;
use Database\Factories\RecipeIngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CmvCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private CmvCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CmvCalculatorService::class);
    }

    public function test_calculate_cmv_for_recipe_with_base_recipe_only(): void
    {
        // Flour: 1000g package costs 560 cents
        $flour = IngredientFactory::new()->grams(1000.0, 560)->create();
        // Sugar: 1000g package costs 800 cents
        $sugar = IngredientFactory::new()->grams(1000.0, 800)->create();

        // Recipe: uses 500g flour + 200g sugar, yields 10 units
        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);

        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 500.0,
            'unit' => MeasurementUnit::Grams,
        ]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $sugar->id,
            'quantity' => 200.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $recipe->load('ingredients.ingredient');

        $costPerUnit = $this->service->calculateForRecipe($recipe);

        // Flour cost: (500 / 1000) * 560 = 280
        // Sugar cost: (200 / 1000) * 800 = 160
        // Total: 440 / 10 units = 44 cents per unit
        $this->assertSame(44, $costPerUnit);
    }

    public function test_calculate_cmv_for_product_with_base_recipe_and_sub_recipes(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 560)->create();
        $chocolate = IngredientFactory::new()->grams(500.0, 1200)->create();

        // Base recipe: uses 500g flour, yields 10
        $baseRecipe = RecipeFactory::new()->base()->create(['yield_quantity' => 10]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $baseRecipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 500.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        // Sub recipe: uses 100g chocolate, yields 5
        $subRecipe = RecipeFactory::new()->sub()->create(['yield_quantity' => 5]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $subRecipe->id,
            'ingredient_id' => $chocolate->id,
            'quantity' => 100.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 50]);

        // Product: sale_price = 1000 cents, packaging = 50 cents
        $product = ProductFactory::new()->create([
            'recipe_id' => $baseRecipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);
        $product->subRecipes()->attach($subRecipe->id);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        // Base recipe cost per unit: (500/1000)*560 = 280 / 10 = 28
        // Sub recipe cost per unit: (100/500)*1200 = 240 / 5 = 48
        // Ingredient cost: 28 + 48 = 76
        // CMV: 76 + 50 (packaging) = 126
        $this->assertSame(76, $result->ingredientCostCents);
        $this->assertSame(50, $result->packagingCostCents);
        $this->assertSame(126, $result->cmvCents);
    }

    public function test_cmv_percentage_is_correct(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 1]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 200.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        // Cost: (200/1000)*1000 = 200 / 1 = 200 cents
        // Sale price: 500 cents -> CMV% = 200/500*100 = 40%
        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 500,
            'packaging_id' => $packaging->id,
        ]);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        $this->assertSame(200, $result->cmvCents);
        $this->assertSame(40.0, $result->cmvPercent);
    }

    public function test_health_status_healthy_below_45_percent(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 1]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 100.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        // Cost: 100 cents, sale: 1000 cents => CMV% = 10% (healthy)
        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        $this->assertSame(CmvHealthStatus::Healthy, $result->healthStatus);
    }

    public function test_health_status_tight_between_45_and_60_percent(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 1]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 500.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        // Cost: 500 cents, sale: 1000 cents => CMV% = 50% (tight)
        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        $this->assertSame(CmvHealthStatus::Tight, $result->healthStatus);
    }

    public function test_health_status_critical_above_60_percent(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 1]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 700.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        // Cost: 700 cents, sale: 1000 cents => CMV% = 70% (critical)
        $product = ProductFactory::new()->create([
            'recipe_id' => $recipe->id,
            'sale_price_cents' => 1000,
            'packaging_id' => $packaging->id,
        ]);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        $this->assertSame(CmvHealthStatus::Critical, $result->healthStatus);
    }

    public function test_zero_sale_price_returns_null_cmv_percent(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 1]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 100.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $packaging = PackagingFactory::new()->create(['cost_cents' => 0]);

        $product = ProductFactory::new()->zeroPriced()->create([
            'recipe_id' => $recipe->id,
            'packaging_id' => $packaging->id,
        ]);
        $product->load(['baseRecipe.ingredients.ingredient', 'subRecipes.ingredients.ingredient', 'packaging']);

        $result = $this->service->calculateForProduct($product);

        $this->assertNull($result->cmvPercent);
        $this->assertSame(CmvHealthStatus::Critical, $result->healthStatus);
    }

    public function test_pricing_scenarios_calculation(): void
    {
        $cmvCents = 300; // 3.00 reais
        $cardFee = 3.2;

        $scenarios = $this->service->calculatePricingScenarios($cmvCents, $cardFee);

        $this->assertCount(3, $scenarios);

        // Aggressive: margin 45%, fee 3.2% => divisor = 1 - 0.45 - 0.032 = 0.518
        // Price = ceil(300 / 0.518) = ceil(579.15) = 580
        $this->assertSame('Agressivo', $scenarios[0]->name);
        $this->assertSame(45.0, $scenarios[0]->targetMarginPercent);
        $this->assertSame(580, $scenarios[0]->suggestedPriceCents);

        // Standard: margin 55%, fee 3.2% => divisor = 1 - 0.55 - 0.032 = 0.418
        // Price = ceil(300 / 0.418) = ceil(717.70) = 718
        $this->assertSame('Padrão', $scenarios[1]->name);
        $this->assertSame(55.0, $scenarios[1]->targetMarginPercent);
        $this->assertSame(718, $scenarios[1]->suggestedPriceCents);

        // Premium: margin 65%, fee 3.2% => divisor = 1 - 0.65 - 0.032 = 0.318
        // Price = ceil(300 / 0.318) = ceil(943.39) = 944
        $this->assertSame('Premium', $scenarios[2]->name);
        $this->assertSame(65.0, $scenarios[2]->targetMarginPercent);
        $this->assertSame(944, $scenarios[2]->suggestedPriceCents);
    }

    public function test_suggest_price_with_standard_inputs(): void
    {
        // cmv=300, margin=55%, fee=3.2% => divisor=0.418 => ceil(300/0.418)=718
        $price = $this->service->suggestPrice(300, 55.0, 3.2);

        $this->assertSame(718, $price);
    }

    public function test_suggest_price_returns_zero_when_divisor_is_non_positive(): void
    {
        // margin=100%, fee=3.2% => divisor = 1 - 1.0 - 0.032 = -0.032 (negative)
        $price = $this->service->suggestPrice(300, 100.0, 3.2);

        $this->assertSame(0, $price);
    }

    public function test_recipe_with_zero_yield_returns_zero_cost(): void
    {
        $flour = IngredientFactory::new()->grams(1000.0, 1000)->create();

        $recipe = RecipeFactory::new()->base()->create(['yield_quantity' => 0]);
        RecipeIngredientFactory::new()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $flour->id,
            'quantity' => 100.0,
            'unit' => MeasurementUnit::Grams,
        ]);

        $recipe->load('ingredients.ingredient');

        $cost = $this->service->calculateForRecipe($recipe);

        $this->assertSame(0, $cost);
    }
}
