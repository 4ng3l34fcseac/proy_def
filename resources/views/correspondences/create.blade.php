@extends('layouts.app')

@section('title', 'Nueva Correspondencia / CITE')

@section('header-actions')
    <a href="{{ route('correspondences.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver</a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('correspondences.store.web') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf

            {{-- Titulo formal de la interfaz --}}
            <div class="mb-4 pb-3 border-b-2 border-blue-700">
                <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                    Interfaz de Registro de Nueva Correspondencia - CITES
                </h3>
                <p class="text-xs text-slate-500 mt-1">Complete la información del documento y adjunte el archivo digital de respaldo obligatorio para el control institucional.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                            N° de Documento / CITE
                            <span class="text-slate-400 font-normal">(se genera automáticamente)</span>
                        </label>
                        <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-100 text-slate-600 font-mono cursor-not-allowed" placeholder="Seleccione primero el tipo de documento">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo de Documento *</label>
                        <select id="tipo_documento" name="tipo_documento" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccione primero el tipo --</option>
                            <option value="nota_interna"  {{ old('tipo_documento') === 'nota_interna'  ? 'selected' : '' }}>Nota Interna</option>
                            <option value="nota_externa"  {{ old('tipo_documento') === 'nota_externa'  ? 'selected' : '' }}>Nota Externa</option>
                            <option value="informe"       {{ old('tipo_documento') === 'informe'       ? 'selected' : '' }}>Informe</option>
                            <option value="instructivo"   {{ old('tipo_documento') === 'instructivo'   ? 'selected' : '' }}>Instructivo</option>
                            <option value="memorandum"    {{ old('tipo_documento') === 'memorandum'    ? 'selected' : '' }}>Memorándum</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">El N° de CITE se regenera automáticamente al cambiar de tipo.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dirección *</label>
                        <select name="direccion" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ingreso" {{ old('direccion', 'ingreso') === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="egreso"  {{ old('direccion') === 'egreso'  ? 'selected' : '' }}>Egreso</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- =================== REMITENTE =================== --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remitente *</label>

                        {{-- Caja A: Nota Interna (select con usuarios) --}}
                        <div id="box-remitente-interna" class="hidden">
                            <select id="remitente-interna" name="remitente" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Seleccione un funcionario --</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ strtoupper($u->name) }}" {{ old('remitente') === strtoupper($u->name) ? 'selected' : '' }}>{{ strtoupper($u->name) }}{{ $u->cargo ? ' · ' . strtoupper($u->cargo) : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Caja B: Externo / Informe / Instructivo / Memorándum (fijo DEFENSORIA DEL PUEBLO) --}}
                        <div id="box-remitente-externo" class="hidden">
                            <input id="remitente-externo" type="text" name="remitente" value="DEFENSORIA DEL PUEBLO" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-100 text-slate-700 font-bold uppercase cursor-not-allowed">
                            <p class="text-xs text-slate-500 mt-1">Remitente institucional fijo.</p>
                        </div>
                    </div>

                    {{-- =================== DESTINATARIO =================== --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Destinatario *</label>

                        {{-- Caja A: Nota Interna (select con usuarios) --}}
                        <div id="box-destinatario-interna" class="hidden">
                            <select id="destinatario-interna" name="destinatario" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Seleccione un funcionario --</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ strtoupper($u->name) }}" {{ old('destinatario') === strtoupper($u->name) ? 'selected' : '' }}>{{ strtoupper($u->name) }}{{ $u->cargo ? ' · ' . strtoupper($u->cargo) : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Caja B: Externo / Informe / Instructivo / Memorándum (texto libre en MAYÚSCULAS) --}}
                        <div id="box-destinatario-externo" class="hidden">
                            <input id="destinatario-externo" type="text" name="destinatario" value="{{ old('destinatario') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nombre del destinatario" style="text-transform: uppercase;">
                            <p class="text-xs text-slate-500 mt-1">Se guardará automáticamente en MAYÚSCULAS.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Asunto *</label>
                    <textarea id="asunto" name="asunto" rows="3" required maxlength="500" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="DESCRIBA BREVEMENTE EL ASUNTO..." style="text-transform: uppercase;">{{ old('asunto') }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Se guardará automáticamente en MAYÚSCULAS.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Archivo Adjunto (PDF, JPG, PNG - máx. 5MB) *</label>
                    <input type="file" name="archivo_adjunto" accept=".pdf,.jpg,.jpeg,.png" required class="w-full text-sm border border-slate-300 rounded-lg p-2">
                    <p class="text-xs text-slate-500 mt-1">La subida del archivo adjunto es obligatoria.</p>
                </div>

                <div class="flex justify-end space-x-2 pt-3">
                    <a href="{{ route('correspondences.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Registrar CITE</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selTipo               = document.getElementById('tipo_documento');
            const inputNumero           = document.getElementById('numero_documento');
            const boxRemitenteInterna   = document.getElementById('box-remitente-interna');
            const boxRemitenteExterno   = document.getElementById('box-remitente-externo');
            const boxDestinatarioInterna= document.getElementById('box-destinatario-interna');
            const boxDestinatarioExterno= document.getElementById('box-destinatario-externo');
            const inputDestinatarioExt  = document.getElementById('destinatario-externo');
            const inputAsunto           = document.getElementById('asunto');

            const prefijos = {
                'nota_interna': 'NIN',
                'nota_externa': 'NEX',
                'informe':      'INF',
                'instructivo':  'INS',
                'memorandum':   'MEM',
            };

            // ============================================================
            // Mostrar / ocultar cajas según el tipo (sin encimar campos)
            // ============================================================
            function aplicarVisibilidadCajas() {
                // Ocultar todas las cajas y limpiar su name interno
                [boxRemitenteInterna, boxRemitenteExterno, boxDestinatarioInterna, boxDestinatarioExterno].forEach(function (box) {
                    if (!box) return;
                    box.classList.add('hidden');
                    // Quitar el name de los inputs/selects internos para que no se envíen duplicados
                    box.querySelectorAll('input, select, textarea').forEach(function (el) {
                        if (el.dataset.keepName !== '1') {
                            el.removeAttribute('name');
                        }
                    });
                });

                const tipo = selTipo.value;

                if (tipo === 'nota_interna') {
                    // Caja A en ambas columnas
                    boxRemitenteInterna.classList.remove('hidden');
                    boxDestinatarioInterna.classList.remove('hidden');
                    // Restaurar los names de los inputs internos
                    boxRemitenteInterna.querySelectorAll('select').forEach(function (el) { el.setAttribute('name', 'remitente'); });
                    boxDestinatarioInterna.querySelectorAll('select').forEach(function (el) { el.setAttribute('name', 'destinatario'); });
                } else if (tipo !== '') {
                    // Cualquier otro tipo (nota_externa, informe, instructivo, memorandum):
                    // Caja B (remitente fijo DEFENSORIA DEL PUEBLO + destinatario libre)
                    boxRemitenteExterno.classList.remove('hidden');
                    boxDestinatarioExterno.classList.remove('hidden');
                    // Restaurar los names
                    boxRemitenteExterno.querySelectorAll('input').forEach(function (el) { el.setAttribute('name', 'remitente'); });
                    boxDestinatarioExterno.querySelectorAll('input').forEach(function (el) { el.setAttribute('name', 'destinatario'); });
                }
            }

            // ============================================================
            // Regenerar el código del CITE en cada cambio de tipo
            // ============================================================
            async function regenerarCodigo() {
                const tipo = selTipo.value;
                if (!tipo || !prefijos[tipo]) {
                    inputNumero.value = '';
                    inputNumero.placeholder = 'Seleccione primero el tipo de documento';
                    return;
                }
                try {
                    const resp = await fetch('{{ route('correspondences.nextCode.web') }}?tipo=' + tipo, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await resp.json();
                    inputNumero.value = data.codigo || (prefijos[tipo] + '-0001');
                } catch (err) {
                    inputNumero.value = prefijos[tipo] + '-0001';
                }
            }

            // ============================================================
            // Forzar MAYÚSCULAS en el input de destinatario externo y asunto
            // ============================================================
            function forzarMayusculas(e) {
                const start = e.target.selectionStart;
                const end   = e.target.selectionEnd;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(start, end);
            }

            // Listeners
            selTipo.addEventListener('change', function () {
                aplicarVisibilidadCajas();
                regenerarCodigo();
            });

            if (inputDestinatarioExt) inputDestinatarioExt.addEventListener('input', forzarMayusculas);
            if (inputAsunto)           inputAsunto.addEventListener('input', forzarMayusculas);

            // Estado inicial: si el form se recargó con un tipo previamente seleccionado (old())
            if (selTipo.value !== '') {
                aplicarVisibilidadCajas();
                regenerarCodigo();
            }
        });
    </script>
@endpush
