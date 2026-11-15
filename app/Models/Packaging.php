<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Packaging extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'cost_cents',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
        ];
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
