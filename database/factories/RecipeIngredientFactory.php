<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Catalog\MeasurementUnit;
use App\Models\RecipeIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeIngredient>
 */
final class RecipeIngredientFactory extends Factory
{
    protected $model = RecipeIngredient::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'recipe_id' => RecipeFactory::new(),
            'ingredient_id' => IngredientFactory::new(),
            'quantity' => fake()->randomFloat(2, 10, 500),
            'unit' => MeasurementUnit::Grams,
            'note' => null,
        ];
    }
}
