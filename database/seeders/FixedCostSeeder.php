<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Finance\FixedCostType;
use App\Models\FixedCost;
use Illuminate\Database\Seeder;

final class FixedCostSeeder extends Seeder
{
    public function run(): void
    {
        $costs = [
            ['name' => 'Aluguel', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 60000, 'percentage_value' => null],
            ['name' => 'Gás', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 30000, 'percentage_value' => null],
            ['name' => 'Luz', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 12000, 'percentage_value' => null],
            ['name' => 'Água', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 7500, 'percentage_value' => null],
            ['name' => 'Saco Kraft Entrega', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 6000, 'percentage_value' => null],
            ['name' => 'Produtos de Limpeza', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 20000, 'percentage_value' => null],
            ['name' => 'Internet', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 4000, 'percentage_value' => null],
            ['name' => 'Pro-Labore', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 155000, 'percentage_value' => null],
            ['name' => 'Adesivos', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 5500, 'percentage_value' => null],
            ['name' => 'Sacolas', 'type' => FixedCostType::FixedAmount, 'default_amount_cents' => 5000, 'percentage_value' => null],
            ['name' => 'Taxa Maquininha', 'type' => FixedCostType::Percentage, 'default_amount_cents' => null, 'percentage_value' => '3.15'],
        ];

        foreach ($costs as $cost) {
            FixedCost::query()->updateOrCreate(
                ['name' => $cost['name']],
                [
                    'type' => $cost['type'],
                    'default_amount_cents' => $cost['default_amount_cents'],
                    'percentage_value' => $cost['percentage_value'],
                ],
            );
        }
    }
}
