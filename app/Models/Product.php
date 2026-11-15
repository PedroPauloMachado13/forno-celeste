<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Product extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'category_id',
        'recipe_id',
        'packaging_id',
        'sale_price_cents',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'recipe_id' => 'integer',
            'packaging_id' => 'integer',
            'sale_price_cents' => 'integer',
        ];
    }

    /** @return BelongsTo<ProductCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /** @return BelongsTo<Recipe, $this> */
    public function baseRecipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    /** @return BelongsTo<Packaging, $this> */
    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class);
    }

    /** @return BelongsToMany<Recipe, $this> */
    public function subRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'product_sub_recipes')
            ->withPivot(['quantity', 'created_at']);
    }
}
