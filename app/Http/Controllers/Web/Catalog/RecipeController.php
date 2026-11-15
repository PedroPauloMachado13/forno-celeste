<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreRecipeRequest;
use App\Http\Requests\Catalog\UpdateRecipeRequest;
use App\Http\Resources\Catalog\IngredientResource;
use App\Http\Resources\Catalog\RecipeResource;
use App\Models\Recipe;
use App\Services\Catalog\CmvCalculatorService;
use App\Services\Catalog\IngredientService;
use App\Services\Catalog\RecipeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class RecipeController extends Controller
{
    public function __construct(
        private readonly RecipeService $recipeService,
        private readonly IngredientService $ingredientService,
        private readonly CmvCalculatorService $cmvCalculator,
    ) {}

    public function index(): Response
    {
        $recipes = $this->recipeService->paginate();

        return Inertia::render('Catalog/Recipes/Index', [
            'recipes' => RecipeResource::collection($recipes),
        ]);
    }

    public function create(): Response
    {
        $ingredients = $this->ingredientService->paginate(perPage: 200);

        return Inertia::render('Catalog/Recipes/Create', [
            'ingredients' => IngredientResource::collection($ingredients),
        ]);
    }

    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        $this->recipeService->create($request->toData());

        return redirect()->route('receitas.index')
            ->with('success', 'Receita criada com sucesso.');
    }

    public function show(Recipe $receita): Response
    {
        $recipe = $this->recipeService->find($receita->id);
        $recipeCostCents = $this->cmvCalculator->calculateForRecipe($recipe);

        return Inertia::render('Catalog/Recipes/Show', [
            'recipe' => RecipeResource::make($recipe),
            'recipe_cost_cents' => $recipeCostCents,
        ]);
    }

    public function edit(Recipe $receita): Response
    {
        $recipe = $this->recipeService->find($receita->id);
        $ingredients = $this->ingredientService->paginate(perPage: 200);

        return Inertia::render('Catalog/Recipes/Edit', [
            'recipe' => RecipeResource::make($recipe),
            'ingredients' => IngredientResource::collection($ingredients),
        ]);
    }

    public function update(UpdateRecipeRequest $request, Recipe $receita): RedirectResponse
    {
        $this->recipeService->update($receita, $request->toData());

        return redirect()->route('receitas.index')
            ->with('success', 'Receita atualizada com sucesso.');
    }

    public function destroy(Recipe $receita): RedirectResponse
    {
        $this->recipeService->delete($receita);

        return redirect()->route('receitas.index')
            ->with('success', 'Receita excluída com sucesso.');
    }
}
