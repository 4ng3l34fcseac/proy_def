<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of materiales.
     */
    public function index(Request $request)
    {
        $query = Material::with('categoria');

        // Buscar por nombre o código (HU12)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('codigo', 'LIKE', "%{$search}%");
            });
        }

        // Filtrar por categoría (HU13)
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->input('categoria_id'));
        }

        // Ordenar por stock ascendente por defecto
        $sortBy = $request->input('sort_by', 'nombre');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $materiales = $query->get();

        return response()->json([
            'materiales' => $materiales,
        ], 200);
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:materiales,codigo',
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'medida' => 'nullable|string|max:50',
        ]);

        $material = Material::create($validated);
        $material->load('categoria');

        return response()->json([
            'message' => 'Material registrado exitosamente.',
            'material' => $material,
        ], 201);
    }

    /**
     * Display the specified material (HU14).
     */
    public function show($id)
    {
        $material = Material::with('categoria')->find($id);

        if (!$material) {
            return response()->json([
                'message' => 'Material no encontrado.',
            ], 404);
        }

        return response()->json([
            'material' => $material,
        ], 200);
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'message' => 'Material no encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:materiales,codigo,' . $material->id,
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'medida' => 'nullable|string|max:50',
        ]);

        $material->update($validated);
        $material->load('categoria');

        return response()->json([
            'message' => 'Material actualizado exitosamente.',
            'material' => $material,
        ], 200);
    }

    /**
     * Remove the specified material (BAJA LOGICA).
     * En lugar de eliminar fisicamente, cambia el estado a 'Inactivo'
     * para preservar el historial de pedidos y movimientos.
     * (La baja fisica seria solo un caso extremo desde BD).
     */
    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'message' => 'Material no encontrado.',
            ], 404);
        }

        if ($material->pedidos()->count() > 0) {
            $material->update(['estado' => 'Inactivo']);
            return response()->json([
                'message' => 'El material se ha desactivado correctamente porque tiene pedidos anteriores. Ya no estara disponible para nuevas solicitudes, pero su historial se mantendra guardado.',
            ], 200);
        }

        $material->delete();
        return response()->json([
            'message' => 'Material eliminado correctamente.',
        ], 200);
    }

    /**
     * Vista web del módulo Productos (solo Administrador).
     * Carga directa desde Eloquent (MVC tradicional, sin API).
     */
    public function webIndex()
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para acceder al módulo de Productos. Esta función es exclusiva del Administrador.');
        }

        // El catalogo muestra todos los materiales (incluidos los inactivos) para que
        // el Administrador pueda ver el historial y reactivarlos si es necesario.
        // Los inactivos se distinguen con un badge y no se pueden seleccionar en pedidos.
        $materials = Material::with('categoria')->orderBy('nombre', 'asc')->get();
        $categorias = \App\Models\Categoria::orderBy('nombre', 'asc')->get();

        // Alerta de stock bajo: productos activos con stock <= stock_minimo
        $stockBajo = $materials->filter(function ($m) {
            return $m->estado === 'Activo' && $m->stock <= $m->stock_minimo;
        });

        return view('materials.index', compact('materials', 'categorias', 'stockBajo'));
    }

    /**
     * Generar el siguiente codigo correlativo con formato MAT-XXXX.
     */
    private function generarCodigoMaterial(): string
    {
        $ultimo = Material::where('codigo', 'LIKE', 'MAT-%')
            ->orderByRaw("CAST(SUBSTRING(codigo, 5) AS UNSIGNED) DESC")
            ->value('codigo');

        $siguiente = 1;
        if ($ultimo && preg_match('/MAT-(\d+)/', $ultimo, $matches)) {
            $siguiente = ((int) $matches[1]) + 1;
        }

        return 'MAT-' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Guardar un nuevo material (POST tradicional del formulario web).
     * El codigo se genera automaticamente con formato MAT-XXXX.
     */
    public function webStore(Request $request)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'descripcion'   => 'nullable|string',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'medida'        => 'nullable|string|max:50',
        ]);

        // Forzar MAYUSCULAS en los campos de texto antes de persistir
        $validated['nombre']      = mb_strtoupper(trim($validated['nombre']));
        $validated['descripcion'] = isset($validated['descripcion']) ? mb_strtoupper(trim($validated['descripcion'])) : null;
        $validated['medida']      = isset($validated['medida']) ? mb_strtoupper(trim($validated['medida'])) : null;
        $validated['codigo']      = $this->generarCodigoMaterial();

        Material::create($validated);

        return redirect()->route('materials.index')->with('success', 'Material registrado con éxito. Código asignado: ' . $validated['codigo']);
    }

    /**
     * Mostrar formulario de edición de un material.
     */
    public function webEdit($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para editar materiales.');
        }
        $material = Material::findOrFail($id);
        $categorias = \App\Models\Categoria::orderBy('nombre', 'asc')->get();
        return view('materials.edit', compact('material', 'categorias'));
    }

    /**
     * Actualizar un material existente (PUT tradicional).
     */
    public function webUpdate(Request $request, $id)
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para editar materiales.');
        }
        $material = Material::findOrFail($id);
        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'descripcion'   => 'nullable|string',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'medida'        => 'nullable|string|max:50',
        ]);
        // Forzar MAYUSCULAS en los campos de texto antes de persistir
        $validated['nombre']      = mb_strtoupper(trim($validated['nombre']));
        $validated['descripcion'] = isset($validated['descripcion']) ? mb_strtoupper(trim($validated['descripcion'])) : null;
        $validated['medida']      = isset($validated['medida']) ? mb_strtoupper(trim($validated['medida'])) : null;
        $material->update($validated);
        return redirect()->route('materials.index')->with('success', 'Los cambios se guardaron correctamente.');
    }

    /**
     * Eliminar un material (DELETE tradicional) -> BAJA LOGICA.
     * Si tiene pedidos asociados, NO se elimina de la tabla.
     * En su lugar, se cambia su estado a 'Inactivo' para que:
     *   - Deje de aparecer en el catalogo de solicitudes.
     *   - Se conserve el historial en reportes y movimientos de inventario.
     */
    public function webDestroy($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para eliminar materiales.');
        }

        $material = Material::findOrFail($id);

        if ($material->pedidos()->count() > 0) {
            $material->update(['estado' => 'Inactivo']);
            return redirect()->route('materials.index')->with('success', 'El material se ha desactivado correctamente porque tiene pedidos anteriores. Ya no estara disponible para nuevas solicitudes, pero su historial se mantendra guardado.');
        }

        $nombre = $material->nombre;
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'El material "' . $nombre . '" se elimino correctamente.');
    }

    /**
     * Reactivar un material dado de baja logica (PUT tradicional).
     * Cambia el estado de 'Inactivo' a 'Activo' para que vuelva al catalogo.
     */
    public function webReactivar($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para reactivar materiales.');
        }

        $material = Material::findOrFail($id);
        $material->update(['estado' => 'Activo']);

        return redirect()->route('materials.index')->with('success', 'El material "' . $material->nombre . '" se ha reactivado y vuelve a estar disponible para solicitudes.');
    }

    public function exportarExcel()
    {
        $materiales = \App\Models\Material::with('categoria')->orderBy('nombre', 'asc')->get();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; Filename=Reporte_Inventario_Oruro.xls");
        return view('materials.print_excel', compact('materiales'));
    }

    public function exportarWord()
    {
        $materiales = \App\Models\Material::with('categoria')->orderBy('nombre', 'asc')->get();
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment; Filename=Reporte_Inventario_Oruro.doc");
        return view('materials.print_excel', compact('materiales'));
    }

    public function exportarPDF()
    {
        $materiales = \App\Models\Material::with('categoria')->orderBy('nombre', 'asc')->get();
        return view('materials.print_excel', compact('materiales'))->with('autoPrint', true);
    }
}
