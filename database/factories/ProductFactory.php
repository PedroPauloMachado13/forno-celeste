<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->randomNumber(4),
            'category_id' => ProductCategoryFactory::new(),
            'recipe_id' => RecipeFactory::new()->base(),
            'packaging_id' => PackagingFactory::new(),
            'sale_price_cents' => fake()->numberBetween(500, 5000),
        ];
    }

    public function withSalePrice(int $salePriceCents): static
    {
        return $this->state(fn (): array => [
            'sale_price_cents' => $salePriceCents,
        ]);
    }

    public function zeroPriced(): static
    {
        return $this->state(fn (): array => [
            'sale_price_cents' => 0,
        ]);
    }
}
