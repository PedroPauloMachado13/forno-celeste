<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Packaging */
final class PackagingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost_cents' => $this->cost_cents,
            'description' => $this->description,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
