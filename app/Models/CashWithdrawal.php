<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashWithdrawal extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'amount_cents',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<DailyClosing, $this> */
    public function dailyClosing(): BelongsTo
    {
        return $this->belongsTo(DailyClosing::class);
    }
}
