<?php

declare(strict_types=1);

namespace App\Http\Resources\Sales;

use App\Models\DailyClosing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DailyClosing */
final class DailyClosingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'closing_date' => $this->closing_date->toDateString(),
            'card_sales_cents' => $this->card_sales_cents,
            'cash_drawer_cents' => $this->cash_drawer_cents,
            'opening_cash_cents' => $this->opening_cash_cents,
            'cash_sales_cents' => $this->cash_sales_cents,
            'total_revenue_cents' => $this->total_revenue_cents,
            'total_waste_cents' => $this->total_waste_cents,
            'withdrawals' => CashWithdrawalResource::collection($this->whenLoaded('withdrawals')),
            'wastes' => ClosingWasteResource::collection($this->whenLoaded('wastes')),
        ];
    }
}
