<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceDashboardRequest;
use App\Http\Resources\Finance\BillResource;
use App\Services\Finance\FinanceDashboardService;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

final class FinanceDashboardController extends Controller
{
    public function __construct(
        private readonly FinanceDashboardService $dashboardService,
    ) {}

    public function __invoke(FinanceDashboardRequest $request): Response
    {
        $validated = $request->validated();

        $from = isset($validated['from'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['from'])->startOfDay()
            : null;

        $to = isset($validated['to'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['to'])->startOfDay()
            : null;

        $data = $this->dashboardService->getSummary($from, $to);
        $data['upcoming_bills'] = BillResource::collection($data['upcoming_bills']);

        return Inertia::render('Finance/Dashboard', $data);
    }
}
