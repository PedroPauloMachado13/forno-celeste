<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Pedro',
            'email' => 'pedro@fornoceleste.com',
            'password' => 'fornoceleste2026',
        ]);

        User::factory()->create([
            'name' => 'Joyce',
            'email' => 'joyce@fornoceleste.com',
            'password' => 'fornoceleste2026',
        ]);

        $this->call(CatalogSeeder::class);
        $this->call(FixedCostSeeder::class);
    }
}
