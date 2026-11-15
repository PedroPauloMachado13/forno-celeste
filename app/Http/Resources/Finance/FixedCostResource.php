<?php

declare(strict_types=1);

namespace App\Http\Resources\Finance;

use App\Models\FixedCost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FixedCost */
final class FixedCostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'default_amount_cents' => $this->default_amount_cents,
            'percentage_value' => $this->percentage_value,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'bills_count' => $this->whenCounted('bills'),
        ];
    }
}
