<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_costs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('type', 20);
            $table->unsignedInteger('default_amount_cents')->nullable();
            $table->decimal('percentage_value', 5, 2)->nullable();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_costs');
    }
};
