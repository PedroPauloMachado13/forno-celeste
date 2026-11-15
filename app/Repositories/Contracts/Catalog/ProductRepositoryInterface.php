<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Catalog;

use App\DTOs\Catalog\StoreProductData;
use App\DTOs\Catalog\UpdateProductData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    /** @return LengthAwarePaginator<int, Product> */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(int $id): Product;

    /**
     * Find a product with all recipe relationships loaded (for CMV calculation).
     */
    public function findWithRecipes(int $id): Product;

    /**
     * Get all products ordered by name (for dropdowns / selection lists).
     *
     * @return Collection<int, Product>
     */
    public function listAll(): Collection;

    /**
     * Get all products with recipe relationships loaded (for CMV calculation).
     *
     * @return Collection<int, Product>
     */
    public function listAllWithRecipes(): Collection;

    public function createWithSubRecipes(StoreProductData $data): Product;

    public function updateWithSubRecipes(Product $product, UpdateProductData $data): Product;

    public function delete(Product $product): void;
}
