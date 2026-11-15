<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CashWithdrawal;
use App\Models\DailyClosing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashWithdrawal>
 */
final class CashWithdrawalFactory extends Factory
{
    protected $model = CashWithdrawal::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'daily_closing_id' => DailyClosingFactory::new(),
            'amount_cents' => fake()->numberBetween(500, 10000),
            'description' => fake()->sentence(3),
        ];
    }

    public function forClosing(DailyClosing $closing): static
    {
        return $this->state(fn (): array => [
            'daily_closing_id' => $closing->id,
        ]);
    }
}
