<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('type', 10);
            $table->unsignedInteger('yield_quantity');
            $table->unsignedInteger('packaging_cost_per_unit_cents')->default(0);
            $table->boolean('is_experimental')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
