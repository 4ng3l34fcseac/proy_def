@php
    $cargosDefensoria = [
        'DELEGADO(A) DEFENSORIAL DEPARTAMENTAL II DE ORURO',
        'PROFESIONAL III DE SERVICIO AL PUEBLO',
        'PROFESIONAL II DE SERVICIO AL PUEBLO',
        'ADMINISTRATIVO OFICINA DEPARTAMENTAL',
        'PROFESIONAL III DE PROMOCION Y COMUNICACIÓN',
        'CONSULTORIA INDIVIDUAL DE LINEA CHOFER',
        'PERSONAL DE LIMPIEZA',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('header-actions')
    <a href="{{ route('users.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver</a>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                    <p class="font-semibold mb-1">Por favor corrija los siguientes errores:</p>
                    <ul class="list-disc pl-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('users.update.web', $user->id) }}" method="POST" class="space-y-3" novalidate>
                @csrf
                @method('PUT')

                {{-- Titulo formal de la interfaz --}}
                <div class="mb-4 pb-3 border-b-2 border-blue-700">
                    <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                        Interfaz de Actualización de Datos del Usuario
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Modifique los campos necesarios. La contraseña solo se actualizará si la ingresa.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre Completo *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+"
                               title="Solo se permiten letras y espacios"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de Usuario *</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Correo Electrónico (opcional)</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="ejemplo@defensoria.gob.bo">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Cargo *</label>
                        <select name="cargo" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccione un cargo --</option>
                            @foreach ($cargosDefensoria as $cargo)
                                <option value="{{ $cargo }}" {{ old('cargo', $user->cargo) === $cargo ? 'selected' : '' }}>{{ $cargo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Teléfono *</label>
                        <input type="tel" name="telefono" value="{{ old('telefono', $user->telefono) }}" required
                               pattern="[0-9]{7,15}"
                               title="Solo se permiten números, sin extensiones, mínimo 7 dígitos"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ej: 72456789">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Rol *</label>
                        <select name="role" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccione un rol --</option>
                            <option value="Funcionario"  {{ old('role', $user->role) === 'Funcionario'  ? 'selected' : '' }}>Funcionario</option>
                            <option value="Administrador" {{ old('role', $user->role) === 'Administrador' ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center space-x-2">
                            <input type="hidden" name="status" value="Inactivo">
                            <input type="checkbox" name="status" value="Activo" {{ $user->status === 'Activo' ? 'checked' : '' }} class="w-4 h-4">
                            <span class="text-sm text-slate-700">Usuario Activo</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nueva Contraseña (opcional)</label>
                        <input type="password" name="password" minlength="6"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Dejar en blanco para mantener">
                        <p class="text-xs text-slate-500 mt-1">La longitud mínima de la contraseña es de 6 caracteres.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" minlength="6"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-2 pt-3">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
