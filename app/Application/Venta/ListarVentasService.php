<?php

namespace App\Application\Venta;

use Illuminate\Support\Collection;

use App\Domain\Venta\VentaRepositoryInterface;
use App\Domain\Venta\CalculadoraDeSaldo;

class ListarVentasService
{
    public function __construct(
        private VentaRepositoryInterface $ventaRepository,
        private CalculadoraDeSaldo $calculadoraSaldo,
    ) {}

    public function ejecutar(array $filtros, ?string $cursor, int $porPagina): array
    {
        $ventas = $this->ventaRepository->listadoKeyset($filtros, $cursor, $porPagina);

        return $this->respuesta($ventas, true);
    }

    public function ejecutarConOffset(array $filtros, int $pagina, int $porPagina): array
    {
        $ventas = $this->ventaRepository->listadoOffset($filtros, $pagina, $porPagina);

        return $this->respuesta($ventas, false);
    }


    private function respuesta(Collection $ventas, bool $usaKeyset): array
    {
        $ventaIds = $ventas->pluck('id')->toArray();
        $cotizacionIds = $ventas->pluck('id_cotizacion')->toArray();

        $pagos = $this->ventaRepository->buscarPago($ventaIds)->groupBy('id_venta');
        $mensualidades = $this->ventaRepository->buscarMensualidades($cotizacionIds)->groupBy('id_cotizacion');

        foreach ($ventas as $venta) {
            $venta->deuda = $this->calculadoraSaldo->calcular(
                $venta,
                $pagos->get($venta->id, collect()),
                $mensualidades->get($venta->id_cotizacion, collect())
            );
        }

        $resultado = ['ventas' => $ventas];

        if ($usaKeyset) {
            $resultado['siguiente_cursor'] = $ventas->last()?->fecha_venta;
        }

        return $resultado;
    }
}
