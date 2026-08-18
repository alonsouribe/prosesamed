<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use App\Domain\Venta\CalculadoraDeSaldo;
use App\Models\Venta;
use Illuminate\Support\Collection;

class CalculadoraDeSaldoTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_calcula_deuda_correctamente_caso_normal(): void
    {
        $venta = new Venta(['monto' => 10000]);
        $pagos = new Collection([(object) ['monto_pagado' => 3000]]);
        $mensualidades = new Collection([]);

        $calculadora = new CalculadoraDeSaldo();
        $resultado = $calculadora->calcular($venta, $pagos, $mensualidades);

        $this->assertEquals(7000.00, $resultado);
    }

    public function test_calcula_deuda_pagada_completamente(): void
    {
        $venta = new Venta(['monto' => 8000]);
        $pagos = new Collection([(object) ['monto_pagado' => 8000]]);
        $mensualidades = new Collection([]);

        $calculadora = new CalculadoraDeSaldo();
        $resultado = $calculadora->calcular($venta, $pagos, $mensualidades);

        $this->assertEquals(0.00, $resultado);
    }
}
