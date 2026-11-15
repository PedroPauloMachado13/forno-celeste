<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\UpdateProductCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($this->route('categoria'))],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function toData(): UpdateProductCategoryData
    {
        return UpdateProductCategoryData::fromArray($this->validated());
    }
}
