@extends('dashboard')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4">Módulo de Productos</h2>
    <p class="text-slate-600">Aquí va la gestión completa de productos / materiales de oficina.</p>
    <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-blue-700 hover:underline">← Volver al Panel</a>
</div>
@endsection
