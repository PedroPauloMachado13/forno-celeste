<?php

declare(strict_types=1);

namespace App\Http\Requests\Sales;

use App\DTOs\Sales\StoreDailyClosingData;
use Illuminate\Foundation\Http\FormRequest;

final class StoreDailyClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No auth required — local-only system
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'closing_date' => ['required', 'date_format:Y-m-d', 'unique:daily_closings,closing_date'],
            'card_sales_cents' => ['required', 'integer', 'min:0', 'max:999999999'],
            'cash_drawer_cents' => ['required', 'integer', 'min:0', 'max:999999999'],
            'withdrawals' => ['sometimes', 'array', 'max:50'],
            'withdrawals.*.amount_cents' => ['required_with:withdrawals', 'integer', 'min:1', 'max:999999999'],
            'withdrawals.*.description' => ['required_with:withdrawals', 'string', 'max:255'],
            'wastes' => ['sometimes', 'array', 'max:50'],
            'wastes.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'wastes.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function toData(): StoreDailyClosingData
    {
        return StoreDailyClosingData::fromArray($this->validated());
    }
}
