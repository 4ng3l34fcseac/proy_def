<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * HU15: Registrar ingreso de materiales al inventario.
     */
    public function registrarIngreso(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materiales,id',
            'cantidad' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'motivo' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $material = Material::findOrFail($validated['material_id']);
            $material->stock += $validated['cantidad'];
            $material->save();

            $movimiento = MovimientoInventario::create([
                'material_id' => $validated['material_id'],
                'tipo' => 'ingreso',
                'cantidad' => $validated['cantidad'],
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'] ?? 'Ingreso desde oficina central',
                'user_id' => $request->user() ? $request->user()->id : null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Ingreso de material registrado exitosamente.',
                'movimiento' => $movimiento,
                'material' => $material,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar el ingreso: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HU16: Registrar salida de materiales.
     */
    public function registrarSalida(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materiales,id',
            'cantidad' => 'required|integer|min:1',
            'fecha' => 'required|date',
            'motivo' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $material = Material::findOrFail($validated['material_id']);

            if ($material->stock < $validated['cantidad']) {
                return response()->json([
                    'message' => 'Stock insuficiente. Stock actual: ' . $material->stock,
                ], 422);
            }

            $material->stock -= $validated['cantidad'];
            $material->save();

            $movimiento = MovimientoInventario::create([
                'material_id' => $validated['material_id'],
                'tipo' => 'salida',
                'cantidad' => $validated['cantidad'],
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'] ?? 'Salida manual de inventario',
                'user_id' => $request->user() ? $request->user()->id : null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Salida de material registrada exitosamente.',
                'movimiento' => $movimiento,
                'material' => $material,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la salida: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HU17: Consultar inventario actual.
     */
    public function inventarioActual(Request $request)
    {
        $query = Material::with('categoria');

        if ($request->filled('sort_by')) {
            $query->orderBy($request->input('sort_by'), $request->input('sort_dir', 'asc'));
        } else {
            $query->orderBy('stock', 'asc');
        }

        $materiales = $query->get();

        return response()->json([
            'materiales' => $materiales,
            'total_materiales' => $materiales->count(),
            'alertas_stock_bajo' => $materiales->where('stock', '<=', DB::raw('stock_minimo'))->count(),
        ], 200);
    }

    /**
     * HU18: Consultar historial de movimientos de un material.
     */
    public function historialMovimientos(Request $request, $materialId = null)
    {
        $query = MovimientoInventario::with(['material.categoria', 'user']);

        if ($materialId) {
            $query->where('material_id', $materialId);
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
        ], 200);
    }

    /**
     * HU19: Configurar alerta de stock mínimo para un material.
     */
    public function configurarStockMinimo(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'message' => 'Material no encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'stock_minimo' => 'required|integer|min:0',
        ]);

        $material->stock_minimo = $validated['stock_minimo'];
        $material->save();

        return response()->json([
            'message' => 'Stock mínimo actualizado exitosamente.',
            'material' => $material,
        ], 200);
    }

    /**
     * HU20: Visualizar alertas de stock mínimo.
     */
    public function alertasStock(Request $request)
    {
        $materiales = Material::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json([
            'alertas' => $materiales,
            'total_alertas' => $materiales->count(),
        ], 200);
    }
}
