<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreDailyClosingRequest;
use App\Http\Requests\Sales\UpdateDailyClosingRequest;
use App\Http\Resources\Sales\DailyClosingResource;
use App\Models\DailyClosing;
use App\Services\Sales\DailyClosingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DailyClosingController extends Controller
{
    public function __construct(
        private readonly DailyClosingService $dailyClosingService,
    ) {}

    public function index(): Response
    {
        $closings = $this->dailyClosingService->paginate();

        return Inertia::render('Sales/DailyClosings/Index', [
            'closings' => DailyClosingResource::collection($closings),
        ]);
    }

    public function create(): Response
    {
        $formData = $this->dailyClosingService->getCreateFormData();

        return Inertia::render('Sales/DailyClosings/Create', $formData);
    }

    public function store(StoreDailyClosingRequest $request): RedirectResponse
    {
        $this->dailyClosingService->create($request->toData());

        return redirect()->route('fechamentos.index')
            ->with('success', 'Fechamento registrado com sucesso.');
    }

    public function show(DailyClosing $fechamento): Response
    {
        $fechamento->load(['withdrawals', 'wastes.product']);

        return Inertia::render('Sales/DailyClosings/Show', [
            'closing' => DailyClosingResource::make($fechamento),
        ]);
    }

    public function edit(DailyClosing $fechamento): Response
    {
        $fechamento->load(['withdrawals', 'wastes.product']);

        return Inertia::render('Sales/DailyClosings/Edit', [
            'closing' => DailyClosingResource::make($fechamento),
            'products' => $this->dailyClosingService->getProductsWithCmv(),
        ]);
    }

    public function update(UpdateDailyClosingRequest $request, DailyClosing $fechamento): RedirectResponse
    {
        $this->dailyClosingService->update($fechamento, $request->toData());

        return redirect()->route('fechamentos.index')
            ->with('success', 'Fechamento atualizado com sucesso.');
    }

    public function destroy(DailyClosing $fechamento): RedirectResponse
    {
        $this->dailyClosingService->delete($fechamento);

        return redirect()->route('fechamentos.index')
            ->with('success', 'Fechamento excluido com sucesso.');
    }

    public function openingCash(Request $request): JsonResponse
    {
        $date = $request->query('date');

        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['opening_cash_cents' => 0]);
        }

        $openingCash = $this->dailyClosingService->getOpeningCash(
            CarbonImmutable::parse($date),
        );

        return response()->json(['opening_cash_cents' => $openingCash]);
    }
}
