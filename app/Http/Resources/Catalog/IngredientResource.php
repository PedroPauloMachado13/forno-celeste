<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ingredient */
final class IngredientResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $packageQuantity = (float) $this->package_quantity;

        // Cost per measurement unit in cents (e.g. cents/gram).
        // Formula: package_cost_cents / package_quantity
        // Example: 560 cents / 1000g = 0.56 cents/gram
        $costPerUnitCents = $packageQuantity > 0.0
            ? round($this->package_cost_cents / $packageQuantity, 4)
            : 0.0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'package_quantity' => (int) $this->package_quantity,
            'unit' => $this->unit->value,
            'package_cost_cents' => $this->package_cost_cents,
            'cost_per_unit_cents' => $costPerUnitCents,
        ];
    }
}
