<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    /**
     * Generar código correlativo de pedido.
     */
    private function generarCodigo()
    {
        $year = date('Y');
        $ultimo = Pedido::where('codigo', 'LIKE', "PED-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimo && preg_match('/PED-' . $year . '-(\d+)/', $ultimo->codigo, $matches)) {
            $next = intval($matches[1]) + 1;
        } else {
            $next = 1;
        }

        return 'PED-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * HU37: Ver mis pedidos realizados (Funcionario) / Listar todos (Admin).
     */
    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'materiales.categoria']);

        // Si es funcionario, solo ver sus propios pedidos
        $user = $request->user();
        if ($user && $user->role === 'funcionario') {
            $query->where('user_id', $user->id);
        }

        // Filtrar por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        // Filtrar por usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filtrar por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        $pedidos = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'pedidos' => $pedidos,
            'total' => $pedidos->count(),
        ], 200);
    }

    /**
     * HU34, HU35, HU36: Realizar pedido de materiales (con múltiples materiales).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'justificacion' => 'required|string|min:5|max:1000', // HU4
            'materiales' => 'required|array|min:1',
            'materiales.*.material_id' => 'required|exists:materiales,id',
            'materiales.*.cantidad_solicitada' => 'required|integer|min:1',
        ], [
            'justificacion.required' => 'Debe ingresar una justificación para el pedido.',
            'justificacion.min' => 'La justificación debe tener al menos 5 caracteres.',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Debe estar autenticado para realizar un pedido.',
            ], 401);
        }

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'codigo' => $this->generarCodigo(),
                'user_id' => $user->id,
                'justificacion' => $validated['justificacion'],
                'estado' => 'pendiente',
            ]);

            foreach ($validated['materiales'] as $item) {
                PedidoMaterial::create([
                    'pedido_id' => $pedido->id,
                    'material_id' => $item['material_id'],
                    'cantidad_solicitada' => $item['cantidad_solicitada'],
                ]);
            }

            DB::commit();

            $pedido->load(['user', 'materiales.categoria']);

            return response()->json([
                'message' => 'Pedido registrado exitosamente.',
                'pedido' => $pedido,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HU38: Ver estado de un pedido específico.
     */
    public function show(Request $request, $id)
    {
        $pedido = Pedido::with(['user', 'materiales.categoria'])->find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        // Si es funcionario, solo puede ver sus propios pedidos
        $user = $request->user();
        if ($user && $user->role === 'funcionario' && $pedido->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permisos para ver este pedido.',
            ], 403);
        }

        return response()->json([
            'pedido' => $pedido,
        ], 200);
    }

    /**
     * HU36: Modificar un pedido antes de enviar (solo en estado pendiente).
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        // Solo el dueño puede modificar su pedido y solo si está pendiente
        $user = $request->user();
        if (!$user || $pedido->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permisos para modificar este pedido.',
            ], 403);
        }

        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden modificar pedidos en estado pendiente.',
            ], 422);
        }

        $validated = $request->validate([
            'justificacion' => 'required|string|min:5|max:1000',
            'materiales' => 'required|array|min:1',
            'materiales.*.material_id' => 'required|exists:materiales,id',
            'materiales.*.cantidad_solicitada' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $pedido->justificacion = $validated['justificacion'];
            $pedido->save();

            // Eliminar items anteriores y crear nuevos
            $pedido->items()->delete();

            foreach ($validated['materiales'] as $item) {
                PedidoMaterial::create([
                    'pedido_id' => $pedido->id,
                    'material_id' => $item['material_id'],
                    'cantidad_solicitada' => $item['cantidad_solicitada'],
                ]);
            }

            DB::commit();

            $pedido->load(['user', 'materiales.categoria']);

            return response()->json([
                'message' => 'Pedido actualizado exitosamente.',
                'pedido' => $pedido,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HU39: Visualizar bandeja de pedidos pendientes (Admin).
     */
    public function pedidosPendientes(Request $request)
    {
        $pedidos = Pedido::with(['user', 'materiales.categoria'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'pedidos' => $pedidos,
            'total' => $pedidos->count(),
        ], 200);
    }

    /**
     * HU40: Ver detalle de un pedido pendiente (Admin).
     */
    public function showPendiente($id)
    {
        $pedido = Pedido::with(['user', 'materiales.categoria'])->find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        return response()->json([
            'pedido' => $pedido,
        ], 200);
    }

    /**
     * HU41: Autorizar un pedido (Admin).
     * HU16: Automáticamente descuenta el stock del inventario.
     */
    public function autorizar(Request $request, $id)
    {
        $pedido = Pedido::with('materiales')->find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden autorizar pedidos en estado pendiente.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Verificar stock disponible para todos los materiales
            foreach ($pedido->materiales as $material) {
                if ($material->stock < $material->pivot->cantidad_solicitada) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Stock insuficiente para '{$material->nombre}'. Stock actual: {$material->stock}, solicitado: {$material->pivot->cantidad_solicitada}",
                    ], 422);
                }
            }

            // Descontar stock y registrar movimientos
            foreach ($pedido->materiales as $material) {
                $cantidad = $material->pivot->cantidad_solicitada;

                $material->stock -= $cantidad;
                $material->save();

                MovimientoInventario::create([
                    'material_id' => $material->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'fecha' => now(),
                    'motivo' => "Pedido autorizado: {$pedido->codigo}",
                    'user_id' => $request->user() ? $request->user()->id : null,
                ]);
            }

            $pedido->estado = 'autorizado';
            $pedido->save();

            DB::commit();

            $pedido->load(['user', 'materiales.categoria']);

            return response()->json([
                'message' => 'Pedido autorizado exitosamente. Stock actualizado.',
                'pedido' => $pedido,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al autorizar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HU43: Registrar despacho de materiales (Admin).
     */
    public function despachar(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        if ($pedido->estado !== 'autorizado') {
            return response()->json([
                'message' => 'Solo se pueden despachar pedidos autorizados.',
            ], 422);
        }

        $validated = $request->validate([
            'fecha_entrega' => 'required|date',
            'recibido_por' => 'required|string|max:255',
        ]);

        $pedido->estado = 'despachado';
        $pedido->fecha_entrega = $validated['fecha_entrega'];
        $pedido->recibido_por = $validated['recibido_por'];
        $pedido->save();

        $pedido->load(['user', 'materiales.categoria']);

        return response()->json([
            'message' => 'Despacho registrado exitosamente.',
            'pedido' => $pedido,
        ], 200);
    }

    /**
     * Eliminar un pedido (solo si está pendiente y es del propio usuario).
     */
    public function destroy(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'No autorizado.',
            ], 401);
        }

        // Solo el dueño puede eliminar su pedido y solo si está pendiente
        if ($user->role === 'funcionario' && $pedido->user_id !== $user->id) {
            return response()->json([
                'message' => 'No tiene permisos para eliminar este pedido.',
            ], 403);
        }

        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden eliminar pedidos en estado pendiente.',
            ], 422);
        }

        $pedido->delete();

        return response()->json([
            'message' => 'Pedido eliminado exitosamente.',
        ], 200);
    }

    /**
     * Vista web del módulo Pedidos (todos los roles).
     */
    public function webIndex(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && $currentUser->role === 'Administrador';

        $query = Pedido::with(['user', 'materiales.categoria']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('justificacion', 'LIKE', "%{$search}%");
            });
        }

        if (!$isAdmin) {
            $query->where('user_id', $currentUser->id);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->get();

        // Solo materiales activos para el filtro del panel
        $materiales = Material::activos()->orderBy('nombre', 'asc')->get();

        return view('orders.index', compact('pedidos', 'isAdmin', 'materiales'));
    }

    /**
     * Mostrar formulario para crear un nuevo pedido (POST tradicional).
     * Solo se cargan los materiales ACTIVOS (los inactivos quedan ocultos del catalogo).
     */
    public function webCreate()
    {
        $materiales = Material::activos()->orderBy('nombre', 'asc')->get();
        return view('orders.create', compact('materiales'));
    }

    /**
     * Guardar un nuevo pedido (cualquier rol, incluyendo Administrador).
     */
    public function webStore(Request $request)
    {
        $validated = $request->validate([
            'justificacion'                   => 'required|string|min:5|max:1000',
            'materiales'                      => 'required|array|min:1',
            'materiales.*.material_id'        => 'required|exists:materiales,id',
            'materiales.*.cantidad_solicitada' => 'required|integer|min:1',
        ], [
            'materiales.*.material_id.exists' => 'Uno o mas materiales seleccionados no existen o estan inactivos.',
        ]);

        // Defensa en profundidad: rechazar pedidos de materiales inactivos
        $inactivos = Material::whereIn('id', collect($validated['materiales'])->pluck('material_id'))
            ->where('estado', 'Inactivo')
            ->pluck('nombre');
        if ($inactivos->count() > 0) {
            return back()->withErrors(['error' => 'No se puede(n) pedir material(es) inactivo(s): ' . $inactivos->implode(', ')])->withInput();
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'codigo'         => $this->generarCodigo(),
                'user_id'        => $user->id,
                'justificacion'  => mb_strtoupper(trim($validated['justificacion'])),
                'estado'         => 'pendiente',
            ]);
            foreach ($validated['materiales'] as $item) {
                PedidoMaterial::create([
                    'pedido_id'            => $pedido->id,
                    'material_id'          => $item['material_id'],
                    'cantidad_solicitada'  => $item['cantidad_solicitada'],
                ]);
            }
            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Pedido registrado exitosamente. Código: ' . $pedido->codigo);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pedido: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Autorizar pedido desde la vista web (POST tradicional).
     * Descuenta el stock automáticamente.
     */
    public function webAutorizar($id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $pedido = Pedido::with('materiales')->findOrFail($id);
        if ($pedido->estado !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden aprobar pedidos en estado pendiente.');
        }
        DB::beginTransaction();
        try {
            foreach ($pedido->materiales as $material) {
                if ($material->stock < $material->pivot->cantidad_solicitada) {
                    DB::rollBack();
                    return back()->with('error', "Stock insuficiente para '{$material->nombre}'. Stock actual: {$material->stock}, solicitado: {$material->pivot->cantidad_solicitada}");
                }
            }
            foreach ($pedido->materiales as $material) {
                $cantidad = $material->pivot->cantidad_solicitada;
                $material->stock -= $cantidad;
                $material->save();
                MovimientoInventario::create([
                    'material_id' => $material->id,
                    'tipo'        => 'salida',
                    'cantidad'    => $cantidad,
                    'fecha'       => now(),
                    'motivo'      => "Pedido autorizado: {$pedido->codigo}",
                    'user_id'     => auth()->id(),
                ]);
            }
            $pedido->estado = 'Autorizado';
            $pedido->save();
            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Pedido autorizado y stock descontado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al autorizar: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar pedido con observaciones desde la vista web.
     */
    public function webRechazar(Request $request, $id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $request->validate([
            'motivo_rechazo' => 'required|string|max:255'
        ]);

        $pedido = \App\Models\Pedido::findOrFail($id);
        if ($pedido->estado !== 'Pendiente') {
            return back()->with('error', 'Solo se pueden rechazar pedidos en estado pendiente.');
        }

        $pedido->estado = 'RECHAZADO';
        $pedido->observaciones = mb_strtoupper($request->motivo_rechazo);
        $pedido->save();

        return redirect()->back()->with('success', 'SOLICITUD RECHAZADA CORRECTAMENTE CON SU RESPECTIVA OBSERVACIÓN.');
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|max:255'
        ]);

        $pedido = \App\Models\Pedido::findOrFail($id);
        $pedido->estado = 'RECHAZADO';
        $pedido->observaciones = mb_strtoupper($request->motivo_rechazo);
        $pedido->save();

        return redirect()->back()->with('success', 'SOLICITUD RECHAZADA CORRECTAMENTE CON SU OBSERVACIÓN.');
    }

    // 1. EXPORTAR A WORD CON EL FORMATO OFICIAL DGAF/UADM-R-23
    public function exportarWord($id)
    {
        $pedido = \App\Models\Pedido::with(['materiales', 'user'])->findOrFail($id);
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment; Filename=Solicitud_Materiales_".strtoupper($pedido->codigo).".doc");
        return view('orders.print_formato', compact('pedido'));
    }

    public function exportarExcel()
    {
        $materiales = \App\Models\Material::with('categoria')->orderBy('nombre', 'asc')->get();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; Filename=Reporte_Inventario_Oruro.xls");
        return view('materials.print_excel', compact('materiales'));
    }

    public function exportarPDF($id)
    {
        $pedido = \App\Models\Pedido::with(['materiales', 'user'])->findOrFail($id);
        return view('orders.print_formato', compact('pedido'))->with('autoPrint', true);
    }
}
