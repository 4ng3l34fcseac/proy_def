@extends('layouts.app')

@section('title', 'Cites / Correspondencia')

@section('header-actions')
    <a href="{{ route('dashboard') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver al Panel</a>
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Módulo de Cites / Correspondencia</h2>
        <p class="text-slate-500 text-sm mt-1">Correspondencia interna y externa - CITES</p>
    </div>

    {{-- Mensajes flash --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filtros reactivos: sin boton Filtrar y sin recarga completa (Fetch asincrono) --}}
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-4 mb-4">
            <form id="filtro-cites-form" method="GET" action="{{ route('correspondences.index') }}" class="grid grid-cols-1 md:grid-cols-7 gap-3">
                <input type="text" id="search"    name="search"    value="{{ request('search') }}"    placeholder="Búsqueda general..." class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" id="remitente" name="remitente" value="{{ request('remitente') }}" placeholder="Remitente..." class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="tipo_documento" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tipo --</option>
                    <option value="nota_interna"  @if(request('tipo_documento') === 'nota_interna')  selected @endif>Nota Interna</option>
                    <option value="nota_externa"  @if(request('tipo_documento') === 'nota_externa')  selected @endif>Nota Externa</option>
                    <option value="informe"       @if(request('tipo_documento') === 'informe')       selected @endif>Informe</option>
                    <option value="instructivo"   @if(request('tipo_documento') === 'instructivo')   selected @endif>Instructivo</option>
                    <option value="memorandum"    @if(request('tipo_documento') === 'memorandum')    selected @endif>Memorándum</option>
                </select>
                <select name="direccion" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Dirección --</option>
                    <option value="ingreso" @if(request('direccion') === 'ingreso') selected @endif>Ingreso</option>
                    <option value="egreso"  @if(request('direccion') === 'egreso')  selected @endif>Egreso</option>
                </select>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" title="Fecha desde">
                <a href="{{ route('correspondences.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold text-center">+ Nuevo Cite</a>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Registro de Correspondencia</h2>
            <p class="text-slate-500 text-sm mb-4">Total registrados: {{ $correspondencias->count() }}</p>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b-2 border-slate-200">
                            <th class="py-3 pr-4 font-semibold">N° CITE</th>
                            <th class="py-3 pr-4 font-semibold">Tipo</th>
                            <th class="py-3 pr-4 font-semibold">Dir.</th>
                            <th class="py-3 pr-4 font-semibold">Fecha</th>
                            <th class="py-3 pr-4 font-semibold">Remitente</th>
                            <th class="py-3 pr-4 font-semibold">Destinatario</th>
                            <th class="py-3 pr-4 font-semibold">Asunto</th>
                            <th class="py-3 pr-4 font-semibold">Estado</th>
                            <th class="py-3 pr-4 font-semibold">Adjunto</th>
                            @if(strtoupper(Auth::user()->role) === 'ADMINISTRADOR' || Auth::user()->role === 'Administrador')
                                <th class="px-4 py-2 text-left font-bold text-xs uppercase">REGISTRADO POR</th>
                            @endif
                            <th class="py-3 pr-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cites-body">
                        @forelse ($correspondencias as $corresp)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 pr-4 font-mono text-slate-700">{{ $corresp->numero_documento }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ ucfirst(str_replace('_', ' ', $corresp->tipo_documento)) }}</td>
                                <td class="py-3 pr-4">
                                    @if ($corresp->direccion === 'ingreso')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Ingreso</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700">Egreso</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ $corresp->fecha ? $corresp->fecha->format('d/m/Y') : '-' }}</td>
                                <td class="py-3 pr-4 text-slate-700 font-medium uppercase">{{ $corresp->remitente }}</td>
                                <td class="py-3 pr-4 text-slate-600 uppercase">{{ $corresp->destinatario }}</td>
                                <td class="py-3 pr-4 text-slate-600 max-w-xs truncate uppercase" title="{{ $corresp->asunto }}">{{ $corresp->asunto }}</td>
                                <td class="py-3 pr-4">
                                    @php
                                        $colorEstado = match($corresp->estado_atencion) {
                                            'pendiente'  => 'bg-amber-100 text-amber-700',
                                            'en_proceso' => 'bg-blue-100 text-blue-700',
                                            'atendido'   => 'bg-emerald-100 text-emerald-700',
                                            default      => 'bg-slate-100 text-slate-700',
                                        };
                                        $estadoLabel = match($corresp->estado_atencion) {
                                            'pendiente'  => 'PENDIENTE',
                                            'en_proceso' => 'EN PROCESO',
                                            'atendido'   => 'ENVIADO',
                                            default      => strtoupper(str_replace('_', ' ', $corresp->estado_atencion ?? 'PENDIENTE')),
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorEstado }}">{{ $estadoLabel }}</span>
                                </td>
                                <td class="py-3 pr-4">
                                    @if ($corresp->archivo_adjunto)
                                        @php
                                            $ext = strtolower(pathinfo($corresp->archivo_adjunto, PATHINFO_EXTENSION));
                                            $esPdf   = ($ext === 'pdf');
                                            $esImg   = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                        @endphp
                                        @if ($esPdf)
                                            <a href="{{ route('correspondences.download.web', $corresp->id) }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded text-xs font-semibold" title="Descargar PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                Ver PDF
                                            </a>
                                        @elseif ($esImg)
                                            <a href="{{ route('correspondences.download.web', $corresp->id) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded text-xs font-semibold" title="Descargar imagen">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                Ver Imagen
                                            </a>
                                        @else
                                            <a href="{{ route('correspondences.download.web', $corresp->id) }}" class="inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1 rounded text-xs font-semibold" title="Descargar archivo">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                Ver Archivo
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">Sin adjunto</span>
                                    @endif
                                </td>
                                @if(strtoupper(Auth::user()->role) === 'ADMINISTRADOR' || Auth::user()->role === 'Administrador')
                                    <td class="px-4 py-2 text-xs uppercase font-semibold text-blue-900">{{ strtoupper($corresp->user->name ?? 'N/D') }}</td>
                                @endif
                                <td class="py-3 pr-4 text-right whitespace-nowrap">
                                    <a href="{{ route('correspondences.edit', $corresp->id) }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold mr-1">Editar</a>
                                    <form action="{{ route('correspondences.destroy.web', $corresp->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar el CITE {{ $corresp->numero_documento }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="py-8 text-center text-slate-400">No hay correspondencia registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
@endsection

@push('scripts')
    {{-- Motor de busqueda asincrona: actualiza solo el <tbody> sin recargar la pagina --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form      = document.getElementById('filtro-cites-form');
            const tableBody = document.getElementById('tabla-cites-body');
            if (!form || !tableBody) return;

            // Pequeño indicador de carga (opacidad reducida mientras se piden datos)
            const setLoading = function (on) {
                tableBody.style.opacity = on ? '0.5' : '1';
                tableBody.style.transition = 'opacity 120ms';
            };

            // Pide los datos al servidor y reemplaza SOLO el cuerpo de la tabla
            function buscarDatos() {
                const formData = new FormData(form);
                const params   = new URLSearchParams(formData).toString();
                const url      = (form.action || window.location.href) + '?' + params;

                setLoading(true);

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.text();
                })
                .then(function (html) {
                    // Parsear la respuesta como HTML y extraer el nuevo tbody
                    const parser = new DOMParser();
                    const doc    = parser.parseFromString(html, 'text/html');
                    const newBody = doc.getElementById('tabla-cites-body');

                    if (newBody) {
                        tableBody.innerHTML = newBody.innerHTML;
                        // Actualizar la URL del navegador sin recargar (para que sea compartible / con F5)
                        try { history.replaceState(null, '', url); } catch (e) {}
                    }
                })
                .catch(function (error) {
                    console.error('Error al filtrar correspondencia:', error);
                })
                .finally(function () { setLoading(false); });
            }

            // Debounce: evita peticiones en cada tecla, solo envia 250ms despues de pausar
            let debounceTimer = null;
            function buscarConDebounce() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(buscarDatos, 250);
            }

            // Listeners en los inputs de texto
            form.querySelectorAll('input[type="text"]').forEach(function (input) {
                input.addEventListener('input', buscarConDebounce);
                input.removeAttribute('oninput'); // limpieza por si quedo alguno inline
            });

            // Listeners en selects y datepicker
            form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
                el.addEventListener('change', buscarDatos);
                el.removeAttribute('onchange');
            });

            // Evitar el submit tradicional al presionar Enter: usamos la misma funcion
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                buscarDatos();
            });
        });
    </script>
@endpush
