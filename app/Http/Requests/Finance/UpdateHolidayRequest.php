<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\DTOs\Finance\UpdateHolidayData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateHolidayRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d', Rule::unique('holidays', 'date')->ignore($this->route('feriado'))],
            'name' => ['required', 'string', 'max:150'],
            'was_open' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): UpdateHolidayData
    {
        return UpdateHolidayData::fromArray($this->validated());
    }
}
