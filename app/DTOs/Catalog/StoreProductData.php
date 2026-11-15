<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

final readonly class StoreProductData
{
    /**
     * @param  list<array{recipe_id: int, quantity: int}>  $subRecipes
     */
    public function __construct(
        public string $name,
        public int $categoryId,
        public int $recipeId,
        public int $packagingId,
        public int $salePriceCents,
        public array $subRecipes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $subRecipes = [];

        foreach ($data['sub_recipes'] ?? [] as $entry) {
            $subRecipes[] = [
                'recipe_id' => (int) $entry['recipe_id'],
                'quantity' => (int) ($entry['quantity'] ?? 1),
            ];
        }

        return new self(
            name: (string) $data['name'],
            categoryId: (int) $data['category_id'],
            recipeId: (int) $data['recipe_id'],
            packagingId: (int) $data['packaging_id'],
            salePriceCents: (int) $data['sale_price_cents'],
            subRecipes: $subRecipes,
        );
    }

    /** @return array<string, mixed> */
    public function toProductArray(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'recipe_id' => $this->recipeId,
            'packaging_id' => $this->packagingId,
            'sale_price_cents' => $this->salePriceCents,
        ];
    }

    /**
     * Build the sync array for subRecipes: [recipe_id => ['quantity' => N], ...]
     *
     * @return array<int, array{quantity: int}>
     */
    public function toSubRecipeSyncArray(): array
    {
        $sync = [];

        foreach ($this->subRecipes as $entry) {
            $sync[$entry['recipe_id']] = ['quantity' => $entry['quantity']];
        }

        return $sync;
    }
}
