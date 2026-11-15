<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreFixedCostRequest;
use App\Http\Requests\Finance\UpdateFixedCostRequest;
use App\Http\Resources\Finance\FixedCostResource;
use App\Models\FixedCost;
use App\Services\Finance\FixedCostService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class FixedCostController extends Controller
{
    public function __construct(
        private readonly FixedCostService $fixedCostService,
    ) {}

    public function index(): Response
    {
        $fixedCosts = $this->fixedCostService->paginate();

        return Inertia::render('Finance/FixedCosts/Index', [
            'fixedCosts' => FixedCostResource::collection($fixedCosts),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Finance/FixedCosts/Create');
    }

    public function store(StoreFixedCostRequest $request): RedirectResponse
    {
        $this->fixedCostService->create($request->toData());

        return redirect()->route('custos-fixos.index')
            ->with('success', 'Custo fixo criado com sucesso.');
    }

    public function edit(FixedCost $custoFixo): Response
    {
        return Inertia::render('Finance/FixedCosts/Edit', [
            'fixedCost' => FixedCostResource::make($custoFixo),
        ]);
    }

    public function update(UpdateFixedCostRequest $request, FixedCost $custoFixo): RedirectResponse
    {
        $this->fixedCostService->update($custoFixo, $request->toData());

        return redirect()->route('custos-fixos.index')
            ->with('success', 'Custo fixo atualizado com sucesso.');
    }

    public function destroy(FixedCost $custoFixo): RedirectResponse
    {
        $this->fixedCostService->delete($custoFixo);

        return redirect()->route('custos-fixos.index')
            ->with('success', 'Custo fixo excluido com sucesso.');
    }
}
