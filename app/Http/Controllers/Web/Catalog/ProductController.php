<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Enums\Catalog\RecipeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Http\Resources\Catalog\PackagingResource;
use App\Http\Resources\Catalog\ProductCategoryResource;
use App\Http\Resources\Catalog\ProductResource;
use App\Http\Resources\Catalog\RecipeResource;
use App\Models\Product;
use App\Services\Catalog\PackagingService;
use App\Services\Catalog\ProductCategoryService;
use App\Services\Catalog\ProductService;
use App\Services\Catalog\RecipeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductCategoryService $categoryService,
        private readonly RecipeService $recipeService,
        private readonly PackagingService $packagingService,
    ) {}

    public function index(): Response
    {
        $products = $this->productService->paginateWithCmv();

        return Inertia::render('Catalog/Products/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalog/Products/Create', [
            'categories' => ProductCategoryResource::collection($this->categoryService->all()),
            'base_recipes' => RecipeResource::collection($this->recipeService->paginate(RecipeType::Base, 200)),
            'sub_recipes' => RecipeResource::collection($this->recipeService->paginate(RecipeType::Sub, 200)),
            'packagings' => PackagingResource::collection($this->packagingService->all()),
            'card_machine_fee_percent' => config('catalog.card_machine_fee_percent', 3.15),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create($request->toData());

        return redirect()->route('produtos.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function show(Product $produto): Response
    {
        $pricing = $this->productService->getWithPricing($produto->id);

        return Inertia::render('Catalog/Products/Show', [
            'product' => ProductResource::make($pricing->product)->additional(['cmv' => $pricing->cmv->toArray()]),
            'pricing_scenarios' => array_map(fn ($s) => $s->toArray(), $pricing->pricingScenarios),
        ]);
    }

    public function edit(Product $produto): Response
    {
        $pricing = $this->productService->getWithPricing($produto->id);

        return Inertia::render('Catalog/Products/Edit', [
            'product' => ProductResource::make($pricing->product)->additional(['cmv' => $pricing->cmv->toArray()]),
            'categories' => ProductCategoryResource::collection($this->categoryService->all()),
            'base_recipes' => RecipeResource::collection($this->recipeService->paginate(RecipeType::Base, 200)),
            'sub_recipes' => RecipeResource::collection($this->recipeService->paginate(RecipeType::Sub, 200)),
            'packagings' => PackagingResource::collection($this->packagingService->all()),
            'pricing_scenarios' => array_map(fn ($s) => $s->toArray(), $pricing->pricingScenarios),
            'card_machine_fee_percent' => (float) config('catalog.card_machine_fee_percent', 3.15),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $produto): RedirectResponse
    {
        $this->productService->update($produto, $request->toData());

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $produto): RedirectResponse
    {
        $this->productService->delete($produto);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto excluído com sucesso.');
    }
}
