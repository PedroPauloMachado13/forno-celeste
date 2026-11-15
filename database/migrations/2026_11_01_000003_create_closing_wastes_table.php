<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closing_wastes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_cost_cents');
            $table->unsignedInteger('total_cost_cents');
            $table->timestamps();

            $table->index('daily_closing_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closing_wastes');
    }
};
