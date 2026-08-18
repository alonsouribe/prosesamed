<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Venta\VentaRepositoryInterface;
use App\Models\Venta;
use App\Models\Pago;
use App\Models\Mensualidad;
use Illuminate\Support\Collection;

class EloquentVentaRepository implements VentaRepositoryInterface
{
    public function listadoOffset(array $filtros, int $pagina, int $porPagina): Collection
    {
        // Calcula cuántos registros saltar: página 1 = 0, página 2 = $porPagina, etc.
        $offset = ($pagina - 1) * $porPagina;

        return Venta::where('id_sucursal', $filtros['sucursal'])
            ->where('status', $filtros['status'])
            ->orderBy('fecha_venta')
            ->offset($offset)
            ->limit($porPagina)
            ->get();
        }

    public function listadoKeyset(array $filtros, ?string $cursor, int $porPagina): Collection
    {
        $query = Venta::where('id_sucursal', $filtros['sucursal'])->where('status', $filtros['status']);

        if ($cursor) {
            $query->where('fecha_venta', '<', $cursor);
        }
        return $query->orderByDesc('fecha_venta')->limit($porPagina)->get();
    }

    // obtener los pagos por los ids de venta
    public function buscarPago(array $ventaIds): Collection
    {
        return Pago::whereIn('id_venta', $ventaIds)->get();
    }

    // obteber las mensualidades por los ids de cotizacion
    public function buscarMensualidades(array $cotizacionIds): Collection
    {
        return Mensualidad::whereIn('id_cotizacion', $cotizacionIds)->get();
    }
}
