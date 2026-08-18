<?php

namespace App\Domain\Venta;

use Illuminate\Support\Collection;

interface VentaRepositoryInterface
{
    public function listadoOffset(array $filtros, int $pagina, int $porPagina): Collection;
    public function listadoKeyset(array $filtros, ?string $cursor, int $porPagina): Collection;
    public function buscarPago(array $ventaIds): Collection;
    public function buscarMensualidades(array $cotizacionIds): Collection;
}
