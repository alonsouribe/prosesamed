<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_sucursal' => (string) fake()->numberBetween(1, 50),
            'status' => fake()->numberBetween(0, 1),
            'id_cotizacion' => (string) fake()->numberBetween(1, 5000),
            'monto' => fake()->randomFloat(2, 500, 50000),
            'fecha_venta' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
