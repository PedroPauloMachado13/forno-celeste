<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\DTOs\Finance\UpdateFixedCostData;
use App\Enums\Finance\FixedCostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFixedCostRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150', Rule::unique('fixed_costs', 'name')->ignore($this->route('custoFixo'))],
            'type' => ['required', 'string', Rule::enum(FixedCostType::class)],
            'default_amount_cents' => ['nullable', 'integer', 'min:0', 'max:999999999', 'required_if:type,fixed_amount'],
            'percentage_value' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:type,percentage'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): UpdateFixedCostData
    {
        return UpdateFixedCostData::fromArray($this->validated());
    }
}
