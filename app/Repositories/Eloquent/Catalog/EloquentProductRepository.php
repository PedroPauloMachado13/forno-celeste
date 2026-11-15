<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Catalog;

use App\DTOs\Catalog\StoreProductData;
use App\DTOs\Catalog\UpdateProductData;
use App\Models\Product;
use App\Repositories\Contracts\Catalog\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category',
                'packaging',
                'baseRecipe.ingredients.ingredient',
                'subRecipes.ingredients.ingredient',
            ])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()
            ->with([
                'category',
                'packaging',
                'baseRecipe.ingredients.ingredient',
                'subRecipes.ingredients.ingredient',
            ])
            ->findOrFail($id);
    }

    public function findWithRecipes(int $id): Product
    {
        return Product::query()
            ->with([
                'packaging',
                'baseRecipe.ingredients.ingredient',
                'subRecipes.ingredients.ingredient',
            ])
            ->findOrFail($id);
    }

    /** @return Collection<int, Product> */
    public function listAll(): Collection
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sale_price_cents']);
    }

    /** @return Collection<int, Product> */
    public function listAllWithRecipes(): Collection
    {
        return Product::query()
            ->with([
                'packaging',
                'baseRecipe.ingredients.ingredient',
                'subRecipes.ingredients.ingredient',
            ])
            ->orderBy('name')
            ->get();
    }

    public function createWithSubRecipes(StoreProductData $data): Product
    {
        $product = Product::query()->create($data->toProductArray());

        $syncArray = $data->toSubRecipeSyncArray();

        if ($syncArray !== []) {
            $product->subRecipes()->attach($syncArray);
        }

        return $product->load([
            'category',
            'packaging',
            'baseRecipe.ingredients.ingredient',
            'subRecipes.ingredients.ingredient',
        ]);
    }

    public function updateWithSubRecipes(Product $product, UpdateProductData $data): Product
    {
        $product->update($data->toProductArray());

        $product->subRecipes()->sync($data->toSubRecipeSyncArray());

        return $product->load([
            'category',
            'packaging',
            'baseRecipe.ingredients.ingredient',
            'subRecipes.ingredients.ingredient',
        ]);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
