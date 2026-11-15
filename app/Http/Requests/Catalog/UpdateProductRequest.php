<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\UpdateProductData;
use App\Enums\Catalog\RecipeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($this->route('produto'))],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'recipe_id' => ['required', 'integer', Rule::exists('recipes', 'id')->where('type', RecipeType::Base->value)],
            'packaging_id' => ['required', 'integer', 'exists:packagings,id'],
            'sale_price_cents' => ['required', 'integer', 'min:0', 'max:99999999'],
            'sub_recipes' => ['sometimes', 'array'],
            'sub_recipes.*.recipe_id' => ['required', 'integer', Rule::exists('recipes', 'id')->where('type', RecipeType::Sub->value)],
            'sub_recipes.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function toData(): UpdateProductData
    {
        return UpdateProductData::fromArray($this->validated());
    }
}
