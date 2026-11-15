<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\DTOs\Finance\StoreBillData;
use App\Enums\Finance\BillType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBillRequest extends FormRequest
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
            'fixed_cost_id' => ['nullable', 'integer', 'exists:fixed_costs,id'],
            'bill_type' => ['required', Rule::enum(BillType::class)],
            'description' => ['required', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:1', 'max:999999999'],
            'reference_month' => ['nullable', 'date_format:Y-m-d'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'mark_as_paid' => ['boolean'],
        ];
    }

    public function toData(): StoreBillData
    {
        return StoreBillData::fromArray($this->validated());
    }
}
