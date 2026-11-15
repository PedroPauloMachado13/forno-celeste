<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use Carbon\CarbonImmutable;

final readonly class UpdateHolidayData
{
    public function __construct(
        public CarbonImmutable $date,
        public string $name,
        public bool $wasOpen,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            date: CarbonImmutable::parse($data['date']),
            name: (string) $data['name'],
            wasOpen: (bool) ($data['was_open'] ?? false),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'date' => $this->date->toDateString(),
            'name' => $this->name,
            'was_open' => $this->wasOpen,
        ];
    }
}
