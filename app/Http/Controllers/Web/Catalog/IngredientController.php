<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreIngredientRequest;
use App\Http\Requests\Catalog\UpdateIngredientRequest;
use App\Http\Resources\Catalog\IngredientResource;
use App\Models\Ingredient;
use App\Services\Catalog\IngredientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class IngredientController extends Controller
{
    public function __construct(
        private readonly IngredientService $ingredientService,
    ) {}

    public function index(): Response
    {
        $ingredients = $this->ingredientService->paginate();

        return Inertia::render('Catalog/Ingredients/Index', [
            'ingredients' => IngredientResource::collection($ingredients),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalog/Ingredients/Create');
    }

    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        $this->ingredientService->create($request->toData());

        return redirect()->route('ingredientes.index')
            ->with('success', 'Ingrediente criado com sucesso.');
    }

    public function show(Ingredient $ingrediente): Response
    {
        return Inertia::render('Catalog/Ingredients/Show', [
            'ingredient' => IngredientResource::make($ingrediente),
        ]);
    }

    public function edit(Ingredient $ingrediente): Response
    {
        return Inertia::render('Catalog/Ingredients/Edit', [
            'ingredient' => IngredientResource::make($ingrediente),
        ]);
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingrediente): RedirectResponse
    {
        $this->ingredientService->update($ingrediente, $request->toData());

        return redirect()->route('ingredientes.index')
            ->with('success', 'Ingrediente atualizado com sucesso.');
    }

    public function destroy(Ingredient $ingrediente): RedirectResponse
    {
        $this->ingredientService->delete($ingrediente);

        return redirect()->route('ingredientes.index')
            ->with('success', 'Ingrediente excluído com sucesso.');
    }
}
