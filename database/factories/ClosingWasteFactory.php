<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClosingWaste;
use App\Models\DailyClosing;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClosingWaste>
 */
final class ClosingWasteFactory extends Factory
{
    protected $model = ClosingWaste::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitCostCents = fake()->numberBetween(100, 5000);

        return [
            'daily_closing_id' => DailyClosing::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_cost_cents' => $unitCostCents,
            'total_cost_cents' => $quantity * $unitCostCents,
        ];
    }
}
