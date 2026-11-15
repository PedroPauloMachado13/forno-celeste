<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use App\DTOs\Catalog\UpdatePackagingData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePackagingRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150', Rule::unique('packagings', 'name')->ignore($this->route('embalagem'))],
            'cost_cents' => ['required', 'integer', 'min:0', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(): UpdatePackagingData
    {
        return UpdatePackagingData::fromArray($this->validated());
    }
}
