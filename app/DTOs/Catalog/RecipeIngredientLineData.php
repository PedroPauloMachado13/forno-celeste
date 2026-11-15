<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

use App\Enums\Catalog\MeasurementUnit;

final readonly class RecipeIngredientLineData
{
    public function __construct(
        public int $ingredientId,
        public string $quantity,
        public MeasurementUnit $unit,
        public ?string $note,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            ingredientId: (int) $data['ingredient_id'],
            quantity: (string) $data['quantity'],
            unit: $data['unit'] instanceof MeasurementUnit
                ? $data['unit']
                : MeasurementUnit::from($data['unit']),
            note: isset($data['note']) ? (string) $data['note'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'ingredient_id' => $this->ingredientId,
            'quantity' => $this->quantity,
            'unit' => $this->unit->value,
            'note' => $this->note,
        ];
    }
}
