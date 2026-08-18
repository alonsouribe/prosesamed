<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Application\Venta\ListarVentasService;
use App\Http\Requests\ListarVentasRequest;

use App\Jobs\GenerarReporteVentasJob;
use App\Models\Reporte;

class VentasController extends Controller
{
    public function __construct(private ListarVentasService $listarVentasService) {

    }

    public function index(ListarVentasRequest $request)
    {
        $resultado = $this->listarVentasService->ejecutar(
            $request->validated(),
            $request->query('cursor'),
            (int) $request->query('por_pagina', 50)
        );

        return response()->json($resultado);
    }

    public function indexOffset(ListarVentasRequest $request)
    {
        $resultado = $this->listarVentasService->ejecutarConOffset(
            $request->validated(),
            (int) $request->query('pagina', 1),
            (int) $request->query('por_pagina', 50)
        );

        return response()->json($resultado);
    }

    public function excel(ListarVentasRequest $request)
    {
        $reporte = Reporte::create(['status' => 'procesando']);

        GenerarReporteVentasJob::dispatch($request->validated(), $reporte->id);

        return response()->json([
            'reporte_id' => $reporte->id,
            'mensaje' => 'Tu reporte se está generando. Consulta el estado con el ID proporcionado.',
        ]);
    }

    public function descargarReporte(int $id)
    {
        $reporte = Reporte::findOrFail($id);

        if ($reporte->status !== 'listo') {
            return response()->json(['status' => $reporte->status]);
        }

        return response()->download(storage_path("app/{$reporte->path}"));
    }

}
