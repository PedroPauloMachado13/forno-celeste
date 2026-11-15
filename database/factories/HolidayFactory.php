<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
final class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'name' => fake()->words(2, true),
            'was_open' => false,
        ];
    }

    public function wasOpen(): static
    {
        return $this->state(fn (): array => [
            'was_open' => true,
        ]);
    }
}
