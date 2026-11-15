<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table): void {
            $table->id();
            $table->date('closing_date')->unique();
            $table->unsignedInteger('card_sales_cents')->default(0);
            $table->unsignedInteger('cash_drawer_cents');
            $table->unsignedInteger('opening_cash_cents');
            $table->integer('cash_sales_cents');
            $table->unsignedInteger('total_revenue_cents');
            $table->integer('expected_cash_cents');
            $table->integer('discrepancy_cents');
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
