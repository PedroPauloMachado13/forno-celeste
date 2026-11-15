<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_cost_id')->nullable()->constrained('fixed_costs')->nullOnDelete();
            $table->string('description', 255);
            $table->unsignedInteger('amount_cents');
            $table->date('reference_month');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('fixed_cost_id');
            $table->index('reference_month');
            $table->index(['due_date', 'paid_at']);
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
