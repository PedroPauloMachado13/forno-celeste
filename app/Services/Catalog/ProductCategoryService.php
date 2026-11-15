<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\StoreProductCategoryData;
use App\DTOs\Catalog\UpdateProductCategoryData;
use App\Models\ProductCategory;
use App\Repositories\Contracts\Catalog\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class ProductCategoryService
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {}

    /** @return Collection<int, ProductCategory> */
    public function all(): Collection
    {
        return $this->categories->all();
    }

    public function find(int $id): ProductCategory
    {
        return $this->categories->findOrFail($id);
    }

    public function create(StoreProductCategoryData $data): ProductCategory
    {
        return $this->categories->create($data);
    }

    public function update(ProductCategory $category, UpdateProductCategoryData $data): ProductCategory
    {
        return $this->categories->update($category, $data);
    }

    public function delete(ProductCategory $category): void
    {
        $this->categories->delete($category);
    }
}
