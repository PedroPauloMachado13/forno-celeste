<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Finance\FixedCostType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FixedCost extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'default_amount_cents',
        'percentage_value',
        'description',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => FixedCostType::class,
            'default_amount_cents' => 'integer',
            'percentage_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Bill, $this> */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /** @param Builder<FixedCost> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
