<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductCategoryRequest;
use App\Http\Requests\Catalog\UpdateProductCategoryRequest;
use App\Http\Resources\Catalog\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Services\Catalog\ProductCategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly ProductCategoryService $categoryService,
    ) {}

    public function index(): Response
    {
        $categories = $this->categoryService->all();

        return Inertia::render('Catalog/Categories/Index', [
            'categories' => ProductCategoryResource::collection($categories),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Catalog/Categories/Create');
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->toData());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function show(ProductCategory $categoria): Response
    {
        return Inertia::render('Catalog/Categories/Show', [
            'category' => ProductCategoryResource::make($categoria),
        ]);
    }

    public function edit(ProductCategory $categoria): Response
    {
        return Inertia::render('Catalog/Categories/Edit', [
            'category' => ProductCategoryResource::make($categoria),
        ]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $categoria): RedirectResponse
    {
        $this->categoryService->update($categoria, $request->toData());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(ProductCategory $categoria): RedirectResponse
    {
        $this->categoryService->delete($categoria);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }
}
