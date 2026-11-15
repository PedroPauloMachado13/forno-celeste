<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\StoreRecipeData;
use App\Enums\Catalog\MeasurementUnit;
use App\Enums\Catalog\RecipeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRecipeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:recipes,name'],
            'type' => ['required', 'string', Rule::in(array_column(RecipeType::cases(), 'value'))],
            'yield_quantity' => ['required', 'integer', 'min:1'],
            'is_experimental' => ['sometimes', 'boolean'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999.9999'],
            'ingredients.*.unit' => ['required', 'string', Rule::in(array_column(MeasurementUnit::cases(), 'value'))],
            'ingredients.*.note' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toData(): StoreRecipeData
    {
        return StoreRecipeData::fromArray($this->validated());
    }
}
