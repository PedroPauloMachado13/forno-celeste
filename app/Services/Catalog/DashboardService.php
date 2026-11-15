<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Repositories\Contracts\Catalog\IngredientRepositoryInterface;
use App\Repositories\Contracts\Catalog\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\Catalog\ProductRepositoryInterface;
use App\Repositories\Contracts\Catalog\RecipeRepositoryInterface;

final class DashboardService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly IngredientRepositoryInterface $ingredients,
        private readonly RecipeRepositoryInterface $recipes,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly CmvCalculatorService $cmvCalculator,
    ) {}

    /** @return array<string, mixed> */
    public function getSummary(): array
    {
        $products = $this->products->paginate(perPage: 999);

        $totalProducts = $products->total();
        $totalIngredients = $this->ingredients->paginate(perPage: 1)->total();
        $totalRecipes = $this->recipes->paginate(type: null, perPage: 1)->total();
        $totalCategories = $this->categories->all()->count();

        // Calculate CMV for all products
        /** @var list<array{name: string, cmv_percent: float, cmv_cents: int, sale_price_cents: int, health: string}> $cmvData */
        $cmvData = [];
        $healthCounts = ['healthy' => 0, 'tight' => 0, 'critical' => 0, 'unknown' => 0];
        $totalCmvPercent = 0.0;
        $productsWithCmv = 0;

        foreach ($products as $product) {
            $cmv = $this->cmvCalculator->calculateForProduct($product);

            if ($cmv->cmvPercent !== null) {
                $cmvData[] = [
                    'name' => $product->name,
                    'cmv_percent' => $cmv->cmvPercent,
                    'cmv_cents' => $cmv->cmvCents,
                    'sale_price_cents' => $product->sale_price_cents,
                    'health' => $cmv->healthStatus->value,
                ];
                $healthCounts[$cmv->healthStatus->value]++;
                $totalCmvPercent += $cmv->cmvPercent;
                $productsWithCmv++;
            } else {
                $healthCounts['unknown']++;
            }
        }

        $avgCmvPercent = $productsWithCmv > 0
            ? round($totalCmvPercent / $productsWithCmv, 1)
            : null;

        return [
            'counts' => [
                'products' => $totalProducts,
                'ingredients' => $totalIngredients,
                'recipes' => $totalRecipes,
                'categories' => $totalCategories,
            ],
            'cmv' => [
                'average_percent' => $avgCmvPercent,
                'health_counts' => $healthCounts,
                'products' => $cmvData,
            ],
        ];
    }
}
