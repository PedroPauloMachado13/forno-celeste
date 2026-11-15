<?php

declare(strict_types=1);

namespace App\Http\Requests\Finance;

use App\DTOs\Finance\StoreHolidayData;
use Illuminate\Foundation\Http\FormRequest;

final class StoreHolidayRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d', 'unique:holidays,date'],
            'name' => ['required', 'string', 'max:150'],
            'was_open' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): StoreHolidayData
    {
        return StoreHolidayData::fromArray($this->validated());
    }
}
