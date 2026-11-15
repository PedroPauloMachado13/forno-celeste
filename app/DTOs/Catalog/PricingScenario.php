<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

final readonly class PricingScenario
{
    public function __construct(
        public string $name,
        public float $targetMarginPercent,
        public int $suggestedPriceCents,
        public int $marginPerUnitCents,
        public float $netMarginPercent,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            targetMarginPercent: (float) $data['target_margin_percent'],
            suggestedPriceCents: (int) $data['suggested_price_cents'],
            marginPerUnitCents: (int) $data['margin_per_unit_cents'],
            netMarginPercent: (float) $data['net_margin_percent'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'target_margin_percent' => $this->targetMarginPercent,
            'suggested_price_cents' => $this->suggestedPriceCents,
            'margin_per_unit_cents' => $this->marginPerUnitCents,
            'net_margin_percent' => $this->netMarginPercent,
        ];
    }
}
