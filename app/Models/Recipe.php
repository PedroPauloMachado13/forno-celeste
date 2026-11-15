<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Catalog\RecipeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Recipe extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'type',
        'yield_quantity',
        'is_experimental',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => RecipeType::class,
            'yield_quantity' => 'integer',
            'is_experimental' => 'boolean',
        ];
    }

    /** @return HasMany<RecipeIngredient, $this> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'recipe_id');
    }

    /** @return HasMany<ProductSubRecipe, $this> */
    public function productSubRecipes(): HasMany
    {
        return $this->hasMany(ProductSubRecipe::class);
    }
}
