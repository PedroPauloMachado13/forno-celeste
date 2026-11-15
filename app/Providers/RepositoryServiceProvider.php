<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\Catalog\IngredientRepositoryInterface;
use App\Repositories\Contracts\Catalog\PackagingRepositoryInterface;
use App\Repositories\Contracts\Catalog\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\Catalog\ProductRepositoryInterface;
use App\Repositories\Contracts\Catalog\RecipeRepositoryInterface;
use App\Repositories\Contracts\Finance\BillRepositoryInterface;
use App\Repositories\Contracts\Finance\FixedCostRepositoryInterface;
use App\Repositories\Contracts\Finance\HolidayRepositoryInterface;
use App\Repositories\Contracts\Sales\DailyClosingRepositoryInterface;
use App\Repositories\Eloquent\Catalog\EloquentIngredientRepository;
use App\Repositories\Eloquent\Catalog\EloquentPackagingRepository;
use App\Repositories\Eloquent\Catalog\EloquentProductCategoryRepository;
use App\Repositories\Eloquent\Catalog\EloquentProductRepository;
use App\Repositories\Eloquent\Catalog\EloquentRecipeRepository;
use App\Repositories\Eloquent\Finance\EloquentBillRepository;
use App\Repositories\Eloquent\Finance\EloquentFixedCostRepository;
use App\Repositories\Eloquent\Finance\EloquentHolidayRepository;
use App\Repositories\Eloquent\Sales\EloquentDailyClosingRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        IngredientRepositoryInterface::class => EloquentIngredientRepository::class,
        PackagingRepositoryInterface::class => EloquentPackagingRepository::class,
        ProductCategoryRepositoryInterface::class => EloquentProductCategoryRepository::class,
        RecipeRepositoryInterface::class => EloquentRecipeRepository::class,
        ProductRepositoryInterface::class => EloquentProductRepository::class,

        // Sales
        DailyClosingRepositoryInterface::class => EloquentDailyClosingRepository::class,

        // Finance
        FixedCostRepositoryInterface::class => EloquentFixedCostRepository::class,
        BillRepositoryInterface::class => EloquentBillRepository::class,
        HolidayRepositoryInterface::class => EloquentHolidayRepository::class,
    ];
}
