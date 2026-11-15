<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable packaging_id column
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('packaging_id')
                ->nullable()
                ->after('recipe_id')
                ->constrained('packagings')
                ->restrictOnDelete();
        });

        // 2. Seed the 3 packagings
        $cookieId = DB::table('packagings')->insertGetId([
            'name' => 'Embalagem Cookie',
            'cost_cents' => 29,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $focacciaId = DB::table('packagings')->insertGetId([
            'name' => 'Embalagem Focaccia',
            'cost_cents' => 39,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paoDoceId = DB::table('packagings')->insertGetId([
            'name' => 'Embalagem Pao Doce',
            'cost_cents' => 47,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Backfill products with matching packaging
        DB::table('products')->where('packaging_cost_cents', 29)->update(['packaging_id' => $cookieId]);
        DB::table('products')->where('packaging_cost_cents', 39)->update(['packaging_id' => $focacciaId]);
        DB::table('products')->where('packaging_cost_cents', 47)->update(['packaging_id' => $paoDoceId]);

        // 4. Any products with a packaging_cost_cents not matching (0 or other values):
        //    assign the closest packaging or the first one as a fallback.
        //    For safety, assign products with 0 cost to the cheapest packaging.
        $unassigned = DB::table('products')->whereNull('packaging_id')->count();
        if ($unassigned > 0) {
            DB::table('products')->whereNull('packaging_id')->update(['packaging_id' => $cookieId]);
        }

        // 5. Make packaging_id non-nullable
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('packaging_id')
                ->nullable(false)
                ->change();
        });

        // 6. Drop the old column
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('packaging_cost_cents');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('packaging_cost_cents')->default(0)->after('recipe_id');
        });

        // Backfill from packaging relationship
        $packagings = DB::table('packagings')->get()->keyBy('id');
        DB::table('products')->get()->each(function ($product) use ($packagings): void {
            $packaging = $packagings->get($product->packaging_id);
            DB::table('products')
                ->where('id', $product->id)
                ->update(['packaging_cost_cents' => $packaging?->cost_cents ?? 0]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('packaging_id');
        });

        // Clean up seeded packagings
        DB::table('packagings')->whereIn('name', [
            'Embalagem Cookie',
            'Embalagem Focaccia',
            'Embalagem Pao Doce',
        ])->delete();
    }
};
