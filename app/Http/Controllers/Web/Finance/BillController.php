<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreBillRequest;
use App\Http\Requests\Finance\UpdateBillRequest;
use App\Http\Resources\Finance\BillResource;
use App\Http\Resources\Finance\FixedCostResource;
use App\Models\Bill;
use App\Services\Finance\BillService;
use App\Services\Finance\FixedCostService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BillController extends Controller
{
    public function __construct(
        private readonly BillService $billService,
        private readonly FixedCostService $fixedCostService,
    ) {}

    public function index(Request $request): Response
    {
        $defaultMonth = CarbonImmutable::now()->startOfMonth()->toDateString();
        $referenceMonth = $request->query('month', $defaultMonth);

        $bills = $this->billService->paginate(50, $referenceMonth);
        $upcoming = $this->billService->upcomingUnpaid();

        return Inertia::render('Finance/Bills/Index', [
            'bills' => BillResource::collection($bills),
            'upcoming' => BillResource::collection($upcoming),
            'currentMonth' => $referenceMonth,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Finance/Bills/Create', [
            'fixedCosts' => FixedCostResource::collection($this->fixedCostService->allActive()),
        ]);
    }

    public function store(StoreBillRequest $request): RedirectResponse
    {
        $bill = $this->billService->create($request->toData());

        if ($request->boolean('mark_as_paid')) {
            $this->billService->markAsPaid($bill);
        }

        return redirect()->route('contas.index')
            ->with('success', 'Conta criada com sucesso.');
    }

    public function show(Bill $conta): Response
    {
        $conta->load('fixedCost');

        return Inertia::render('Finance/Bills/Show', [
            'bill' => BillResource::make($conta),
        ]);
    }

    public function edit(Bill $conta): Response
    {
        $conta->load('fixedCost');

        return Inertia::render('Finance/Bills/Edit', [
            'bill' => BillResource::make($conta),
            'fixedCosts' => FixedCostResource::collection($this->fixedCostService->allActive()),
        ]);
    }

    public function update(UpdateBillRequest $request, Bill $conta): RedirectResponse
    {
        $this->billService->update($conta, $request->toData());

        return redirect()->route('contas.index')
            ->with('success', 'Conta atualizada com sucesso.');
    }

    public function destroy(Bill $conta): RedirectResponse
    {
        $this->billService->delete($conta);

        return redirect()->route('contas.index')
            ->with('success', 'Conta excluida com sucesso.');
    }

    public function pay(Bill $conta): RedirectResponse
    {
        $this->billService->markAsPaid($conta);

        return redirect()->route('contas.index')
            ->with('success', 'Conta marcada como paga.');
    }

    public function unpay(Bill $conta): RedirectResponse
    {
        $this->billService->markAsUnpaid($conta);

        return redirect()->route('contas.index')
            ->with('success', 'Pagamento desfeito.');
    }
}
