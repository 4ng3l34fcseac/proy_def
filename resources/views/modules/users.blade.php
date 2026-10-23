@extends('dashboard')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4">Gestión de Usuarios</h2>
    <p class="text-slate-600">Listado, creación, edición y desactivación de usuarios del sistema (solo Administrador).</p>
    <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-blue-700 hover:underline">← Volver al Panel</a>
</div>
@endsection
