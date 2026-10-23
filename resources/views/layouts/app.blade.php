<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Control') - Defensoría del Pueblo Oruro</title>

    {{-- Favicon oficial institucional --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.25);
        }
    </style>

    {{-- Permite a las vistas inyectar CSS adicional --}}
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- Contenedor principal con marca de agua institucional --}}
    <div class="relative min-h-screen bg-gray-100 overflow-x-hidden">

        {{-- Marca de agua institucional gigante (opacidad 5%) --}}
        <div class="absolute inset-0 bg-center bg-no-repeat opacity-5 pointer-events-none z-0"
             style="background-image: url('{{ asset('img/logo.jpg') }}'); background-size: 40rem;"></div>

        {{-- Contenido real flotando encima del fondo --}}
        <div class="relative z-10">

            {{-- =================== BANNER INSTITUCIONAL =================== --}}
            <header class="w-full">
                {{-- Franja naranja superior --}}
                <div class="bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 h-2 w-full"></div>

                {{-- Banner azul principal --}}
                <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-blue-900 text-white shadow-lg">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div class="flex items-center justify-between">

                            {{-- Logo + nombre institucional --}}
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-md overflow-hidden">
                                    <img src="{{ asset('img/logo.jpg') }}"
                                         class="w-10 h-10 aspect-square object-contain rounded-full"
                                         alt="Logo Defensoría">
                                </div>
                                <div>
                                    <h1 class="text-lg sm:text-xl font-bold tracking-tight">Defensoría del Pueblo</h1>
                                    <p class="text-xs sm:text-sm text-blue-200 font-medium">Delegación Departamental · Oruro - Bolivia</p>
                                </div>
                            </div>

                            {{-- Slot opcional para acciones personalizadas (botones a la derecha del banner) --}}
                            <div class="flex items-center space-x-2">
                                @yield('header-actions')
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sombra inferior sutil --}}
                <div class="h-1.5 bg-gradient-to-b from-blue-900/30 to-transparent"></div>
            </header>

            {{-- =================== CONTENIDO DINÁMICO =================== --}}
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @yield('content')
            </main>

            {{-- =================== PIE INSTITUCIONAL =================== --}}
            <footer class="text-center text-xs text-slate-500 py-6">
                <p>Sistema de Gestión Administrativa · Defensoría del Pueblo Oruro · {{ date('Y') }}</p>
            </footer>
        </div>
    </div>

    {{-- Stack para scripts JS específicos de cada vista --}}
    @stack('scripts')
</body>
</html>