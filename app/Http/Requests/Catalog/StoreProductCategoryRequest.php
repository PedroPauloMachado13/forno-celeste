<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\StoreProductCategoryData;
use Illuminate\Foundation\Http\FormRequest;

final class StoreProductCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'unique:product_categories,name'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function toData(): StoreProductCategoryData
    {
        return StoreProductCategoryData::fromArray($this->validated());
    }
}
