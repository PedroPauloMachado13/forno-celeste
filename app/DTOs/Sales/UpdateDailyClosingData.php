<?php

declare(strict_types=1);

namespace App\DTOs\Sales;

final readonly class UpdateDailyClosingData
{
    /**
     * @param  list<CashWithdrawalLineData>  $withdrawals
     * @param  list<WasteLineData>  $wastes
     */
    public function __construct(
        public int $cardSalesCents,
        public int $cashDrawerCents,
        public array $withdrawals,
        public array $wastes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            cardSalesCents: (int) $data['card_sales_cents'],
            cashDrawerCents: (int) $data['cash_drawer_cents'],
            withdrawals: array_map(
                CashWithdrawalLineData::fromArray(...),
                $data['withdrawals'] ?? [],
            ),
            wastes: array_map(
                WasteLineData::fromArray(...),
                $data['wastes'] ?? [],
            ),
        );
    }
}
