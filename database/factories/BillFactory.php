<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Finance\BillType;
use App\Models\Bill;
use App\Models\FixedCost;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
final class BillFactory extends Factory
{
    protected $model = Bill::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $referenceMonth = CarbonImmutable::now()->startOfMonth();
        $dueDate = $referenceMonth->addDays(fake()->numberBetween(5, 28));

        return [
            'fixed_cost_id' => null,
            'bill_type' => fake()->randomElement(BillType::cases())->value,
            'description' => fake()->sentence(3),
            'amount_cents' => fake()->numberBetween(1000, 200000),
            'reference_month' => $referenceMonth->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'paid_at' => null,
        ];
    }

    public function forFixedCost(?FixedCost $fixedCost = null): static
    {
        return $this->state(fn (): array => [
            'fixed_cost_id' => $fixedCost?->id ?? FixedCost::factory(),
            'bill_type' => BillType::Fixed->value,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'paid_at' => CarbonImmutable::now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'due_date' => CarbonImmutable::now()->subDays(5)->toDateString(),
            'paid_at' => null,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (): array => [
            'due_date' => CarbonImmutable::now()->addDays(3)->toDateString(),
            'paid_at' => null,
        ]);
    }
}
