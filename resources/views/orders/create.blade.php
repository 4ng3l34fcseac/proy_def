@extends('layouts.app')

@section('title', 'Nuevo Pedido de Materiales')

@section('header-actions')
    <a href="{{ route('orders.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver</a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                <ul class="list-disc pl-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('orders.store.web') }}" method="POST" id="form-pedido" class="space-y-4">
            @csrf

            {{-- Titulo formal de la interfaz --}}
            <div class="mb-4 pb-3 border-b-2 border-blue-700">
                <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                    Interfaz de Solicitud de Materiales del Personal
                </h3>
                <p class="text-xs text-slate-500 mt-1">Seleccione los materiales del catálogo y especifique las cantidades requeridas para su orden de oficina.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Justificación del Pedido *</label>
                <textarea name="justificacion" rows="3" required minlength="5" maxlength="1000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Explique el motivo o necesidad del pedido...">{{ old('justificacion') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-600">Materiales Solicitados *</label>
                    <button type="button" onclick="agregarMaterial()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-semibold">+ Agregar Material</button>
                </div>

                <div id="materiales-container" class="space-y-2">
                    <div class="grid grid-cols-12 gap-2 material-row">
                        <select name="materiales[0][material_id]" required onchange="onSelectMaterial(this)" class="col-span-7 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccione un material --</option>
                            @foreach ($materiales as $mat)
                                <option value="{{ $mat->id }}" data-stock="{{ $mat->stock }}" data-medida="{{ strtoupper($mat->medida ?? 'UNIDADES') }}">{{ $mat->codigo }} - {{ $mat->nombre }} (Stock: {{ $mat->stock }} {{ strtoupper($mat->medida ?? 'UNIDADES') }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="materiales[0][cantidad_solicitada]" min="1" required placeholder="CANTIDAD EN UNIDADES" class="col-span-4 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="eliminarFila(this)" class="col-span-1 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">×</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-3">
                @if(strtoupper(Auth::user()->role) === 'FUNCIONARIO' || Auth::user()->role === 'Funcionario')
                    <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">← Volver</a>
                @else
                    <a href="{{ route('almacen.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">← Volver</a>
                @endif
                <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Enviar Solicitud</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let contador = 1;
        const opcionesMateriales = `@foreach ($materiales as $mat)<option value="{{ $mat->id }}" data-stock="{{ $mat->stock }}" data-medida="{{ strtoupper($mat->medida ?? 'UNIDADES') }}">{{ $mat->codigo }} - {{ $mat->nombre }} (Stock: {{ $mat->stock }} {{ strtoupper($mat->medida ?? 'UNIDADES') }})</option>@endforeach`;

        function agregarMaterial() {
            const container = document.getElementById('materiales-container');
            const nuevaFila = document.createElement('div');
            nuevaFila.className = 'grid grid-cols-12 gap-2 material-row';
            nuevaFila.innerHTML = `
                <select name="materiales[${contador}][material_id]" required onchange="onSelectMaterial(this)" class="col-span-7 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Seleccione un material --</option>${opcionesMateriales}
                </select>
                <input type="number" name="materiales[${contador}][cantidad_solicitada]" min="1" required placeholder="CANTIDAD EN UNIDADES" class="col-span-4 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="eliminarFila(this)" class="col-span-1 bg-red-100 text-red-700 rounded-lg text-sm font-semibold">×</button>
            `;
            container.appendChild(nuevaFila);
            contador++;
        }

        function onSelectMaterial(select) {
            const materialId = select.value;
            const filaActual = select.closest('.material-row');
            const inputNuevo = filaActual.querySelector('input[type="number"]');
            const medida = select.options[select.selectedIndex]?.dataset?.medida || 'UNIDADES';

            if (!materialId) {
                inputNuevo.placeholder = 'CANTIDAD EN UNIDADES';
                return;
            }

            inputNuevo.placeholder = 'CANTIDAD EN ' + medida;

            const selects = document.querySelectorAll('select[name$="[material_id]"]');
            selects.forEach(function(s) {
                if (s !== select && s.value === materialId) {
                    const filaExistente = s.closest('.material-row');
                    const inputExistente = filaExistente.querySelector('input[type="number"]');
                    const valorNuevo = parseInt(inputNuevo.value) || 0;
                    if (valorNuevo > 0 && inputExistente) {
                        inputExistente.value = (parseInt(inputExistente.value) || 0) + valorNuevo;
                    }
                    select.value = '';
                    inputNuevo.value = '';
                    inputNuevo.placeholder = 'CANTIDAD EN UNIDADES';
                    alert('Este material ya fue agregado. La cantidad se sumó al ítem existente en el carrito.');
                }
            });
        }

        function eliminarFila(btn) {
            const filas = document.querySelectorAll('.material-row');
            if (filas.length > 1) {
                btn.parentElement.remove();
            } else {
                alert('Debe existir al menos un material en el pedido.');
            }
        }
    </script>
@endpush
