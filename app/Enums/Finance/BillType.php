<?php

declare(strict_types=1);

namespace App\Enums\Finance;

enum BillType: string
{
    case Fixed = 'fixed';
    case Supplier = 'supplier';
    case Personal = 'personal';
    case Tax = 'tax';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixo',
            self::Supplier => 'Fornecedor',
            self::Personal => 'Pessoal',
            self::Tax => 'Imposto',
            self::Other => 'Outro',
        };
    }
}
