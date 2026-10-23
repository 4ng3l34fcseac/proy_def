@extends('layouts.app')

@section('title', 'Panel de Control')

@section('header-actions')
    @php
        // Acceso normalizado a la información del usuario (acepta 'Administrador'/'administrador'/'Funcionario'/'funcionario')
        $currentUser  = Auth::user() ?? $user ?? null;
        $displayName  = $currentUser->name ?? 'Usuario';
        $displayRole  = $currentUser->role_display ?? 'Funcionario';
        $isAdmin      = (bool) ($currentUser->is_admin ?? false);
        $isFuncionario = !$isAdmin;
    @endphp

    <button type="button" onclick="abrirModalPerfil()" class="flex items-center space-x-2 bg-blue-950/40 hover:bg-blue-950/60 rounded-full pl-1 pr-4 py-1.5 transition">
        <div class="flex items-center space-x-2">
            @if($currentUser && $currentUser->avatar)
                <img src="{{ asset('storage/' . $currentUser->avatar) }}" alt="Foto de Perfil" class="w-8 h-8 rounded-full object-cover border border-white shadow-sm">
            @else
                <div class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                </div>
            @endif
        </div>
        <div class="flex flex-col text-left">
            <p class="text-sm font-semibold leading-tight text-white">{{ $displayName }}</p>
            <p class="text-xs text-blue-200 leading-tight">{{ $displayRole }}</p>
        </div>
    </button>
    <button type="button" onclick="abrirModalLogout()" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span class="hidden sm:inline">Cerrar Sesión</span>
    </button>
@endsection

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-800">Panel de Control</h2>
        <p class="text-slate-600 mt-1">
                Bienvenido, <span class="font-semibold">{{ $displayName }}</span>
                (<span class="font-semibold">{{ $displayRole }}</span>).
                Seleccione el módulo al que desea acceder.
            </p>
        </div>

        <div class="grid @if(strtoupper(Auth::user()->role) === 'FUNCIONARIO' || Auth::user()->role === 'Funcionario') grid-cols-1 md:grid-cols-2 gap-6 justify-items-center max-w-4xl @else grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl @endif mx-auto px-4">

            @if(strtoupper(Auth::user()->role) === 'ADMINISTRADOR' || Auth::user()->role === 'Administrador')
                {{-- =================== ALMACÉN (tarjeta limpia con enlace a la vista independiente) =================== --}}
                <a href="{{ route('almacen.index') }}" class="card-hover bg-white rounded-2xl shadow-md border border-slate-200 p-8 flex flex-col items-center text-center group">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 5l9 4 9-4M3 17l9 4 9-4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Almacén</h3>
                    <p class="text-sm text-slate-500">Gestión de productos y solicitudes de materiales</p>
                </a>

                {{-- USUARIOS (solo visible para Administrador) --}}
                <a href="{{ route('users.index') }}" class="card-hover bg-white rounded-2xl shadow-md border border-slate-200 p-8 flex flex-col items-center text-center group">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-emerald-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Usuarios</h3>
                    <p class="text-sm text-slate-500">Administrar funcionarios del sistema</p>
                </a>
            @endif

            {{-- CITES (visible para ambos roles) --}}
            <a href="{{ route('correspondences.index') }}" class="card-hover bg-white rounded-2xl shadow-md border border-slate-200 p-8 flex flex-col items-center text-center group">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-orange-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-extrabold text-slate-800 mb-2 tracking-wide">CITES</h3>
                <p class="text-sm text-slate-500">Correspondencia interna y externa - CITES</p>
            </a>

            @if(strtoupper(Auth::user()->role) === 'FUNCIONARIO' || Auth::user()->role === 'Funcionario')
                {{-- =================== GESTIÓN DE PEDIDOS (exclusivo para Funcionario) =================== --}}
                <a href="{{ route('orders.index') }}" class="card-hover bg-white rounded-2xl shadow-md border border-slate-200 p-8 flex flex-col items-center text-center group md:col-span-1">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Gestión de Pedidos</h3>
                    <p class="text-sm text-slate-500">Solicitud y seguimiento de materiales</p>
                </a>
            @endif
        </div>

    {{-- =================== MODAL CONFIRMAR CIERRE DE SESIÓN =================== --}}
    <div id="modal-logout" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <h3 class="text-lg font-bold text-white">Confirmar Cierre de Sesión</h3>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                        </svg>
                    </div>
                    <p class="text-slate-700">¿Está seguro que desea cerrar su sesión? Tendrá que ingresar sus credenciales nuevamente.</p>
                </div>
            </div>
            <div class="px-6 pb-6 flex justify-end space-x-3">
                <button type="button" onclick="cerrarModalLogout()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold transition">
                    Cancelar
                </button>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition shadow-md">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- =================== MODAL ADMINISTRAR PERFIL =================== --}}
    <div id="modal-profile" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">

            <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Administrar Perfil / Detalles del Usuario</h3>
                <button type="button" onclick="cerrarModalPerfil()" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-0">

                    {{-- COLUMNA IZQUIERDA: Información del Perfil --}}
                    <div class="p-6 border-r border-slate-200">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Información del Perfil</h4>

                        <div class="flex flex-col items-center mb-6">
                            <div id="avatar-preview" class="w-28 h-28 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg mb-3 overflow-hidden">
                                @if ($currentUser && $currentUser->avatar && \Storage::disk('public')->exists($currentUser->avatar))
                                    <img src="{{ asset('storage/' . $currentUser->avatar) }}" alt="Foto de perfil" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($displayName, 0, 1)) }}
                                @endif
                            </div>
                            <label for="foto" class="text-sm font-semibold text-blue-700 hover:text-blue-900 underline cursor-pointer">
                                Cambiar Foto
                            </label>
                            <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" onchange="previewFoto(event)">
                            <p class="text-xs text-slate-500 mt-1">JPG, PNG o WebP. Máx. 2MB.</p>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre Completo</label>
                                <input type="text" name="name" value="{{ $currentUser->name ?? '' }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Correo Electrónico</label>
                                <input type="email" name="email" value="{{ $currentUser->email ?? '' }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Teléfono</label>
                                <input type="text" name="telefono" value="{{ $currentUser->telefono ?? '' }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: Seguridad y Acciones --}}
                    <div class="p-6 bg-slate-50">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Seguridad y Acciones</h4>

                        {{-- Bloque de cambio de contraseña --}}
                        <div class="space-y-3 mb-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Contraseña actual</label>
                                <input type="password" name="current_password" autocomplete="current-password" placeholder="••••••••" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Nueva contraseña</label>
                                <input type="password" name="new_password" autocomplete="new-password" placeholder="Mínimo 6 caracteres" minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirme contraseña</label>
                                <input type="password" name="new_password_confirmation" autocomplete="new-password" placeholder="Repita la nueva contraseña" minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-slate-500">La nueva contraseña debe tener <strong>mínimo 6 caracteres</strong>. Solo se actualizará si completas los 3 campos.</p>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-lg p-4">
                            <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Permisos y Rol</h5>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-slate-600">Rol asignado:</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $isAdmin ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $displayRole }}
                                </span>
                            </div>
                            <ul class="text-xs text-slate-500 space-y-1">
                                <li>✓ {{ $isAdmin ? 'Acceso total al sistema' : 'Acceso a Pedidos y Cites' }}</li>
                                <li>{{ $isAdmin ? '✓' : '✗' }} Gestión de Usuarios</li>
                                <li>{{ $isAdmin ? '✓' : '✗' }} Gestión de Productos</li>
                                <li>✓ Cambio de contraseña</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-100 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModalPerfil()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endsection

@push('scripts')
    <script>
        const $ = id => document.getElementById(id);
        const abrirModalLogout  = () => $('modal-logout').classList.remove('hidden');
        const cerrarModalLogout = () => $('modal-logout').classList.add('hidden');
        const abrirModalPerfil  = () => $('modal-profile').classList.remove('hidden');
        const cerrarModalPerfil = () => $('modal-profile').classList.add('hidden');

        function previewFoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => $('avatar-preview').innerHTML =
                '<img src="' + e.target.result + '" alt="Foto de perfil" class="w-full h-full object-cover">';
            reader.readAsDataURL(file);
        }

        // Cierre con ESC y clic fuera del modal (delegado)
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { cerrarModalLogout(); cerrarModalPerfil(); }
        });
        $('modal-logout').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarModalLogout(); });
        $('modal-profile').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarModalPerfil(); });

        // Validacion de cambio de contrasena (cliente)
        const formProfile = $('modal-profile').querySelector('form');
        if (formProfile) {
            formProfile.addEventListener('submit', e => {
                const [actual, nuevo, confir] = ['current_password','new_password','new_password_confirmation']
                    .map(n => formProfile.querySelector('input[name="'+n+'"]'));
                const lleno = a => a && a.value.trim() !== '';
                if (lleno(actual) || lleno(nuevo) || lleno(confir)) {
                    if (!lleno(actual))      { e.preventDefault(); alert('Debe ingresar su contrasena actual.');          actual?.focus(); return; }
                    if (!lleno(nuevo))       { e.preventDefault(); alert('Debe completar la nueva contrasena.');        return; }
                    if (!lleno(confir))      { e.preventDefault(); alert('Debe confirmar la nueva contrasena.');       return; }
                    if (nuevo.value.length < 6) { e.preventDefault(); alert('La nueva contrasena debe tener al menos 6 caracteres.'); nuevo.focus(); return; }
                    if (nuevo.value !== confir.value) { e.preventDefault(); alert('La nueva contrasena y su confirmacion no coinciden.'); confir.focus(); return; }
                }
            });
        }
    </script>
@endpush
