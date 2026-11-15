<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Models\RecipeIngredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecipeIngredient */
final class RecipeIngredientResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ingredient' => new IngredientResource($this->whenLoaded('ingredient')),
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit->value,
            'note' => $this->note,
        ];
    }
}
