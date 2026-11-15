<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\Catalog\IngredientController;
use App\Http\Controllers\Web\Catalog\PackagingController;
use App\Http\Controllers\Web\Catalog\ProductCategoryController;
use App\Http\Controllers\Web\Catalog\ProductController;
use App\Http\Controllers\Web\Catalog\RecipeController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Finance\BillController;
use App\Http\Controllers\Web\Finance\FinanceDashboardController;
use App\Http\Controllers\Web\Finance\FixedCostController;
use App\Http\Controllers\Web\Finance\HolidayController;
use App\Http\Controllers\Web\Sales\DailyClosingController;
use App\Http\Controllers\Web\Sales\SalesDashboardController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});
Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// App
Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('home');

    // Catalog
    Route::resource('ingredientes', IngredientController::class);
    Route::resource('categorias', ProductCategoryController::class);
    Route::resource('receitas', RecipeController::class);
    Route::resource('embalagens', PackagingController::class)
        ->parameters(['embalagens' => 'embalagem'])
        ->except(['show']);
    Route::resource('produtos', ProductController::class);

    // Sales
    Route::resource('fechamentos', DailyClosingController::class)
        ->parameters(['fechamentos' => 'fechamento']);
    Route::get('fechamentos-opening-cash', [DailyClosingController::class, 'openingCash'])->name('fechamentos.opening-cash');
    Route::get('vendas', SalesDashboardController::class)->name('vendas.dashboard');

    // Finance
    Route::resource('custos-fixos', FixedCostController::class)
        ->parameters(['custos-fixos' => 'custoFixo'])
        ->except(['show']);
    Route::resource('contas', BillController::class)
        ->parameters(['contas' => 'conta']);
    Route::patch('contas/{conta}/pagar', [BillController::class, 'pay'])->name('contas.pay');
    Route::patch('contas/{conta}/desfazer-pagamento', [BillController::class, 'unpay'])->name('contas.unpay');
    Route::get('financeiro', FinanceDashboardController::class)->name('financeiro.dashboard');
    Route::resource('feriados', HolidayController::class)
        ->parameters(['feriados' => 'feriado']);
});
