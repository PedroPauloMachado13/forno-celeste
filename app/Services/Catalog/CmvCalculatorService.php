<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\CmvResult;
use App\DTOs\Catalog\PricingScenario;
use App\Enums\Catalog\CmvHealthStatus;
use App\Models\Product;
use App\Models\Recipe;

final class CmvCalculatorService
{
    /**
     * Calculate the ingredient cost in cents for one unit of a recipe.
     *
     * Formula per ingredient line:
     *   line_cost = (quantity / package_quantity) * package_cost_cents
     * Total recipe cost per unit = sum(line_costs) / yield_quantity
     */
    public function calculateForRecipe(Recipe $recipe): int
    {
        $totalCostCents = 0.0;

        foreach ($recipe->ingredients as $line) {
            $ingredient = $line->ingredient;
            $packageQuantity = (float) $ingredient->package_quantity;

            if ($packageQuantity <= 0) {
                continue;
            }

            $lineCost = ((float) $line->quantity / $packageQuantity) * $ingredient->package_cost_cents;
            $totalCostCents += $lineCost;
        }

        $yieldQuantity = $recipe->yield_quantity;

        if ($yieldQuantity <= 0) {
            return 0;
        }

        return (int) round($totalCostCents / $yieldQuantity);
    }

    /**
     * Calculate full CMV for a product: base recipe + sub-recipes + packaging.
     */
    public function calculateForProduct(Product $product): CmvResult
    {
        $ingredientCostCents = $this->calculateForRecipe($product->baseRecipe);

        foreach ($product->subRecipes as $subRecipe) {
            $pivotQuantity = (int) ($subRecipe->pivot->quantity ?? 1);
            $ingredientCostCents += $this->calculateForRecipe($subRecipe) * $pivotQuantity;
        }

        $packagingCostCents = $product->packaging?->cost_cents ?? 0;
        $cmvCents = $ingredientCostCents + $packagingCostCents;

        $cmvPercent = null;
        if ($product->sale_price_cents > 0) {
            $cmvPercent = round(($cmvCents / $product->sale_price_cents) * 100, 1);
        }

        $healthStatus = $this->determineHealthStatus($cmvPercent);

        return new CmvResult(
            ingredientCostCents: $ingredientCostCents,
            packagingCostCents: $packagingCostCents,
            cmvCents: $cmvCents,
            cmvPercent: $cmvPercent,
            healthStatus: $healthStatus,
        );
    }

    /**
     * Suggest a sale price given the CMV, target margin and card machine fee.
     *
     * Formula: suggested_price = cmv / (1 - margin - fee)
     */
    public function suggestPrice(int $cmvCents, float $targetMarginPercent, float $cardMachineFeePercent): int
    {
        $divisor = 1.0 - ($targetMarginPercent / 100.0) - ($cardMachineFeePercent / 100.0);

        if ($divisor <= 0) {
            return 0;
        }

        return (int) ceil($cmvCents / $divisor);
    }

    /**
     * Calculate pricing scenarios at 45%, 55%, 65% margins.
     *
     * @return list<PricingScenario>
     */
    public function calculatePricingScenarios(int $cmvCents, float $cardMachineFeePercent): array
    {
        $scenarios = [
            ['name' => 'Agressivo', 'margin' => 45.0],
            ['name' => 'Padrão', 'margin' => 55.0],
            ['name' => 'Premium', 'margin' => 65.0],
        ];

        $result = [];

        foreach ($scenarios as $scenario) {
            $suggestedPriceCents = $this->suggestPrice($cmvCents, $scenario['margin'], $cardMachineFeePercent);

            $feeAmountCents = (int) round($suggestedPriceCents * ($cardMachineFeePercent / 100.0));
            $marginPerUnitCents = $suggestedPriceCents - $cmvCents - $feeAmountCents;

            $netMarginPercent = $suggestedPriceCents > 0
                ? round(($marginPerUnitCents / $suggestedPriceCents) * 100, 1)
                : 0.0;

            $result[] = new PricingScenario(
                name: $scenario['name'],
                targetMarginPercent: $scenario['margin'],
                suggestedPriceCents: $suggestedPriceCents,
                marginPerUnitCents: $marginPerUnitCents,
                netMarginPercent: $netMarginPercent,
            );
        }

        return $result;
    }

    private function determineHealthStatus(?float $cmvPercent): CmvHealthStatus
    {
        if ($cmvPercent === null) {
            return CmvHealthStatus::Critical;
        }

        if ($cmvPercent < 45.0) {
            return CmvHealthStatus::Healthy;
        }

        if ($cmvPercent <= 60.0) {
            return CmvHealthStatus::Tight;
        }

        return CmvHealthStatus::Critical;
    }
}
