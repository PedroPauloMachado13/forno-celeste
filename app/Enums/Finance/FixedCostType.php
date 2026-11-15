<?php

declare(strict_types=1);

namespace App\Enums\Finance;

enum FixedCostType: string
{
    case FixedAmount = 'fixed_amount';
    case Percentage = 'percentage';
}
