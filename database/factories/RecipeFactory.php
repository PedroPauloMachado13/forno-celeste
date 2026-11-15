<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Catalog\RecipeType;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
final class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomNumber(4),
            'type' => RecipeType::Base,
            'yield_quantity' => fake()->numberBetween(1, 50),
            'is_experimental' => false,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (): array => [
            'type' => RecipeType::Base,
        ]);
    }

    public function sub(): static
    {
        return $this->state(fn (): array => [
            'type' => RecipeType::Sub,
        ]);
    }

    public function experimental(): static
    {
        return $this->state(fn (): array => [
            'is_experimental' => true,
        ]);
    }
}
