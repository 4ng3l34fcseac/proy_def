<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('cargo', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'users' => $users,
        ], 200);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['administrador', 'funcionario'])],
            'cargo' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'active' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['active'] = $validated['active'] ?? true;

        $user = User::create($validated);

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ], 200);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::in(['administrador', 'funcionario'])],
            'cargo' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente.',
            'user' => $user,
        ], 200);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // No permitir eliminar el propio usuario
        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puede eliminar su propio usuario.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente.',
        ], 200);
    }

    /**
     * Asignar rol a un usuario (HU6).
     */
    public function assignRole(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['administrador', 'funcionario'])],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => 'Rol asignado exitosamente.',
            'user' => $user,
        ], 200);
    }

    /**
     * Activar o desactivar usuario (HU7).
     */
    public function toggleActive(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'message' => $user->active
                ? 'Usuario activado exitosamente.'
                : 'Usuario desactivado exitosamente.',
            'user' => $user,
        ], 200);
    }

    /**
     * Vista web del módulo Usuarios (solo Administrador).
     */
    public function webIndex()
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role !== 'Administrador') {
            abort(403, 'No tiene permisos para acceder al módulo de Usuarios.');
        }
        $users = User::orderBy('name', 'asc')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Mostrar formulario de creación de usuario.
     */
    public function webCreate()
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        return view('users.create');
    }

    /**
     * Guardar un nuevo usuario (POST tradicional).
     */
    public function webStore(Request $request)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/'],
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:Administrador,Funcionario',
            'cargo'    => 'required|string|max:255',
            'telefono' => ['required', 'string', 'min:7', 'max:15', 'regex:/^[0-9]+$/'],
            'status'   => 'nullable|string|in:Activo,Inactivo',
        ], [
            'name.required'     => 'El nombre completo es obligatorio.',
            'name.regex'        => 'El nombre completo solo debe contener letras y espacios.',
            'name.max'          => 'El nombre completo no debe superar los 255 caracteres.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique'   => 'Este nombre de usuario ya esta registrado.',
            'email.email'       => 'Ingrese un correo electronico valido.',
            'email.unique'      => 'Este correo electronico ya esta registrado.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min'      => 'La longitud minima de la contrasena es de 6 caracteres.',
            'password.confirmed'=> 'La contrasena de confirmacion no coincide.',
            'role.required'     => 'Seleccione un rol para el usuario.',
            'role.in'           => 'El rol seleccionado no es valido.',
            'cargo.required'    => 'Seleccione un cargo de la lista.',
            'telefono.required' => 'El telefono es obligatorio.',
            'telefono.regex'    => 'El telefono solo acepta numeros, sin espacios, ni extensiones.',
            'telefono.min'      => 'El telefono debe tener al menos 7 digitos.',
            'telefono.max'      => 'El telefono no debe superar los 15 digitos.',
        ]);
        // Forzar MAYUSCULAS en los campos de texto relevantes antes de persistir
        $validated['name']  = mb_strtoupper(trim($validated['name']));
        $validated['cargo'] = mb_strtoupper(trim($validated['cargo']));
        $validated['password'] = Hash::make($validated['password']);
        // Si el checkbox no viene marcado, el hidden 'Inactivo' toma el control; si viene marcado, llega 'Activo'.
        $validated['status']   = $request->input('status', 'Inactivo');
        User::create($validated);
        return redirect()->route('users.index')->with('success', 'Usuario registrado con exito.');
    }

    /**
     * Mostrar formulario de edición de usuario.
     */
    public function webEdit($id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Actualizar usuario (PUT tradicional).
     */
    public function webUpdate(Request $request, $id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/'],
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'    => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'role'     => 'required|in:Administrador,Funcionario',
            'cargo'    => 'required|string|max:255',
            'telefono' => ['required', 'string', 'min:7', 'max:15', 'regex:/^[0-9]+$/'],
            'password' => 'nullable|string|min:6|confirmed',
            'status'   => 'nullable|string|in:Activo,Inactivo',
        ], [
            'name.required'     => 'El nombre completo es obligatorio.',
            'name.regex'        => 'El nombre completo solo debe contener letras y espacios.',
            'name.max'          => 'El nombre completo no debe superar los 255 caracteres.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique'   => 'Este nombre de usuario ya esta registrado.',
            'email.email'       => 'Ingrese un correo electronico valido.',
            'email.unique'      => 'Este correo electronico ya esta registrado.',
            'password.min'      => 'La longitud minima de la contrasena es de 6 caracteres.',
            'password.confirmed'=> 'La contrasena de confirmacion no coincide.',
            'role.required'     => 'Seleccione un rol para el usuario.',
            'role.in'           => 'El rol seleccionado no es valido.',
            'cargo.required'    => 'Seleccione un cargo de la lista.',
            'telefono.required' => 'El telefono es obligatorio.',
            'telefono.regex'    => 'El telefono solo acepta numeros, sin espacios, ni extensiones.',
            'telefono.min'      => 'El telefono debe tener al menos 7 digitos.',
            'telefono.max'      => 'El telefono no debe superar los 15 digitos.',
        ]);
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        // Forzar MAYUSCULAS en los campos de texto relevantes antes de persistir
        $validated['name']  = mb_strtoupper(trim($validated['name']));
        $validated['cargo'] = mb_strtoupper(trim($validated['cargo']));
        // Si el checkbox no viene marcado, el hidden 'Inactivo' ya se recibió; si viene marcado, llega 'Activo'.
        $validated['status'] = $request->input('status', $user->status ?? 'Inactivo');
        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'Los cambios del usuario se guardaron correctamente.');
    }

    /**
     * Eliminar usuario (DELETE tradicional).
     */
    public function webDestroy($id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        if (auth()->id() == $id) {
            return redirect()->route('users.index')->with('error', 'No puede eliminar su propio usuario.');
        }
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Alternar estado Activo/Inactivo del usuario.
     * Sincronizado con la columna 'status' (string) de la base de datos.
     */
    public function webToggleActive($id)
    {
        if (auth()->user()->role !== 'Administrador') abort(403);
        $user = User::findOrFail($id);
        if ($user->status === 'Activo') {
            $user->status = 'Inactivo';
        } else {
            $user->status = 'Activo';
        }
        $user->save();
        return redirect()->route('users.index')->with('success', 'Estado del usuario actualizado.');
    }
}
