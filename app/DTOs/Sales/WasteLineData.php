<?php

declare(strict_types=1);

namespace App\DTOs\Sales;

final readonly class WasteLineData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) $data['quantity'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
        ];
    }
}
