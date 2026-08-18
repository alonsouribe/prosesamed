<?php

namespace App\Domain\Venta;

use App\Models\Venta;
use Illuminate\Support\Collection;

class CalculadoraDeSaldo
{
    public function calcular(Venta $venta, Collection $pagos, Collection $mensualidades): float
    {
        $totalPagado = $pagos->sum('monto_pagado');

        // Fórmula simple para el examen
        // Aquí iría el codigo de la logica de negocio.
        $deuda = (float) $venta->monto - $totalPagado;

        return round($deuda, 2);
    }
}
