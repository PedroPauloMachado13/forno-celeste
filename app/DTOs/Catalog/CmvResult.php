<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

use App\Enums\Catalog\CmvHealthStatus;

final readonly class CmvResult
{
    public function __construct(
        public int $ingredientCostCents,
        public int $packagingCostCents,
        public int $cmvCents,
        public ?float $cmvPercent,
        public CmvHealthStatus $healthStatus,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ingredientCostCents: (int) $data['ingredient_cost_cents'],
            packagingCostCents: (int) $data['packaging_cost_cents'],
            cmvCents: (int) $data['cmv_cents'],
            cmvPercent: isset($data['cmv_percent']) ? (float) $data['cmv_percent'] : null,
            healthStatus: $data['health_status'] instanceof CmvHealthStatus
                ? $data['health_status']
                : CmvHealthStatus::from($data['health_status']),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'ingredient_cost_cents' => $this->ingredientCostCents,
            'packaging_cost_cents' => $this->packagingCostCents,
            'cmv_cents' => $this->cmvCents,
            'cmv_percent' => $this->cmvPercent,
            'health_status' => $this->healthStatus->value,
        ];
    }
}
