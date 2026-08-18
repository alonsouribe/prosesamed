<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Venta;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $total = 500000;
        $lote = 5000;

        for ($i = 0; $i < $total / $lote; $i++) {
            $registros = Venta::factory()->count($lote)->make()->toArray();
            Venta::insert($registros);
            unset($registros);
            echo "Insertados: " . (($i + 1) * $lote) . "\n";
        }
    }
}
