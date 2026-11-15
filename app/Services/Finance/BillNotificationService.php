<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Repositories\Contracts\Finance\BillRepositoryInterface;

final class BillNotificationService
{
    public function __construct(
        private readonly BillRepositoryInterface $bills,
    ) {}

    /**
     * @return array{due_soon: int, overdue: int}
     */
    public function getCounts(): array
    {
        return [
            'due_soon' => $this->bills->countDueSoon(),
            'overdue' => $this->bills->countOverdue(),
        ];
    }
}
