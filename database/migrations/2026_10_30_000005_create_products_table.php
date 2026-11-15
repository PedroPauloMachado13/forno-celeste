<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255)->unique();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('recipe_id')->constrained('recipes')->restrictOnDelete();
            $table->unsignedInteger('packaging_cost_cents')->default(0);
            $table->unsignedInteger('sale_price_cents');
            $table->timestamps();

            $table->index('category_id');
            $table->index('recipe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
