<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sub_recipes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['product_id', 'recipe_id']);
            $table->index('recipe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sub_recipes');
    }
};
