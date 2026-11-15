<?php

declare(strict_types=1);

namespace App\Http\Resources\Finance;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Bill */
final class BillResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fixed_cost_id' => $this->fixed_cost_id,
            'fixed_cost' => FixedCostResource::make($this->whenLoaded('fixedCost')),
            'bill_type' => $this->bill_type->value,
            'bill_type_label' => $this->bill_type->label(),
            'description' => $this->description,
            'amount_cents' => $this->amount_cents,
            'reference_month' => $this->reference_month?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
