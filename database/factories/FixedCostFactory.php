<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Finance\FixedCostType;
use App\Models\FixedCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FixedCost>
 */
final class FixedCostFactory extends Factory
{
    protected $model = FixedCost::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => FixedCostType::FixedAmount,
            'default_amount_cents' => fake()->numberBetween(1000, 200000),
            'percentage_value' => null,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function percentage(float $value = 3.15): static
    {
        return $this->state(fn (): array => [
            'type' => FixedCostType::Percentage,
            'default_amount_cents' => null,
            'percentage_value' => $value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
