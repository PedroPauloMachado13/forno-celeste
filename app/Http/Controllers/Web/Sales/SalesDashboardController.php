<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\SalesDashboardRequest;
use App\Services\Sales\SalesDashboardService;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

final class SalesDashboardController extends Controller
{
    public function __construct(
        private readonly SalesDashboardService $dashboardService,
    ) {}

    public function __invoke(SalesDashboardRequest $request): Response
    {
        $validated = $request->validated();

        $from = isset($validated['from'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['from'])->startOfDay()
            : null;

        $to = isset($validated['to'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['to'])->startOfDay()
            : null;

        $data = $this->dashboardService->getSummary($from, $to);

        return Inertia::render('Sales/Dashboard', $data);
    }
}
