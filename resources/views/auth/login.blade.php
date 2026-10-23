<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - Defensoría del Pueblo Oruro</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-institutional {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    </style>
</head>
<body class="bg-institutional min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo / Encabezado institucional --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full mb-4 shadow-lg shadow-orange-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white text-shadow">Defensoría del Pueblo</h1>
            <p class="text-orange-400 font-semibold tracking-wider text-sm mt-1">ORURO · BOLIVIA</p>
        </div>

        {{-- Tarjeta de Login --}}
        <div class="bg-slate-800/80 backdrop-blur-sm border border-slate-700 rounded-2xl shadow-2xl p-8">

            <h2 class="text-xl font-semibold text-white text-center mb-1">Iniciar Sesión</h2>
            <p class="text-slate-400 text-sm text-center mb-6">Accede al sistema de gestión</p>

            {{-- Mensaje de error (mostrado si la autenticación falla) --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/50 text-red-300 text-sm rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Campo Usuario (acepta texto corto, ej: 'admin', 'usu') --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-300 mb-2">
                        <span class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Usuario
                        </span>
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        placeholder="admin"
                        value="{{ old('username') }}"
                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    >
                </div>

                {{-- Campo Contraseña con botón del ojo --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                        <span class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Contraseña
                        </span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 pr-12 bg-slate-900/50 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-orange-400 focus:outline-none transition"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            {{-- Icono ojo abierto (por defecto) --}}
                            <svg id="icon-eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Icono ojo cerrado (oculto por defecto) --}}
                            <svg id="icon-eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Recordar --}}
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-300 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-500 bg-slate-900 text-orange-500 focus:ring-orange-500 focus:ring-offset-slate-800">
                        <span class="ml-2">Recordarme</span>
                    </label>
                    <a href="#" class="text-orange-400 hover:text-orange-300 transition">¿Olvidó su contraseña?</a>
                </div>

                {{-- Botón --}}
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 px-4 rounded-lg shadow-lg shadow-orange-500/30 transition transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-800"
                >
                    Ingresar al Sistema
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-slate-500 text-xs mt-6">
            © {{ date('Y') }} Defensoría del Pueblo · Delegación Departamental Oruro
        </p>
    </div>

    <script>
        // Mostrar / ocultar contraseña con cambio de icono
        (function () {
            const toggleBtn   = document.getElementById('toggle-password');
            const passwordInp = document.getElementById('password');
            const iconOpen    = document.getElementById('icon-eye-open');
            const iconClosed  = document.getElementById('icon-eye-closed');

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInp.type === 'password';
                passwordInp.type = isHidden ? 'text' : 'password';
                iconOpen.classList.toggle('hidden', isHidden);
                iconClosed.classList.toggle('hidden', !isHidden);
            });
        })();
    </script>
</body>
</html>
