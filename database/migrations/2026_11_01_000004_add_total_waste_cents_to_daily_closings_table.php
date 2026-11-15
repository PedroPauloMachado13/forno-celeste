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
            $table->unsignedInteger('total_waste_cents')->default(0)->after('total_revenue_cents');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table): void {
            $table->dropColumn('total_waste_cents');
        });
    }
};
