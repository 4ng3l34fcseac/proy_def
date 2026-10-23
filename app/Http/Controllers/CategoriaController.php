<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of categorias.
     */
    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'LIKE', "%{$search}%");
        }

        $categorias = $query->orderBy('nombre', 'asc')->get();

        return response()->json([
            'categorias' => $categorias,
        ], 200);
    }

    /**
     * Almacenar una nueva categoria desde el formulario web.
     * (Equivalente a store() pero devuelve un redirect para mantener la UX tradicional de Blade).
     */
    public function webStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ], [
            'nombre.required' => 'Debe ingresar el nombre de la categoria.',
            'nombre.unique'   => 'Ya existe una categoria con ese nombre.',
        ]);

        $categoria = Categoria::create($validated);

        return redirect()->route('materials.index')->with('success', 'Categoria "' . $categoria->nombre . '" registrada exitosamente.');
    }

    /**
     * Actualizar una categoria existente desde el formulario web.
     */
    public function webUpdate(Request $request, $id)
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return redirect()->route('materials.index')->with('error', 'Categoria no encontrada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
        ], [
            'nombre.required' => 'Debe ingresar el nombre de la categoria.',
            'nombre.unique'   => 'Ya existe otra categoria con ese nombre.',
        ]);

        $categoria->update($validated);

        return redirect()->route('materials.index')->with('success', 'Categoria "' . $categoria->nombre . '" actualizada exitosamente.');
    }

    /**
     * Eliminar una categoria desde el formulario web.
     * Valida que no tenga materiales asociados antes de borrar.
     */
    public function webDestroy($id)
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return redirect()->route('materials.index')->with('error', 'Categoria no encontrada.');
        }

        // Proteccion: no permitir eliminar si tiene materiales asociados
        if ($categoria->materiales()->count() > 0) {
            return redirect()->route('materials.index')->with('error', 'No se puede eliminar una categoria que tenga materiales asociados.');
        }

        $nombre = $categoria->nombre;
        $categoria->delete();

        return redirect()->route('materials.index')->with('success', 'Categoria "' . $nombre . '" eliminada exitosamente.');
    }

    /**
     * Store a newly created categoria.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json([
            'message' => 'Categoría registrada exitosamente.',
            'categoria' => $categoria,
        ], 201);
    }

    /**
     * Display the specified categoria.
     */
    public function show($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        return response()->json([
            'categoria' => $categoria,
        ], 200);
    }

    /**
     * Update the specified categoria.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
        ]);

        $categoria->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada exitosamente.',
            'categoria' => $categoria,
        ], 200);
    }

    /**
     * Remove the specified categoria.
     */
    public function destroy($id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        // No permitir eliminar si tiene materiales asociados
        if ($categoria->materiales()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene materiales asociados.',
            ], 422);
        }

        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente.',
        ], 200);
    }
}
