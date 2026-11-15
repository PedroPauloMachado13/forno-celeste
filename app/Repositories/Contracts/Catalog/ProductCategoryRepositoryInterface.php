<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Catalog;

use App\DTOs\Catalog\StoreProductCategoryData;
use App\DTOs\Catalog\UpdateProductCategoryData;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface
{
    /** @return Collection<int, ProductCategory> */
    public function all(): Collection;

    public function findOrFail(int $id): ProductCategory;

    public function create(StoreProductCategoryData $data): ProductCategory;

    public function update(ProductCategory $category, UpdateProductCategoryData $data): ProductCategory;

    public function delete(ProductCategory $category): void;
}
