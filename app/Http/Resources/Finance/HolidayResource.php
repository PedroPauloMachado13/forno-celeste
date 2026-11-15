<?php

declare(strict_types=1);

namespace App\Http\Resources\Finance;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Holiday */
final class HolidayResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'name' => $this->name,
            'was_open' => $this->was_open,
        ];
    }
}
