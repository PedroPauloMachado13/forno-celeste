<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

final readonly class UpdatePackagingData
{
    public function __construct(
        public string $name,
        public int $costCents,
        public ?string $description,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            costCents: (int) $data['cost_cents'],
            description: isset($data['description']) ? (string) $data['description'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'cost_cents' => $this->costCents,
            'description' => $this->description,
        ];
    }
}
