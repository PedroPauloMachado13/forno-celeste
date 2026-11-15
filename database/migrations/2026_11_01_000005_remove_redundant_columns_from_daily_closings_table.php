<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table): void {
            $table->dropColumn(['notes', 'expected_cash_cents', 'discrepancy_cents']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table): void {
            $table->integer('expected_cash_cents')->default(0)->after('total_waste_cents');
            $table->integer('discrepancy_cents')->default(0)->after('expected_cash_cents');
            $table->string('notes', 500)->nullable()->after('discrepancy_cents');
        });
    }
};
