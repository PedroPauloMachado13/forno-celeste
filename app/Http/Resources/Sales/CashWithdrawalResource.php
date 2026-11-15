<?php

declare(strict_types=1);

namespace App\Http\Resources\Sales;

use App\Models\CashWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CashWithdrawal */
final class CashWithdrawalResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount_cents' => $this->amount_cents,
            'description' => $this->description,
        ];
    }
}
