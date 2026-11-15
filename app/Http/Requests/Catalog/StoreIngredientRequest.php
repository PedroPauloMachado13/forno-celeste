<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\StoreIngredientData;
use App\Enums\Catalog\MeasurementUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIngredientRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:ingredients,name'],
            'package_quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.9999'],
            'unit' => ['required', 'string', Rule::in(array_column(MeasurementUnit::cases(), 'value'))],
            'package_cost_cents' => ['required', 'integer', 'min:0', 'max:99999999'],
        ];
    }

    public function toData(): StoreIngredientData
    {
        return StoreIngredientData::fromArray($this->validated());
    }
}
