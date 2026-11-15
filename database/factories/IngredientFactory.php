<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Catalog\MeasurementUnit;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
final class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomNumber(4),
            'package_quantity' => fake()->randomFloat(2, 100, 5000),
            'unit' => fake()->randomElement(MeasurementUnit::cases()),
            'package_cost_cents' => fake()->numberBetween(100, 50000),
        ];
    }

    public function grams(float $packageQuantity = 1000.0, int $packageCostCents = 1000): static
    {
        return $this->state(fn (): array => [
            'unit' => MeasurementUnit::Grams,
            'package_quantity' => $packageQuantity,
            'package_cost_cents' => $packageCostCents,
        ]);
    }

    public function units(float $packageQuantity = 12.0, int $packageCostCents = 600): static
    {
        return $this->state(fn (): array => [
            'unit' => MeasurementUnit::Units,
            'package_quantity' => $packageQuantity,
            'package_cost_cents' => $packageCostCents,
        ]);
    }
}
