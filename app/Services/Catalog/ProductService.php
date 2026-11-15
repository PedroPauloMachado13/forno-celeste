<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\Catalog\ProductPricingData;
use App\DTOs\Catalog\StoreProductData;
use App\DTOs\Catalog\UpdateProductData;
use App\Models\Product;
use App\Repositories\Contracts\Catalog\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CmvCalculatorService $cmvCalculator,
    ) {}

    /**
     * Paginate products, enriching each with its CMV result as `additional` metadata.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateWithCmv(int $perPage = 20): LengthAwarePaginator
    {
        $paginator = $this->products->paginate($perPage);

        $paginator->through(function (Product $product): Product {
            $product->setAttribute('cmv_data', $this->cmvCalculator->calculateForProduct($product)->toArray());

            return $product;
        });

        return $paginator;
    }

    /**
     * Load a single product with full CMV and pricing scenarios.
     */
    public function getWithPricing(int $id): ProductPricingData
    {
        $product = $this->products->findOrFail($id);
        $cmvResult = $this->cmvCalculator->calculateForProduct($product);
        $cardMachineFeePercent = (float) config('catalog.card_machine_fee_percent', 3.15);
        $pricingScenarios = $this->cmvCalculator->calculatePricingScenarios(
            $cmvResult->cmvCents,
            $cardMachineFeePercent,
        );

        return new ProductPricingData(
            product: $product,
            cmv: $cmvResult,
            pricingScenarios: $pricingScenarios,
        );
    }

    public function find(int $id): Product
    {
        return $this->products->findOrFail($id);
    }

    public function create(StoreProductData $data): Product
    {
        return DB::transaction(
            fn (): Product => $this->products->createWithSubRecipes($data),
        );
    }

    public function update(Product $product, UpdateProductData $data): Product
    {
        return DB::transaction(
            fn (): Product => $this->products->updateWithSubRecipes($product, $data),
        );
    }

    public function delete(Product $product): void
    {
        $this->products->delete($product);
    }
}
