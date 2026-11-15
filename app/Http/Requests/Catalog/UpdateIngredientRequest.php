<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\UpdateIngredientData;
use App\Enums\Catalog\MeasurementUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No auth in Phase 1
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')->ignore($this->route('ingrediente'))],
            'package_quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.9999'],
            'unit' => ['required', 'string', Rule::in(array_column(MeasurementUnit::cases(), 'value'))],
            'package_cost_cents' => ['required', 'integer', 'min:0', 'max:99999999'],
        ];
    }

    public function toData(): UpdateIngredientData
    {
        return UpdateIngredientData::fromArray($this->validated());
    }
}
