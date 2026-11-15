<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

use App\Enums\Catalog\MeasurementUnit;

final readonly class UpdateIngredientData
{
    public function __construct(
        public string $name,
        public string $packageQuantity,
        public MeasurementUnit $unit,
        public int $packageCostCents,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            packageQuantity: (string) $data['package_quantity'],
            unit: $data['unit'] instanceof MeasurementUnit
                ? $data['unit']
                : MeasurementUnit::from($data['unit']),
            packageCostCents: (int) $data['package_cost_cents'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'package_quantity' => $this->packageQuantity,
            'unit' => $this->unit->value,
            'package_cost_cents' => $this->packageCostCents,
        ];
    }
}
