<?php

namespace App\Http\Controllers;

use App\Models\Correspondencia;
use App\Models\Material;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * HU44: Generar reporte de correspondencia.
     */
    public function reporteCorrespondencia(Request $request)
    {
        $query = Correspondencia::query();

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->input('tipo_documento'));
        }
        if ($request->filled('direccion')) {
            $query->where('direccion', $request->input('direccion'));
        }
        if ($request->filled('estado_atencion')) {
            $query->where('estado_atencion', $request->input('estado_atencion'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $correspondencias = $query->orderBy('fecha', 'desc')->get();

        $stats = [
            'total' => $correspondencias->count(),
            'ingresos' => $correspondencias->where('direccion', 'ingreso')->count(),
            'egresos' => $correspondencias->where('direccion', 'egreso')->count(),
            'pendientes' => $correspondencias->where('estado_atencion', 'pendiente')->count(),
            'en_proceso' => $correspondencias->where('estado_atencion', 'en_proceso')->count(),
            'atendidos' => $correspondencias->where('estado_atencion', 'atendido')->count(),
        ];

        return response()->json([
            'reporte' => $correspondencias,
            'estadisticas' => $stats,
            'filtros_aplicados' => $request->only(['tipo_documento', 'direccion', 'estado_atencion', 'fecha_desde', 'fecha_hasta']),
        ], 200);
    }

    /**
     * HU45: Generar reporte de inventario actual.
     */
    public function reporteInventarioActual(Request $request)
    {
        $query = Material::with('categoria');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        $materiales = $query->orderBy('nombre', 'asc')->get();

        // Identificar alertas de stock bajo
        $alertas = $materiales->filter(function ($material) {
            return $material->stock <= $material->stock_minimo;
        })->values();

        $stats = [
            'total_materiales' => $materiales->count(),
            'total_unidades' => $materiales->sum('stock'),
            'alertas_stock_bajo' => $alertas->count(),
            'valor_categorias' => $materiales->groupBy('categoria.nombre')->map->count(),
        ];

        return response()->json([
            'materiales' => $materiales,
            'alertas' => $alertas,
            'estadisticas' => $stats,
        ], 200);
    }

    /**
     * Reporte de pedidos.
     */
    public function reportePedidos(Request $request)
    {
        $query = Pedido::with(['user', 'materiales']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        $pedidos = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $pedidos->count(),
            'pendientes' => $pedidos->where('estado', 'pendiente')->count(),
            'autorizados' => $pedidos->where('estado', 'autorizado')->count(),
            'rechazados' => $pedidos->where('estado', 'rechazado')->count(),
            'despachados' => $pedidos->where('estado', 'despachado')->count(),
        ];

        return response()->json([
            'pedidos' => $pedidos,
            'estadisticas' => $stats,
        ], 200);
    }

    /**
     * Reporte de movimientos de inventario.
     */
    public function reporteMovimientos(Request $request)
    {
        $query = MovimientoInventario::with(['material.categoria', 'user']);

        if ($request->filled('material_id')) {
            $query->where('material_id', $request->input('material_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->orderBy('fecha', 'desc')->get();

        return response()->json([
            'movimientos' => $movimientos,
            'total' => $movimientos->count(),
        ], 200);
    }

    /**
     * HU46: Exportar reporte a CSV/Excel.
     */
    public function exportarCSV(Request $request, $tipo)
    {
        $filename = "reporte_{$tipo}_" . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tipo, $request) {
            $file = fopen('php://output', 'w');
            // BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            switch ($tipo) {
                case 'correspondencia':
                    fputcsv($file, ['Nro', 'N° Documento', 'Tipo', 'Dirección', 'Fecha', 'Remitente', 'Destinatario', 'Asunto', 'Estado']);
                    $data = $this->getCorrespondenciaData($request);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->numero_documento,
                            $row->tipo_documento,
                            $row->direccion,
                            $row->fecha,
                            $row->remitente,
                            $row->destinatario,
                            $row->asunto,
                            $row->estado_atencion,
                        ]);
                    }
                    break;

                case 'inventario':
                    fputcsv($file, ['Nro', 'Código', 'Nombre', 'Categoría', 'Stock', 'Stock Mínimo', 'Medida', 'Descripción']);
                    $data = $this->getInventarioData($request);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->codigo,
                            $row->nombre,
                            $row->categoria->nombre ?? '',
                            $row->stock,
                            $row->stock_minimo,
                            $row->medida,
                            $row->descripcion,
                        ]);
                    }
                    break;

                case 'pedidos':
                    fputcsv($file, ['Nro', 'Código', 'Solicitante', 'Fecha', 'Estado', 'Justificación', 'Observaciones']);
                    $data = $this->getPedidosData($request);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->codigo,
                            $row->user->name ?? '',
                            $row->created_at,
                            $row->estado,
                            $row->justificacion,
                            $row->observaciones,
                        ]);
                    }
                    break;

                case 'movimientos':
                    fputcsv($file, ['Nro', 'Fecha', 'Material', 'Tipo', 'Cantidad', 'Motivo', 'Usuario']);
                    $data = $this->getMovimientosData($request);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->fecha,
                            $row->material->nombre ?? '',
                            $row->tipo,
                            $row->cantidad,
                            $row->motivo,
                            $row->user->name ?? '',
                        ]);
                    }
                    break;

                default:
                    fputcsv($file, ['Tipo de reporte no válido']);
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getCorrespondenciaData(Request $request)
    {
        $query = Correspondencia::query();
        if ($request->filled('tipo_documento')) $query->where('tipo_documento', $request->input('tipo_documento'));
        if ($request->filled('direccion')) $query->where('direccion', $request->input('direccion'));
        if ($request->filled('estado_atencion')) $query->where('estado_atencion', $request->input('estado_atencion'));
        if ($request->filled('fecha_desde')) $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        if ($request->filled('fecha_hasta')) $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        return $query->orderBy('fecha', 'desc')->get();
    }

    private function getInventarioData(Request $request)
    {
        $query = Material::with('categoria');
        if ($request->filled('categoria_id')) $query->where('categoria_id', $request->input('categoria_id'));
        return $query->orderBy('nombre', 'asc')->get();
    }

    private function getPedidosData(Request $request)
    {
        $query = Pedido::with('user');
        if ($request->filled('estado')) $query->where('estado', $request->input('estado'));
        if ($request->filled('fecha_desde')) $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        if ($request->filled('fecha_hasta')) $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getMovimientosData(Request $request)
    {
        $query = MovimientoInventario::with(['material', 'user']);
        if ($request->filled('material_id')) $query->where('material_id', $request->input('material_id'));
        if ($request->filled('tipo')) $query->where('tipo', $request->input('tipo'));
        if ($request->filled('fecha_desde')) $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        if ($request->filled('fecha_hasta')) $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        return $query->orderBy('fecha', 'desc')->get();
    }
}
