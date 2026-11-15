<?php

declare(strict_types=1);

namespace App\DTOs\Catalog;

use App\Models\Product;

final readonly class ProductPricingData
{
    /**
     * @param  list<PricingScenario>  $pricingScenarios
     */
    public function __construct(
        public Product $product,
        public CmvResult $cmv,
        public array $pricingScenarios,
    ) {}
}
