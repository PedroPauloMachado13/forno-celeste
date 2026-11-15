<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ClosingWaste extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'quantity',
        'unit_cost_cents',
        'total_cost_cents',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_cost_cents' => 'integer',
            'total_cost_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<DailyClosing, $this> */
    public function dailyClosing(): BelongsTo
    {
        return $this->belongsTo(DailyClosing::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
