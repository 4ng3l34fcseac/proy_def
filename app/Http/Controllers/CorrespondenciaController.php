<?php

namespace App\Http\Controllers;

use App\Models\Correspondencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CorrespondenciaController extends Controller
{
    /**
     * HU27: Listar toda la correspondencia (HU27, HU28, HU29, HU30, HU31).
     */
    public function index(Request $request)
    {
        $query = Correspondencia::query();

        // HU28: Buscar por número de documento
        if ($request->filled('numero_documento')) {
            $query->where('numero_documento', 'LIKE', '%' . $request->input('numero_documento') . '%');
        }

        // HU29: Buscar por remitente
        if ($request->filled('remitente')) {
            $query->where('remitente', 'LIKE', '%' . $request->input('remitente') . '%');
        }

        // HU30: Filtrar por tipo de documento
        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->input('tipo_documento'));
        }

        // Filtrar por dirección (ingreso/egreso)
        if ($request->filled('direccion')) {
            $query->where('direccion', $request->input('direccion'));
        }

        // HU31: Filtrar por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        // Filtrar por estado de atención
        if ($request->filled('estado_atencion')) {
            $query->where('estado_atencion', $request->input('estado_atencion'));
        }

        $correspondencias = $query->orderBy('fecha', 'desc')->get();

        return response()->json([
            'correspondencias' => $correspondencias,
            'total' => $correspondencias->count(),
        ], 200);
    }

    /**
     * HU21: Registrar correspondencia de ingreso.
     */
    public function storeIngreso(Request $request)
    {
        $validated = $request->validate([
            'numero_documento' => 'required|string|max:255',
            'tipo_documento' => ['required', Rule::in(['nota_interna', 'nota_externa', 'informe', 'instructivo', 'memorandum'])],
            'fecha' => 'required|date',
            'remitente' => 'required|string|max:255',
            'destinatario' => 'required|string|max:255',
            'asunto' => 'required|string|max:500',
            'archivo_adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
        ]);

        $validated['direccion'] = 'ingreso';
        $validated['estado_atencion'] = 'pendiente';

        if ($request->hasFile('archivo_adjunto')) {
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $validated['archivo_adjunto'] = $filename;
        }

        $correspondencia = Correspondencia::create($validated);

        return response()->json([
            'message' => 'Correspondencia de ingreso registrada exitosamente.',
            'correspondencia' => $correspondencia,
        ], 201);
    }

    /**
     * HU22: Registrar correspondencia de egreso.
     */
    public function storeEgreso(Request $request)
    {
        $validated = $request->validate([
            'numero_documento' => 'required|string|max:255',
            'tipo_documento' => ['required', Rule::in(['nota_interna', 'nota_externa', 'informe', 'instructivo', 'memorandum'])],
            'fecha' => 'required|date',
            'remitente' => 'required|string|max:255',
            'destinatario' => 'required|string|max:255',
            'asunto' => 'required|string|max:500',
            'archivo_adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
        ]);

        $validated['direccion'] = 'egreso';
        $validated['estado_atencion'] = 'atendido'; // Por defecto ya está atendido al egresar

        if ($request->hasFile('archivo_adjunto')) {
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $validated['archivo_adjunto'] = $filename;
        }

        $correspondencia = Correspondencia::create($validated);

        return response()->json([
            'message' => 'Correspondencia de egreso registrada exitosamente.',
            'correspondencia' => $correspondencia,
        ], 201);
    }

    /**
     * HU32: Ver detalle de un documento.
     */
    public function show($id)
    {
        $correspondencia = Correspondencia::find($id);

        if (!$correspondencia) {
            return response()->json([
                'message' => 'Correspondencia no encontrada.',
            ], 404);
        }

        return response()->json([
            'correspondencia' => $correspondencia,
        ], 200);
    }

    /**
     * HU25: Modificar registro de correspondencia.
     */
    public function update(Request $request, $id)
    {
        $correspondencia = Correspondencia::find($id);

        if (!$correspondencia) {
            return response()->json([
                'message' => 'Correspondencia no encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'numero_documento' => 'required|string|max:255',
            'tipo_documento' => ['required', Rule::in(['nota_interna', 'nota_externa', 'informe', 'instructivo', 'memorandum'])],
            'fecha' => 'required|date',
            'remitente' => 'required|string|max:255',
            'destinatario' => 'required|string|max:255',
            'asunto' => 'required|string|max:500',
            'estado_atencion' => ['nullable', Rule::in(['pendiente', 'en_proceso', 'atendido'])],
            'archivo_adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('archivo_adjunto')) {
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $validated['archivo_adjunto'] = $filename;
        }

        $correspondencia->update($validated);

        return response()->json([
            'message' => 'Correspondencia actualizada exitosamente.',
            'correspondencia' => $correspondencia,
        ], 200);
    }

    /**
     * HU26: Eliminar registro de correspondencia.
     */
    public function destroy($id)
    {
        $correspondencia = Correspondencia::find($id);

        if (!$correspondencia) {
            return response()->json([
                'message' => 'Correspondencia no encontrada.',
            ], 404);
        }

        $correspondencia->delete();

        return response()->json([
            'message' => 'Correspondencia eliminada exitosamente.',
        ], 200);
    }

    /**
     * HU33: Cambiar estado de atención de un documento.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $correspondencia = Correspondencia::find($id);

        if (!$correspondencia) {
            return response()->json([
                'message' => 'Correspondencia no encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'estado_atencion' => ['required', Rule::in(['pendiente', 'en_proceso', 'atendido'])],
        ]);

        $correspondencia->estado_atencion = $validated['estado_atencion'];
        $correspondencia->save();

        return response()->json([
            'message' => 'Estado de atención actualizado exitosamente.',
            'correspondencia' => $correspondencia,
        ], 200);
    }

    /**
     * HU24: Adjuntar archivo a un documento existente.
     */
    public function adjuntarArchivo(Request $request, $id)
    {
        $correspondencia = Correspondencia::find($id);

        if (!$correspondencia) {
            return response()->json([
                'message' => 'Correspondencia no encontrada.',
            ], 404);
        }

        $request->validate([
            'archivo_adjunto' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('archivo_adjunto')) {
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $correspondencia->archivo_adjunto = $filename;
            $correspondencia->save();
        }

        return response()->json([
            'message' => 'Archivo adjuntado exitosamente.',
            'correspondencia' => $correspondencia,
        ], 200);
    }

    /**
     * Vista web del módulo Cites / Correspondencia.
     * Búsqueda, filtros por tipo, dirección y estado de atención.
     * Seguridad por rol: el administrador ve todos los CITES;
     * el funcionario SOLO ve los CITES donde figura como remitente
     * o destinatario (comparando por nombre en MAYÚSCULAS).
     */
    public function webIndex(Request $request)
    {
        $query = Correspondencia::query();

        // ===== Seguridad por rol =====
        $user = auth()->user();
        if (strtoupper($user->role) === 'ADMINISTRADOR' || $user->role === 'Administrador') {
            // El Administrador visualiza absolutamente todas las correspondencias del sistema
        } else {
            // El Funcionario visualiza estrictamente los CITES que él mismo registró
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_documento', 'LIKE', "%{$search}%")
                  ->orWhere('remitente', 'LIKE', "%{$search}%")
                  ->orWhere('destinatario', 'LIKE', "%{$search}%")
                  ->orWhere('asunto', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('remitente')) {
            $query->where('remitente', 'LIKE', '%' . $request->remitente . '%');
        }
        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }
        if ($request->filled('direccion')) {
            $query->where('direccion', $request->direccion);
        }
        if ($request->filled('estado_atencion')) {
            $query->where('estado_atencion', $request->estado_atencion);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $correspondencias = $query->with('user')->orderBy('created_at', 'desc')->get();

        return view('correspondences.index', compact('correspondencias'));
    }

    /**
     * Mostrar formulario de creación de correspondencia.
     * Carga los usuarios ACTIVOS del sistema para alimentar el select
     * de Remitente/Destinatario cuando el tipo es "Nota Interna".
     */
    public function webCreate()
    {
        $usuarios = \App\Models\User::where('status', 'Activo')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'cargo']);
        return view('correspondences.create', compact('usuarios'));
    }

    /**
     * Generar el siguiente código correlativo según el tipo de documento.
     * Devuelve JSON con la clave 'codigo' (ej: INF-0001, MEM-0001, NOT-0001).
     */
    public function nextCode(Request $request)
    {
        $prefijos = [
            'nota_interna' => 'NIN',
            'nota_externa' => 'NEX',
            'informe'      => 'INF',
            'instructivo'  => 'INS',
            'memorandum'   => 'MEM',
        ];
        $tipo = $request->input('tipo');
        if (!$tipo || !isset($prefijos[$tipo])) {
            return response()->json(['codigo' => null], 400);
        }
        $prefijo = $prefijos[$tipo];
        // Buscar el último código con ese prefijo
        $ultimo = Correspondencia::where('numero_documento', 'LIKE', $prefijo . '-%')
            ->orderByRaw("LENGTH(numero_documento) DESC, numero_documento DESC")
            ->value('numero_documento');
        $siguiente = 1;
        if ($ultimo && preg_match('/^' . $prefijo . '-(\d+)$/', $ultimo, $matches)) {
            $siguiente = ((int) $matches[1]) + 1;
        }
        $codigo = $prefijo . '-' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
        return response()->json(['codigo' => $codigo], 200);
    }

    /**
     * Guardar nueva correspondencia (POST tradicional con archivo adjunto).
     * Reglas:
     *  - El número de documento se autocalcula server-side con prefijo por tipo (ignora lo enviado).
     *  - remitente / destinatario / asunto se fuerzan a MAYÚSCULAS antes de guardar.
     *  - El archivo adjunto es OBLIGATORIO.
     *  - Si es Nota Externa, el remitente se fuerza a "DEFENSORIA DEL PUEBLO".
     */
    public function webStore(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento'   => 'required|in:nota_interna,nota_externa,informe,instructivo,memorandum',
            'direccion'        => 'required|in:ingreso,egreso',
            'fecha'            => 'required|date',
            'remitente'        => 'required|string|max:255',
            'destinatario'     => 'required|string|max:255',
            'asunto'           => 'required|string|max:500',
            'archivo_adjunto'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'archivo_adjunto.required' => 'La subida del archivo adjunto es obligatoria.',
            'archivo_adjunto.mimes'    => 'El archivo debe ser PDF, JPG, JPEG o PNG.',
            'archivo_adjunto.max'      => 'El archivo no debe superar los 5MB.',
        ]);

        // Forzar MAYUSCULAS en remitente / destinatario / asunto
        $validated['remitente']    = mb_strtoupper(trim($validated['remitente']));
        $validated['destinatario'] = mb_strtoupper(trim($validated['destinatario']));
        $validated['asunto']       = mb_strtoupper(trim($validated['asunto']));

        // Regla especial: Nota Externa => remitente fijo
        if ($validated['tipo_documento'] === 'nota_externa') {
            $validated['remitente'] = 'DEFENSORIA DEL PUEBLO';
        }

        // Generar codigo correlativo automaticamente (server-side, ignora lo del cliente)
        $validated['numero_documento'] = $this->generarCodigoPorTipo($validated['tipo_documento']);
        $validated['estado_atencion']  = $validated['direccion'] === 'egreso' ? 'atendido' : 'pendiente';

        if ($request->hasFile('archivo_adjunto')) {
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $validated['archivo_adjunto'] = $filename;
        }

        Correspondencia::create(array_merge($validated, ['user_id' => auth()->id()]));

        return redirect()->route('correspondences.index')->with('success', 'Correspondencia registrada exitosamente. CITE: ' . $validated['numero_documento']);
    }

    /**
     * Genera el siguiente codigo correlativo segun el tipo.
     */
    private function generarCodigoPorTipo(string $tipo): string
    {
        $prefijos = [
            'nota_interna' => 'NIN',
            'nota_externa' => 'NEX',
            'informe'      => 'INF',
            'instructivo'  => 'INS',
            'memorandum'   => 'MEM',
        ];
        $prefijo = $prefijos[$tipo] ?? 'GEN';
        $ultimo = Correspondencia::where('numero_documento', 'LIKE', $prefijo . '-%')
            ->orderByRaw("LENGTH(numero_documento) DESC, numero_documento DESC")
            ->value('numero_documento');
        $siguiente = 1;
        if ($ultimo && preg_match('/^' . $prefijo . '-(\d+)$/', $ultimo, $matches)) {
            $siguiente = ((int) $matches[1]) + 1;
        }
        return $prefijo . '-' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function webEdit($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        return view('correspondences.edit', compact('correspondencia'));
    }

    /**
     * Actualizar correspondencia (PUT tradicional).
     * - El número de documento NO se modifica (es inmutable tras creación).
     * - remitente / destinatario / asunto se fuerzan a MAYÚSCULAS.
     * - El archivo adjunto es OBLIGATORIO en cada edición.
     */
    public function webUpdate(Request $request, $id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $validated = $request->validate([
            'tipo_documento'   => 'required|in:nota_interna,nota_externa,informe,instructivo,memorandum',
            'direccion'        => 'required|in:ingreso,egreso',
            'fecha'            => 'required|date',
            'remitente'        => 'required|string|max:255',
            'destinatario'     => 'required|string|max:255',
            'asunto'           => 'required|string|max:500',
            'estado_atencion'  => 'nullable|in:pendiente,en_proceso,atendido',
            'archivo_adjunto'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'archivo_adjunto.required' => 'La subida del archivo adjunto es obligatoria.',
            'archivo_adjunto.mimes'    => 'El archivo debe ser PDF, JPG, JPEG o PNG.',
            'archivo_adjunto.max'      => 'El archivo no debe superar los 5MB.',
        ]);

        // Forzar MAYUSCULAS en remitente / destinatario / asunto
        $validated['remitente']    = mb_strtoupper(trim($validated['remitente']));
        $validated['destinatario'] = mb_strtoupper(trim($validated['destinatario']));
        $validated['asunto']       = mb_strtoupper(trim($validated['asunto']));

        // Regla especial: Nota Externa => remitente fijo
        if ($validated['tipo_documento'] === 'nota_externa') {
            $validated['remitente'] = 'DEFENSORIA DEL PUEBLO';
        }

        if ($request->hasFile('archivo_adjunto')) {
            if ($correspondencia->archivo_adjunto && \Storage::disk('public')->exists('correspondencia/' . $correspondencia->archivo_adjunto)) {
                \Storage::disk('public')->delete('correspondencia/' . $correspondencia->archivo_adjunto);
            }
            $file = $request->file('archivo_adjunto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('correspondencia', $filename, 'public');
            $validated['archivo_adjunto'] = $filename;
        }

        $correspondencia->update($validated);

        return redirect()->route('correspondences.index')->with('success', 'Correspondencia actualizada exitosamente.');
    }

    /**
     * Eliminar correspondencia.
     */
    public function webDestroy($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        if ($correspondencia->archivo_adjunto && \Storage::disk('public')->exists('correspondencia/' . $correspondencia->archivo_adjunto)) {
            \Storage::disk('public')->delete('correspondencia/' . $correspondencia->archivo_adjunto);
        }
        $correspondencia->delete();
        return redirect()->route('correspondences.index')->with('success', 'Correspondencia eliminada exitosamente.');
    }

    /**
     * Descargar archivo adjunto en PDF/imagen.
     */
    public function webDownload($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        if (!$correspondencia->archivo_adjunto) {
            return back()->with('error', 'Esta correspondencia no tiene archivo adjunto.');
        }
        $path = storage_path('app/public/correspondencia/' . $correspondencia->archivo_adjunto);
        if (!file_exists($path)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }
        return response()->download($path, $correspondencia->archivo_adjunto);
    }
}
