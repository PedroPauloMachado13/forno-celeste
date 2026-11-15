<?php

declare(strict_types=1);

namespace App\Http\Resources\Sales;

use App\Models\ClosingWaste;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClosingWaste */
final class ClosingWasteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'quantity' => $this->quantity,
            'unit_cost_cents' => $this->unit_cost_cents,
            'total_cost_cents' => $this->total_cost_cents,
        ];
    }
}
