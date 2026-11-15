<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Catalog\MeasurementUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Ingredient extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'package_quantity',
        'unit',
        'package_cost_cents',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'package_quantity' => 'decimal:4',
            'package_cost_cents' => 'integer',
            'unit' => MeasurementUnit::class,
        ];
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
