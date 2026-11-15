<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Catalog\MeasurementUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RecipeIngredient extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit',
        'note',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'recipe_id' => 'integer',
            'ingredient_id' => 'integer',
            'quantity' => 'decimal:4',
            'unit' => MeasurementUnit::class,
        ];
    }

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
