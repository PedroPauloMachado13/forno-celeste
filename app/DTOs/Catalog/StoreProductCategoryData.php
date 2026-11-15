<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

final readonly class StoreProductCategoryData
{
    public function __construct(
        public string $name,
        public ?int $sortOrder,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = ['name' => $this->name];

        if ($this->sortOrder !== null) {
            $result['sort_order'] = $this->sortOrder;
        }

        return $result;
    }
}
