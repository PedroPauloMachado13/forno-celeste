<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DailyClosing extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'closing_date',
        'card_sales_cents',
        'cash_drawer_cents',
        'opening_cash_cents',
        'cash_sales_cents',
        'total_revenue_cents',
        'total_waste_cents',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'closing_date' => 'immutable_date',
            'card_sales_cents' => 'integer',
            'cash_drawer_cents' => 'integer',
            'opening_cash_cents' => 'integer',
            'cash_sales_cents' => 'integer',
            'total_revenue_cents' => 'integer',
            'total_waste_cents' => 'integer',
        ];
    }

    /** @return HasMany<CashWithdrawal, $this> */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(CashWithdrawal::class);
    }

    /** @return HasMany<ClosingWaste, $this> */
    public function wastes(): HasMany
    {
        return $this->hasMany(ClosingWaste::class);
    }
}
