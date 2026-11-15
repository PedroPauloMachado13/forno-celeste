<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Exceptions\Catalog\PackagingInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StorePackagingRequest;
use App\Http\Requests\Catalog\UpdatePackagingRequest;
use App\Http\Resources\Catalog\PackagingResource;
use App\Models\Packaging;
use App\Services\Catalog\PackagingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PackagingController extends Controller
{
    public function __construct(
        private readonly PackagingService $packagingService,
    ) {}

    public function index(): Response
    {
        $packagings = $this->packagingService->paginate();

        return Inertia::render('Catalog/Packagings/Index', [
            'packagings' => PackagingResource::collection($packagings),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalog/Packagings/Create');
    }

    public function store(StorePackagingRequest $request): RedirectResponse
    {
        $this->packagingService->create($request->toData());

        return redirect()->route('embalagens.index')
            ->with('success', 'Embalagem criada com sucesso.');
    }

    public function edit(Packaging $embalagem): Response
    {
        return Inertia::render('Catalog/Packagings/Edit', [
            'packaging' => PackagingResource::make($embalagem),
        ]);
    }

    public function update(UpdatePackagingRequest $request, Packaging $embalagem): RedirectResponse
    {
        $this->packagingService->update($embalagem, $request->toData());

        return redirect()->route('embalagens.index')
            ->with('success', 'Embalagem atualizada com sucesso.');
    }

    public function destroy(Packaging $embalagem): RedirectResponse
    {
        try {
            $this->packagingService->delete($embalagem);
        } catch (PackagingInUseException $e) {
            return redirect()->route('embalagens.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('embalagens.index')
            ->with('success', 'Embalagem excluída com sucesso.');
    }
}
