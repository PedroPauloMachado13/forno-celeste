<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
final class RecipeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'yield_quantity' => (int) $this->yield_quantity,
            'is_experimental' => $this->is_experimental,
            'ingredients' => RecipeIngredientResource::collection($this->whenLoaded('ingredients')),
        ];
    }
}
