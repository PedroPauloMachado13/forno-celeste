<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Packaging;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Packaging>
 */
final class PackagingFactory extends Factory
{
    protected $model = Packaging::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomNumber(4),
            'cost_cents' => fake()->numberBetween(10, 500),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
