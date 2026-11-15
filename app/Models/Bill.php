<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Finance\BillType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Bill extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'fixed_cost_id',
        'bill_type',
        'description',
        'amount_cents',
        'reference_month',
        'due_date',
        'paid_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bill_type' => BillType::class,
            'amount_cents' => 'integer',
            'due_date' => 'immutable_date',
            'reference_month' => 'immutable_date',
            'paid_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<FixedCost, $this> */
    public function fixedCost(): BelongsTo
    {
        return $this->belongsTo(FixedCost::class);
    }

    /**
     * Computed status: 'paid', 'overdue', or 'pending'.
     *
     * @return Attribute<string, never>
     */
    protected function status(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->paid_at !== null) {
                return 'paid';
            }

            if ($this->due_date === null) {
                return 'pending';
            }

            if ($this->due_date < CarbonImmutable::today()) {
                return 'overdue';
            }

            return 'pending';
        });
    }

    /**
     * Normalize reference_month to always be the first day of the month.
     */
    protected function referenceMonth(): Attribute
    {
        return Attribute::set(
            fn (mixed $value): ?string => $value !== null
                ? CarbonImmutable::parse($value)->startOfMonth()->toDateString()
                : null,
        );
    }
}
