@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('header-actions')
    <a href="{{ route('dashboard') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver al Panel</a>
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Módulo de Usuarios</h2>
        <p class="text-slate-500 text-sm mt-1">Listado y administración de funcionarios</p>
    </div>

    {{-- Mensajes flash --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Listado de Usuarios del Sistema</h2>
                    <p class="text-slate-500 text-sm mt-1">Total registrados: <span id="total-registrados">{{ $users->count() }}</span> · Visibles: <span id="total-visibles">{{ $users->count() }}</span></p>
                </div>
                <a href="{{ route('users.create') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">+ Crear Nuevo</a>
            </div>

            <div class="mb-5 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <label class="block text-xs font-semibold text-slate-600 mb-1">🔍 Buscar personal por nombre</label>
                <input id="filtro-personal" type="text" placeholder="Escriba el nombre del funcionario para filtrar..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            </div>

            <div id="filas-vacias" class="hidden mb-4 p-4 bg-slate-50 border border-dashed border-slate-300 rounded-lg text-center text-sm text-slate-500">
                No hay funcionarios que coincidan con la búsqueda.
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b-2 border-slate-200">
                            <th class="py-3 pr-4 font-semibold">ID</th>
                            <th class="py-3 pr-4 font-semibold">Nombre</th>
                            <th class="py-3 pr-4 font-semibold">Usuario</th>
                            <th class="py-3 pr-4 font-semibold">Correo</th>
                            <th class="py-3 pr-4 font-semibold">Cargo</th>
                            <th class="py-3 pr-4 font-semibold">Teléfono</th>
                            <th class="py-3 pr-4 font-semibold">Rol</th>
                            <th class="py-3 pr-4 font-semibold">Estado</th>
                            <th class="py-3 pr-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-slate-100 hover:bg-slate-50" data-search="{{ strtolower($user->name) }}">
                                <td class="py-3 pr-4 text-slate-500">{{ $user->id }}</td>
                                <td class="py-3 pr-4 text-slate-800 font-medium">{{ $user->name }}</td>
                                <td class="py-3 pr-4 text-slate-600 font-mono">{{ $user->username ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $user->email }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $user->cargo ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $user->telefono ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    @if ($user->role === 'Administrador')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Administrador</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Funcionario</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if($user->status === 'Activo')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-200 text-slate-700">Inactivo</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-right whitespace-nowrap">
                                    <a href="{{ route('users.edit', $user->id) }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold mr-1">Editar</a>
                                    <form action="{{ route('users.toggle.web', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @if($user->status === 'Activo')
                                            <button type="submit" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-xs font-semibold mr-1" title="Desactivar usuario">Desactivar</button>
                                        @else
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold mr-1" title="Activar usuario">Activar</button>
                                        @endif
                                    </form>
                                    @if (auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy.web', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar al usuario {{ $user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="py-8 text-center text-slate-400">No hay usuarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        // ============================================================
        // Buscador en tiempo real para filtrar personal por nombre
        // ============================================================
        const filtroPersonal   = document.getElementById('filtro-personal');
        const totalVisibles    = document.getElementById('total-visibles');
        const mensajeVacio     = document.getElementById('filas-vacias');
        const filasUsuarios    = document.querySelectorAll('tbody tr[data-search]');

        filtroPersonal.addEventListener('input', function () {
            const texto = (this.value || '').toLowerCase().trim();
            let visibles = 0;

            filasUsuarios.forEach(function (fila) {
                const nombre = fila.getAttribute('data-search') || '';
                if (!texto || nombre.indexOf(texto) !== -1) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            if (totalVisibles) totalVisibles.textContent = visibles;
            if (mensajeVacio)  mensajeVacio.classList.toggle('hidden', visibles !== 0);
        });
    </script>
@endpush
