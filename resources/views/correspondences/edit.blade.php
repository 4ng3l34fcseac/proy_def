@extends('layouts.app')

@section('title', 'Editar CITE ' . $correspondencia->numero_documento)

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

        <form action="{{ route('correspondences.update.web', $correspondencia->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @method('PUT')

            {{-- Titulo formal de la interfaz --}}
            <div class="mb-4 pb-3 border-b-2 border-blue-700">
                <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                    Interfaz de Actualización de Control Documental - CITES
                </h3>
                <p class="text-xs text-slate-500 mt-1">Corrija los datos de la correspondencia recibida o enviada y asegúrese de que el documento digital adjunto esté actualizado.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">
                            N° de Documento / CITE
                            <span class="text-slate-400 font-normal">(no editable)</span>
                        </label>
                        <input type="text" value="{{ $correspondencia->numero_documento }}" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-100 text-slate-600 font-mono cursor-not-allowed">
                        <input type="hidden" name="numero_documento" value="{{ $correspondencia->numero_documento }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Fecha *</label>
                        <input type="date" name="fecha" value="{{ old('fecha', $correspondencia->fecha ? $correspondencia->fecha->format('Y-m-d') : '') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo de Documento *</label>
                        <select name="tipo_documento" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @php
                                $tipos = ['nota_interna'=>'Nota Interna','nota_externa'=>'Nota Externa','informe'=>'Informe','instructivo'=>'Instructivo','memorandum'=>'Memorándum'];
                            @endphp
                            @foreach ($tipos as $val => $label)
                                <option value="{{ $val }}" {{ $correspondencia->tipo_documento === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dirección *</label>
                        <select name="direccion" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ingreso" {{ $correspondencia->direccion === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="egreso"  {{ $correspondencia->direccion === 'egreso'  ? 'selected' : '' }}>Egreso</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remitente *</label>
                        <input type="text" name="remitente" value="{{ old('remitente', $correspondencia->remitente) }}" required oninput="this.value = this.value.toUpperCase()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Destinatario *</label>
                        <input type="text" name="destinatario" value="{{ old('destinatario', $correspondencia->destinatario) }}" required oninput="this.value = this.value.toUpperCase()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Asunto *</label>
                    <textarea name="asunto" rows="3" required maxlength="500" oninput="this.value = this.value.toUpperCase()" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500" style="text-transform: uppercase;">{{ old('asunto', $correspondencia->asunto) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Estado de Atención</label>
                    <select name="estado_atencion" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pendiente"  {{ $correspondencia->estado_atencion === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_proceso" {{ $correspondencia->estado_atencion === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="atendido"   {{ $correspondencia->estado_atencion === 'atendido'   ? 'selected' : '' }}>Enviado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reemplazar Archivo Adjunto *</label>
                    @if ($correspondencia->archivo_adjunto)
                        <p class="text-xs text-slate-500 mb-1">Actual: <a href="{{ route('correspondences.download.web', $correspondencia->id) }}" class="text-blue-700 hover:underline">{{ $correspondencia->archivo_adjunto }}</a></p>
                    @endif
                    <input type="file" name="archivo_adjunto" accept=".pdf,.jpg,.jpeg,.png" required class="w-full text-sm border border-slate-300 rounded-lg p-2">
                    <p class="text-xs text-slate-500 mt-1">La subida del archivo adjunto es obligatoria en cada edición.</p>
                </div>

                <div class="flex justify-end space-x-2 pt-3">
                    <a href="{{ route('correspondences.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Actualizar CITE</button>
                </div>
            </form>
        </div>
    </div>
@endsection
