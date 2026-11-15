<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
final class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'base_recipe' => new RecipeResource($this->whenLoaded('baseRecipe')),
            'sub_recipes' => $this->whenLoaded('subRecipes', function () {
                return $this->subRecipes->map(function ($recipe) {
                    $resource = (new RecipeResource($recipe))->toArray(request());
                    $resource['pivot_quantity'] = (int) ($recipe->pivot->quantity ?? 1);

                    return $resource;
                });
            }),
            'packaging_id' => $this->packaging_id,
            'packaging' => new PackagingResource($this->whenLoaded('packaging')),
            'sale_price_cents' => $this->sale_price_cents,
        ];

        // CMV data is passed through additional() from the controller, not via dynamic model properties
        if ($this->resource->getAttribute('cmv_data') !== null) {
            $data['cmv'] = $this->resource->getAttribute('cmv_data');
        }

        return $data;
    }
}
