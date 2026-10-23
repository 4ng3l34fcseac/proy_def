@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('header-actions')
    @if(strtoupper(Auth::user()->role) === 'FUNCIONARIO' || Auth::user()->role === 'Funcionario')
        <a href="{{ route('dashboard') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver al Panel</a>
    @else
        <a href="{{ route('almacen.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver al Almacén</a>
    @endif
@endsection

@section('content')
    {{-- Encabezado específico del módulo --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Módulo de Pedidos</h2>
        <p class="text-slate-500 text-sm mt-1">{{ $isAdmin ? 'Bandeja de pedidos del personal' : 'Mis pedidos del personal' }}</p>
    </div>

    {{-- Titulo formal de la interfaz (visible para Administrador) --}}
    @if ($isAdmin)
        <div class="mb-4 pb-3 border-b-2 border-blue-700">
            <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                Interfaz de Revisión y Dictamen de Solicitudes
            </h3>
            <p class="text-xs text-slate-500 mt-1">Revise la justificación del personal, valide las existencias actuales en el almacén y proceda a autorizar o rechazar la solicitud.</p>
        </div>
    @endif

        @if (session('success'))
            <div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-4 mb-4">
            <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código o justificación..." class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="estado" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente"  @if(request('estado') === 'Pendiente')  selected @endif>Pendiente</option>
                    <option value="Autorizado" @if(request('estado') === 'Autorizado') selected @endif>Autorizado</option>
                    <option value="Rechazado"  @if(request('estado') === 'Rechazado')  selected @endif>Rechazado</option>
                </select>
                <a href="{{ route('orders.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold text-center">+ Nuevo Pedido</a>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ $isAdmin ? 'Todos los Pedidos' : 'Mis Pedidos' }}</h2>
                    <p class="text-slate-500 text-sm mt-1">Total: {{ $pedidos->count() }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b-2 border-slate-200">
                            <th class="py-3 pr-4 font-semibold">Código</th>
                            <th class="py-3 pr-4 font-semibold">Solicitante</th>
                            <th class="py-3 pr-4 font-semibold">Justificación</th>
                            <th class="py-3 pr-4 font-semibold">Items</th>
                            <th class="py-3 pr-4 font-semibold">Estado</th>
                            <th class="py-3 pr-4 font-semibold">Fecha</th>
                            <th class="py-3 pr-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pedidos as $pedido)
                            @php
                                $colorEstado = 'bg-slate-100 text-slate-700';
                                if ($pedido->estado === 'Pendiente')  $colorEstado = 'bg-yellow-100 text-yellow-800';
                                if ($pedido->estado === 'Autorizado') $colorEstado = 'bg-green-100 text-green-800';
                                if ($pedido->estado === 'Rechazado')  $colorEstado = 'bg-red-100 text-red-800';
                                if ($pedido->estado === 'Despachado') $colorEstado = 'bg-blue-100 text-blue-800';
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 pr-4 font-mono text-slate-700">{{ $pedido->codigo }}</td>
                                <td class="py-3 pr-4 text-slate-800 font-medium">{{ $pedido->user->name ?? 'N/D' }}</td>
                                <td class="py-3 pr-4 text-slate-600 max-w-xs truncate" title="{{ $pedido->justificacion }}">{{ $pedido->justificacion }}</td>
                                <td class="py-3 pr-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-700">{{ $pedido->materiales->count() }}</span>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorEstado }}">{{ ucfirst($pedido->estado) }}</span>
                                    @if ($pedido->observaciones)
                                        <p class="text-xs text-red-600 italic mt-1" title="{{ $pedido->observaciones }}">{{ Str::limit($pedido->observaciones, 50) }}</p>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-slate-500">{{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="py-3 pr-4 text-right whitespace-nowrap">
                                    {{-- Boton Ver Detalle: visible para TODOS los roles --}}
                                    <button type="button" onclick="abrirModalDetalle({{ $pedido->id }}, '{{ $pedido->codigo }}')" class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded text-xs font-semibold mr-1" title="Ver Detalle del Pedido">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ver Detalle
                                    </button>

                                    {{-- Aprobar / Rechazar: SOLO Administrador --}}
                                    @if($pedido->estado === 'Pendiente')
                                        @if(Auth::user()->role === 'Administrador')
                                            <form action="{{ route('pedidos.aprobar', $pedido->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-bold mr-1">Aprobar</button>
                                            </form>
                                            <button type="button" onclick="abrirModalRechazo({{ $pedido->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-bold">Rechazar</button>
                                        @else
                                            {{-- Lo que ve el Funcionario cuando su pedido aun esta en revision --}}
                                            <span class="text-yellow-600 text-xs italic font-medium">En espera de revision</span>
                                        @endif
                                    @endif
                                    <div class="relative inline-block text-left dropdown-export">
                                        <button type="button" onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $pedido->id }}').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-bold">
                                            EXPORTAR
                                        </button>
                                        <div id="dropdown-{{ $pedido->id }}" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10">
                                            <a href="{{ route('pedidos.exportar.pdf', $pedido->id) }}" target="_blank" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">EXPORTAR A PDF</a>
                                            <a href="{{ route('pedidos.exportar.word', $pedido->id) }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">EXPORTAR A WORD</a>
                                            <a href="{{ route('inventario.exportar.excel') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">EXPORTAR A EXCEL</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">NO HAY PEDIDOS REGISTRADOS</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- =================== MODAL DETALLE DEL PEDIDO =================== --}}
    <div id="modal-detalle" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Detalle del Pedido <span id="detalle-codigo" class="text-orange-300"></span></h3>
                <button type="button" onclick="cerrarModalDetalle()" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Solicitante</p>
                        <p id="detalle-solicitante" class="text-sm font-bold text-slate-800 mt-1">—</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Estado</p>
                        <p id="detalle-estado" class="text-sm font-bold mt-1">—</p>
                    </div>
                </div>

                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Materiales Solicitados</h4>
                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 bg-slate-50 border-b-2 border-slate-200">
                                <th class="py-3 px-4 font-semibold">Código</th>
                                <th class="py-3 px-4 font-semibold">Material</th>
                                <th class="py-3 px-4 font-semibold">Cantidad Solicitada</th>
                            </tr>
                        </thead>
                        <tbody id="detalle-materiales"></tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Justificación del Funcionario</p>
                    <div id="detalle-justificacion-wrap" class="max-h-32 overflow-y-auto block p-2 bg-gray-50 rounded border border-slate-200">
                        <p id="detalle-justificacion" class="text-sm text-slate-700 whitespace-pre-wrap break-words">—</p>
                    </div>
                </div>

                <div id="detalle-observaciones-wrap" class="hidden mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-xs font-semibold text-red-700 uppercase">Observaciones / Motivo de Rechazo</p>
                    <p id="detalle-observaciones" class="text-sm text-red-800 mt-1 italic"></p>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" onclick="cerrarModalDetalle()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== MODAL RECHAZO DE PEDIDO =================== --}}
    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full relative mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-2">REGISTRAR MOTIVO DE RECHAZO</h3>
            <p class="text-sm text-gray-600 mb-4">Escriba brevemente la observación o motivo técnico por el cual se deniega esta solicitud de materiales.</p>
            
            <form id="form-rechazo" method="POST" action="">
                @csrf
                <textarea name="motivo_rechazo" required rows="3" class="w-full p-2 border rounded mb-4 text-sm uppercase" placeholder="EJ: STOCK INSUFICIENTE EN ALMACÉN CENTRAL / SOLICITUD FUERA DE FECHA LÍMITE"></textarea>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModalRechazo()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-bold">CANCELAR</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-bold">CONFIRMAR RECHAZO</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @php
            // Proyectamos los pedidos a una estructura simple para inyectar en JS,
            // extrayendo nombre del usuario y la cantidad desde el pivot.
            $pedidosDataJs = $pedidos->keyBy('id')->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'codigo'         => $p->codigo,
                    'estado'         => $p->estado,
                    'justificacion'  => $p->justificacion,
                    'observaciones'  => $p->observaciones,
                    'solicitante'    => optional($p->user)->name ?? 'N/D',
                    'materiales'    => $p->materiales->map(function ($m) {
                        return [
                            'codigo'   => $m->codigo ?? ('MAT-' . str_pad($m->id, 4, '0', STR_PAD_LEFT)),
                            'nombre'   => $m->nombre,
                            'cantidad' => (int) ($m->pivot->cantidad_solicitada ?? 0),
                        ];
                    })->values(),
                ];
            });
        @endphp
        const pedidosData = @json($pedidosDataJs);

        function colorBadge(estado) {
            if (estado === 'Pendiente')  return 'bg-yellow-100 text-yellow-800';
            if (estado === 'Autorizado') return 'bg-green-100 text-green-800';
            if (estado === 'Rechazado')  return 'bg-red-100 text-red-800';
            if (estado === 'Despachado') return 'bg-blue-100 text-blue-800';
            return 'bg-gray-100 text-gray-800';
        }

        function abrirModalDetalle(pedidoId, codigo) {
            const data = pedidosData[pedidoId];
            if (!data) return;

            document.getElementById('detalle-codigo').textContent      = '(' + data.codigo + ')';
            document.getElementById('detalle-solicitante').textContent = data.solicitante;

            const estadoEl = document.getElementById('detalle-estado');
            estadoEl.textContent = data.estado;
            estadoEl.className   = 'inline-block px-2 py-1 text-xs font-semibold rounded-full ' + colorBadge(data.estado);

            const tbody = document.getElementById('detalle-materiales');
            tbody.innerHTML = '';
            if (data.materiales.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-slate-400">Este pedido no tiene materiales asociados.</td></tr>';
            } else {
                data.materiales.forEach(function (m) {
                    tbody.innerHTML +=
                        '<tr class="border-b border-slate-100">' +
                            '<td class="py-3 px-4 font-mono text-slate-700">' + m.codigo + '</td>' +
                            '<td class="py-3 px-4 text-slate-800 font-medium">' + m.nombre + '</td>' +
                            '<td class="py-3 px-4 text-slate-700"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">' + m.cantidad + '</span></td>' +
                        '</tr>';
                });
            }

            const wrap = document.getElementById('detalle-observaciones-wrap');
            if (data.observaciones && data.observaciones.trim() !== '') {
                document.getElementById('detalle-observaciones').textContent = data.observaciones;
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }

            // Inyectar justificacion del funcionario en el contenedor con scroll
            const justEl = document.getElementById('detalle-justificacion');
            if (data.justificacion && data.justificacion.trim() !== '') {
                justEl.textContent = data.justificacion;
            } else {
                justEl.textContent = 'El funcionario no ingresó una justificación para este pedido.';
            }

            document.getElementById('modal-detalle').classList.remove('hidden');
        }

        function cerrarModalDetalle() {
            document.getElementById('modal-detalle').classList.add('hidden');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarModalDetalle();
        });
        document.getElementById('modal-detalle').addEventListener('click', function (e) {
            if (e.target === this) cerrarModalDetalle();
        });

        window.addEventListener('click', function(e){
            document.querySelectorAll('.dropdown-export').forEach(function(container){
                if(!container.contains(e.target)){
                    container.querySelector('[id^="dropdown-"]').classList.add('hidden');
                }
            });
        });

        function abrirModalRechazo(id) {
            const modal = document.getElementById('modal-rechazo');
            const form = document.getElementById('form-rechazo');
            
            // Construimos la ruta dinámica respetando el entorno de Laragon
            form.action = `{{ url('/pedidos') }}/${id}/rechazar`;
            
            modal.classList.remove('hidden');
        }
        function cerrarModalRechazo() {
            document.getElementById('modal-rechazo').classList.add('hidden');
        }
    </script>
@endpush
