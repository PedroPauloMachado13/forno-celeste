<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreHolidayRequest;
use App\Http\Requests\Finance\UpdateHolidayRequest;
use App\Http\Resources\Finance\HolidayResource;
use App\Models\Holiday;
use App\Services\Finance\HolidayService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $holidayService,
    ) {}

    public function index(): Response
    {
        $holidays = $this->holidayService->paginate();

        return Inertia::render('Finance/Holidays/Index', [
            'holidays' => HolidayResource::collection($holidays),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Finance/Holidays/Create');
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $this->holidayService->create($request->toData());

        return redirect()->route('feriados.index')
            ->with('success', 'Feriado criado com sucesso.');
    }

    public function show(Holiday $feriado): Response
    {
        return Inertia::render('Finance/Holidays/Show', [
            'holiday' => HolidayResource::make($feriado),
        ]);
    }

    public function edit(Holiday $feriado): Response
    {
        return Inertia::render('Finance/Holidays/Edit', [
            'holiday' => HolidayResource::make($feriado),
        ]);
    }

    public function update(UpdateHolidayRequest $request, Holiday $feriado): RedirectResponse
    {
        $this->holidayService->update($feriado, $request->toData());

        return redirect()->route('feriados.index')
            ->with('success', 'Feriado atualizado com sucesso.');
    }

    public function destroy(Holiday $feriado): RedirectResponse
    {
        $this->holidayService->delete($feriado);

        return redirect()->route('feriados.index')
            ->with('success', 'Feriado excluido com sucesso.');
    }
}
