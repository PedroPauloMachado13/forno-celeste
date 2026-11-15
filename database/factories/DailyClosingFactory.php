<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DailyClosing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyClosing>
 */
final class DailyClosingFactory extends Factory
{
    protected $model = DailyClosing::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $openingCash = fake()->numberBetween(0, 50000);
        $cashSales = fake()->numberBetween(5000, 30000);
        $cardSales = fake()->numberBetween(5000, 30000);
        $cashDrawer = $openingCash + $cashSales;

        return [
            'closing_date' => fake()->unique()->date(),
            'card_sales_cents' => $cardSales,
            'cash_drawer_cents' => $cashDrawer,
            'opening_cash_cents' => $openingCash,
            'cash_sales_cents' => $cashSales,
            'total_revenue_cents' => $cardSales + $cashSales,
            'total_waste_cents' => 0,
        ];
    }
}
