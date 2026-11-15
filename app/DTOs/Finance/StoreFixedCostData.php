<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use App\Enums\Finance\FixedCostType;

final readonly class StoreFixedCostData
{
    public function __construct(
        public string $name,
        public FixedCostType $type,
        public ?int $defaultAmountCents,
        public ?string $percentageValue,
        public ?string $description,
        public bool $isActive,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            type: FixedCostType::from((string) $data['type']),
            defaultAmountCents: isset($data['default_amount_cents']) ? (int) $data['default_amount_cents'] : null,
            percentageValue: isset($data['percentage_value']) ? (string) $data['percentage_value'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'default_amount_cents' => $this->defaultAmountCents,
            'percentage_value' => $this->percentageValue,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
