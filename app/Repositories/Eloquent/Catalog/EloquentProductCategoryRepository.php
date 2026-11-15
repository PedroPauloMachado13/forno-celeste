<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent\Catalog;

use App\DTOs\Catalog\StoreProductCategoryData;
use App\DTOs\Catalog\UpdateProductCategoryData;
use App\Models\ProductCategory;
use App\Repositories\Contracts\Catalog\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function all(): Collection
    {
        return ProductCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): ProductCategory
    {
        return ProductCategory::query()->findOrFail($id);
    }

    public function create(StoreProductCategoryData $data): ProductCategory
    {
        return ProductCategory::query()->create($data->toArray());
    }

    public function update(ProductCategory $category, UpdateProductCategoryData $data): ProductCategory
    {
        $category->update($data->toArray());

        return $category;
    }

    public function delete(ProductCategory $category): void
    {
        $category->delete();
    }
}
