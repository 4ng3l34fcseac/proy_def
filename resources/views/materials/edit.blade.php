@extends('layouts.app')

@section('title', 'Editar Material')

@section('header-actions')
    <a href="{{ route('materials.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver</a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            {{-- Titulo formal de la interfaz --}}
            <div class="mb-4 pb-3 border-b-2 border-blue-700">
                <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                    Interfaz de Actualización de Material o Producto
                </h3>
                <p class="text-xs text-slate-500 mt-1">Modifique los campos necesarios para actualizar la información, el stock mínimo o las especificaciones del artículo en el almacén.</p>
            </div>

            <form action="{{ route('materials.update.web', $material->id) }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Código <span class="text-slate-400 font-normal">(generado automáticamente, no editable)</span></label>
                    <input type="text" value="{{ $material->codigo }}" readonly class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-100 text-slate-600 font-mono cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $material->nombre) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Categoría</label>
                    <select name="categoria_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Seleccione --</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ $cat->id == $material->categoria_id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock', $material->stock) }}" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Stock Mínimo</label>
                        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $material->stock_minimo) }}" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Unidades</label>
                        <input type="text" name="medida" value="{{ old('medida', $material->medida) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descripcion', $material->descripcion) }}</textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-3">
                    <a href="{{ route('materials.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
