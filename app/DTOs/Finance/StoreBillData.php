<?php

declare(strict_types=1);

namespace App\DTOs\Finance;

use App\Enums\Finance\BillType;
use Carbon\CarbonImmutable;

final readonly class StoreBillData
{
    public function __construct(
        public ?int $fixedCostId,
        public BillType $billType,
        public string $description,
        public int $amountCents,
        public ?CarbonImmutable $referenceMonth,
        public ?CarbonImmutable $dueDate,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $billType = BillType::from($data['bill_type'] ?? 'other');

        // Default to 'fixed' when linked to a fixed cost
        if (isset($data['fixed_cost_id']) && ! isset($data['bill_type'])) {
            $billType = BillType::Fixed;
        }

        return new self(
            fixedCostId: isset($data['fixed_cost_id']) ? (int) $data['fixed_cost_id'] : null,
            billType: $billType,
            description: (string) $data['description'],
            amountCents: (int) $data['amount_cents'],
            referenceMonth: isset($data['reference_month']) ? CarbonImmutable::parse($data['reference_month']) : null,
            dueDate: isset($data['due_date']) ? CarbonImmutable::parse($data['due_date']) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'fixed_cost_id' => $this->fixedCostId,
            'bill_type' => $this->billType->value,
            'description' => $this->description,
            'amount_cents' => $this->amountCents,
            'reference_month' => $this->referenceMonth?->toDateString(),
            'due_date' => $this->dueDate?->toDateString(),
        ];
    }
}
