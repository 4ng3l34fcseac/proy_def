<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle authentication attempt.
     * Acepta 'username' o 'email' como credencial de identidad.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El campo Usuario es obligatorio.',
            'password.required' => 'El campo Contraseña es obligatorio.',
        ]);

        $remember = (bool) $request->boolean('remember');

        // Permitir login por 'username' (campo agregado a users) o por 'email' como fallback
        $loginColumn = $this->resolveLoginColumn($credentials['username']);

        if (Auth::attempt([$loginColumn => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            if ($user->status !== 'Activo') {
                Auth::logout();
                return back()->withErrors(['username' => 'Su cuenta está desactivada temporalmente.'])->withInput();
            }

            $request->session()->regenerate();

            // Si la petición espera JSON (p. ej. fetch desde JS), responder JSON con token Sanctum
            if ($request->wantsJson() || $request->expectsJson()) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Inicio de sesión exitoso.',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ], 200);
            }

            // Petición web nativa -> redirigir al dashboard (usando nombre de ruta)
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas son incorrectas.',
        ])->withInput($request->only('username', 'remember'));
    }

    /**
     * Determina qué columna usar para buscar al usuario.
     * Si la columna 'username' existe en la tabla y el valor no parece un email,
     * se prioriza 'username'. Si parece email o 'username' no existe, se usa 'email'.
     */
    private function resolveLoginColumn(string $identifier): string
    {
        static $hasUsernameColumn = null;

        if ($hasUsernameColumn === null) {
            try {
                $hasUsernameColumn = \Schema::hasColumn('users', 'username');
            } catch (\Throwable $e) {
                $hasUsernameColumn = false;
            }
        }

        if ($hasUsernameColumn && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return 'username';
        }

        return 'email';
    }

    /**
     * Destroy session / logout.
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();

        return response()->json([
            'message' => 'Sesión cerrada de forma segura.',
        ], 200);
    }

    /**
     * Cierre de sesión nativo (formulario web con CSRF).
     * Usado por la modal de confirmación en el dashboard.
     */
    public function logoutWeb(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }

    /**
     * Get the authenticated user profile.
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Update the authenticated user profile.
     * Valida y guarda los datos editados desde la modal del Dashboard.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]+$/'],
            'email'     => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'telefono'  => 'nullable|string|max:20',
            'cargo'     => 'nullable|string|max:255',
            'foto'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
        ], [
            'name.regex'      => 'El nombre solo puede contener letras y espacios.',
            'email.email'     => 'Ingrese un correo electrónico válido.',
            'foto.image'      => 'El archivo subido debe ser una imagen.',
            'foto.max'        => 'La foto no debe pesar más de 2MB.',
            'new_password.min'      => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        // Actualizar campos básicos
        $user->name     = $validated['name'];
        $user->email    = $validated['email']    ?? $user->email;
        $user->telefono = $validated['telefono'] ?? $user->telefono;
        $user->cargo    = $validated['cargo']    ?? $user->cargo;

        // Guardar foto de perfil si se subió
        if ($request->hasFile('foto')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            // Guardar nueva imagen en storage/app/public/avatars/
            $path = $request->file('foto')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        // Cambio de contraseña opcional
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
            $user->save();
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Perfil actualizado exitosamente.',
                'user'    => $user,
                'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ], 200);
        }

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual no coincide con nuestros registros.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña cambiada exitosamente.',
        ], 200);
    }
}
