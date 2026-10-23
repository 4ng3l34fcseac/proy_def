@extends('layouts.app')

@section('title', 'Módulo de Almacén e Inventarios')

@section('header-actions')
    <a href="{{ route('dashboard') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">
        ← Volver al Panel
    </a>
@endsection

@section('content')
    <div class="text-center mb-10">

        {{-- Título centrado --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 5l9 4 9-4M3 17l9 4 9-4" />
                </svg>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-800 tracking-tight uppercase">
                Módulo de Almacén e Inventarios
            </h1>
            <p class="text-slate-500 mt-2 text-sm sm:text-base">
                Seleccione una opción para continuar con la gestión correspondiente.
            </p>
            <div class="mt-4 mx-auto w-24 h-1 bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 rounded-full"></div>
        </div>
{{-- Botones grandes centrados --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">

            {{-- Botón: Gestión de Pedidos --}}
            <a href="{{ route('orders.index') }}" class="card-hover group bg-white rounded-2xl shadow-md border border-slate-200 p-10 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-700 rounded-3xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-purple-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Gestión de Pedidos</h2>
                <p class="text-sm text-slate-500 mb-5">
                    Realice, autorice o dé seguimiento a las solicitudes de materiales de oficina del personal.
                </p>
                <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 group-hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition shadow-md">
                    Ingresar
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </span>
            </a>

            {{-- Botón: Catálogo de Productos --}}
            <a href="{{ route('materials.index') }}" class="card-hover group bg-white rounded-2xl shadow-md border border-slate-200 p-10 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-blue-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Catálogo de Productos</h2>
                <p class="text-sm text-slate-500 mb-5">
                    Gestione el catálogo de productos de oficina: altas, ediciones, stock mínimo y categorías.
                </p>
                <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 group-hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-md">
                    Ingresar
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </span>
            </a>

        </div>
@endsection