<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\StorePackagingData;
use Illuminate\Foundation\Http\FormRequest;

final class StorePackagingRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150', 'unique:packagings,name'],
            'cost_cents' => ['required', 'integer', 'min:0', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(): StorePackagingData
    {
        return StorePackagingData::fromArray($this->validated());
    }
}
