<?php

declare(strict_types=1);

namespace App\DTOs\Sales;

final readonly class CashWithdrawalLineData
{
    public function __construct(
        public int $amountCents,
        public string $description,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            amountCents: (int) $data['amount_cents'],
            description: (string) $data['description'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'amount_cents' => $this->amountCents,
            'description' => $this->description,
        ];
    }
}
