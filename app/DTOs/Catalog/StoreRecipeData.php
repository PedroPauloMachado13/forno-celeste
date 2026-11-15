<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

use App\Enums\Catalog\RecipeType;

final readonly class StoreRecipeData
{
    /**
     * @param  list<RecipeIngredientLineData>  $ingredients
     */
    public function __construct(
        public string $name,
        public RecipeType $type,
        public int $yieldQuantity,
        public bool $isExperimental,
        public array $ingredients,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            type: $data['type'] instanceof RecipeType
                ? $data['type']
                : RecipeType::from($data['type']),
            yieldQuantity: (int) $data['yield_quantity'],
            isExperimental: (bool) ($data['is_experimental'] ?? false),
            ingredients: array_map(
                RecipeIngredientLineData::fromArray(...),
                $data['ingredients'],
            ),
        );
    }

    /** @return array<string, mixed> */
    public function toRecipeArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'yield_quantity' => $this->yieldQuantity,
            'is_experimental' => $this->isExperimental,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function toIngredientLinesArray(): array
    {
        return array_map(
            static fn (RecipeIngredientLineData $line): array => $line->toArray(),
            $this->ingredients,
        );
    }
}
