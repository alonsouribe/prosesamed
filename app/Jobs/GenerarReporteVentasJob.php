<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Domain\Venta\VentaRepositoryInterface;
use App\Domain\Venta\CalculadoraDeSaldo;
use App\Models\Reporte;

class GenerarReporteVentasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $filtros,
        private int $reporteId
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(VentaRepositoryInterface $VentaRepository, CalculadoraDeSaldo $calculadora): void
    {
        $nombreArchivo = "reporte_{$this->reporteId}.csv";
        $rutaCompleta = storage_path("app/reportes/{$nombreArchivo}");

        if (!is_dir(storage_path('app/reportes'))) {
            mkdir(storage_path('app/reportes'), 0755, true);
        }

        $handle = fopen($rutaCompleta, 'w');
        fputcsv($handle, ['ID Venta', 'Sucursal', 'Monto', 'Deuda', 'Fecha']);

        $cursor = null;
        do {
            $ventas = $VentaRepository->listadoKeyset($this->filtros, $cursor, 5000);

            if ($ventas->isEmpty()) {
                break;
            }

            $ventaIds = $ventas->pluck('id')->toArray();
            $cotizacionIds = $ventas->pluck('id_cotizacion')->toArray();

            $pagos = $VentaRepository->buscarPago($ventaIds)->groupBy('id_venta');
            $mensualidades = $VentaRepository->buscarMensualidades($cotizacionIds)->groupBy('id_cotizacion');

            foreach ($ventas as $venta) {
                    $deuda = $calculadora->calcular(
                    $venta,
                    $pagos->get($venta->id, collect()),
                    $mensualidades->get($venta->id_cotizacion, collect())
                );

                fputcsv($handle, [$venta->id, $venta->id_sucursal, $venta->monto, $deuda, $venta->fecha_venta]);
            }

            $cursor = $ventas->last()->fecha_venta;
        } while ($ventas->count() === 5000);

        fclose($handle);

        Reporte::find($this->reporteId)->update([
            'status' => 'listo',
            'path' => "reportes/{$nombreArchivo}",
        ]);
    }
}
