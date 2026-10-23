@extends('layouts.app')

@section('title', 'Gestión de Productos')

@section('header-actions')
                    <a href="{{ route('almacen.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">← Volver al Almacén</a>
                    <div class="relative inline-block text-left dropdown-export ml-auto">
                        <button type="button" onclick="event.stopPropagation(); document.getElementById('dropdown-materiales').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">
                            EXPORTAR
                        </button>
                        <div id="dropdown-materiales" class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                            <a href="{{ route('inventario.exportar.excel') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">EXPORTAR A EXCEL</a>
                            <a href="{{ route('inventario.exportar.word') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">EXPORTAR A WORD</a>
                            <a href="{{ route('inventario.exportar.pdf') }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">EXPORTAR A PDF</a>
                        </div>
                    </div>
@endsection

@section('content')
    {{-- Mensajes flash --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Catálogo de Materiales de Oficina</h2>
                    <p class="text-slate-500 text-sm mt-1">Total registrados: <span id="total-registrados">{{ $materials->count() }}</span> · Visibles: <span id="total-visibles">{{ $materials->count() }}</span></p>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="abrirModalCategoria()" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">
                        + Añadir Categoría
                    </button>
                    <button type="button" onclick="abrirModalCrear()" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow">
                        + Añadir Producto
                    </button>
                </div>
            </div>

            {{-- =================== ALERTA DE STOCK BAJO =================== --}}
            @if ($stockBajo->count() > 0)
                <div class="mb-5 rounded-xl border-2 border-amber-300 bg-gradient-to-r from-amber-50 to-yellow-50 p-4 shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-200 rounded-full flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-amber-800 uppercase tracking-wide">
                                ⚠ Alerta de Stock Bajo · {{ $stockBajo->count() }} {{ $stockBajo->count() === 1 ? 'producto' : 'productos' }}
                            </h4>
                            <p class="text-xs text-amber-700 mt-1 mb-2">Los siguientes productos activos tienen stock igual o menor a su stock mínimo configurado:</p>
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($stockBajo as $alerta)
                                    <li class="inline-flex items-center bg-white border border-amber-300 text-amber-900 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        {{ $alerta->nombre }}
                                        <span class="ml-2 text-amber-600 font-mono text-[10px]">({{ $alerta->codigo }}: {{ $alerta->stock }}/{{ $alerta->stock_minimo }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =================== BUSCADOR INTERACTIVO (FILTROS EN TIEMPO REAL) =================== --}}
            <div class="mb-5 grid grid-cols-1 md:grid-cols-3 gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">🔍 Buscar por nombre o código</label>
                    <input id="filtro-busqueda" type="text" placeholder="Escriba para filtrar..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">📊 Estado</label>
                    <select id="filtro-estado" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Solo Activos</option>
                        <option value="Inactivo">Solo Inactivos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">🏷 Categoría</label>
                    <select id="filtro-categoria" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="filas-vacias" class="hidden mb-4 p-4 bg-slate-50 border border-dashed border-slate-300 rounded-lg text-center text-sm text-slate-500">
                No hay productos que coincidan con los filtros aplicados.
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b-2 border-slate-200">
                            <th class="py-3 pr-4 font-semibold">Código</th>
                            <th class="py-3 pr-4 font-semibold">Nombre</th>
                            <th class="py-3 pr-4 font-semibold">Categoría</th>
                            <th class="py-3 pr-4 font-semibold">Stock</th>
                            <th class="py-3 pr-4 font-semibold">Stock Mín.</th>
                            <th class="py-3 pr-4 font-semibold">Unidades</th>
                            <th class="py-3 pr-4 font-semibold">Estado</th>
                            <th class="py-3 pr-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $material)
                            <tr class="border-b border-slate-100 hover:bg-slate-50"
                                data-estado="{{ $material->estado }}"
                                data-categoria="{{ $material->categoria_id }}"
                                data-search="{{ strtolower($material->codigo . ' ' . $material->nombre) }}">
                                <td class="py-3 pr-4 font-mono text-slate-700">{{ $material->codigo }}</td>
                                <td class="py-3 pr-4 text-slate-800 font-medium">{{ $material->nombre }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $material->categoria->nombre ?? 'Sin categoría' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="font-semibold {{ $material->stock <= $material->stock_minimo ? 'text-red-600' : 'text-slate-800' }}">{{ $material->stock }}</span>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ $material->stock_minimo }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $material->medida ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    @if ($material->isInactivo())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-200 text-slate-600">Inactivo</span>
                                    @elseif ($material->stock <= $material->stock_minimo)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Stock Bajo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Disponible</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-right">
                                    <a href="{{ route('materials.edit', $material->id) }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold mr-1">Editar</a>

                                    @if ($material->isInactivo())
                                        {{-- Reactivar: solo si esta Inactivo --}}
                                        <form action="{{ route('materials.reactivar.web', $material->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Reactivar el material {{ $material->nombre }}? Volvera a estar disponible para nuevas solicitudes.')">
                                            @csrf
                                            <button type="submit" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-semibold mr-1">Reactivar</button>
                                        </form>
                                    @else
                                        {{-- Eliminar: se desactiva si tiene pedidos, o se elimina si no tiene --}}
                                        <form action="{{ route('materials.destroy.web', $material->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar el material {{ $material->nombre }}?\n\nSi tiene pedidos anteriores, se desactivara para preservar el historial.\nSi no tiene pedidos, se eliminara por completo.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-8 text-center text-slate-400">No hay materiales registrados todavía.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- =================== MODAL: AÑADIR/EDITAR CATEGORÍA =================== --}}
    <div id="modal-categoria" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-4 flex items-center justify-between">
                <h3 id="modal-categoria-titulo" class="text-lg font-bold text-white">Añadir Nueva Categoría</h3>
                <button type="button" onclick="cerrarModalCategoria()" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Formulario de creacion / edicion --}}
            <form id="form-categoria" action="{{ route('categorias.store.web') }}" method="POST" class="p-6 border-b border-slate-200">
                @csrf
                <input type="hidden" id="form-categoria-method" name="_method" value="POST" disabled>
                <div class="flex items-end space-x-2">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre de la categoría</label>
                        <input type="text" id="form-categoria-nombre" name="nombre" required maxlength="255" placeholder="Ej: Papelería, Tintas, Útiles de escritorio..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <p class="text-xs text-slate-500 mt-1">El nombre debe ser único. Aparecerá inmediatamente en el selector de categorías del formulario de productos.</p>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <button type="submit" id="btn-guardar-categoria" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold whitespace-nowrap">Guardar</button>
                        <button type="button" id="btn-cancelar-edicion" onclick="cancelarEdicionCategoria()" class="hidden px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-xs font-semibold whitespace-nowrap">Cancelar edición</button>
                    </div>
                </div>
            </form>

            {{-- Listado de categorias existentes --}}
            <div class="p-6">
                <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Categorías Existentes <span class="text-slate-400 font-normal">({{ count($categorias) }})</span></h4>

                @if (count($categorias) > 0)
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-500 bg-slate-50 border-b border-slate-200">
                                    <th class="py-2.5 px-4 font-semibold">#</th>
                                    <th class="py-2.5 px-4 font-semibold">Nombre</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categorias as $cat)
                                    <tr class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50">
                                        <td class="py-2.5 px-4 text-slate-500 font-mono text-xs">{{ $loop->iteration }}</td>
                                        <td class="py-2.5 px-4 text-slate-800 font-medium">{{ $cat->nombre }}</td>
                                        <td class="py-2.5 px-4 text-right whitespace-nowrap">
                                            {{-- Boton Editar: rellena el form superior --}}
                                            <button type="button" onclick="editarCategoria({{ $cat->id }}, '{{ addslashes($cat->nombre) }}')" class="inline-flex items-center bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1 rounded text-xs font-semibold mr-1" title="Editar categoria">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Editar
                                            </button>

                                            {{-- Boton Eliminar: formulario POST con @method DELETE --}}
                                            <form action="{{ route('categorias.destroy.web', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar la categoria {{ $cat->nombre }}? Esta accion no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded text-xs font-semibold" title="Eliminar categoria">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" /></svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic text-center py-6">Aún no hay categorías registradas. Crea la primera usando el formulario superior.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- =================== MODAL: CREAR NUEVO MATERIAL =================== --}}
    <div id="modal-crear" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Crear Nuevo Material</h3>
                <button type="button" onclick="cerrarModalCrear()" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="{{ route('materials.store.web') }}" method="POST" class="p-6 space-y-3">
                @csrf

                {{-- Titulo formal de la interfaz --}}
                <div class="mb-4 pb-3 border-b-2 border-blue-700">
                    <h3 class="text-lg font-extrabold text-blue-900 uppercase tracking-wide">
                        Interfaz de Registro de Nuevo Material o Producto
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Complete todos los campos obligatorios (*) para registrar un nuevo artículo en el inventario del almacén.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-lg px-3 py-2">
                    El <strong>código</strong> se generará automáticamente con formato <strong>MAT-XXXX</strong> al guardar.
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
                    <input type="text" name="nombre" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Categoría</label>
                    <select name="categoria_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Seleccione --</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Stock</label>
                        <input type="number" name="stock" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Stock Mín.</label>
                        <input type="number" name="stock_minimo" min="0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Unidades</label>
                        <input type="text" name="medida" placeholder="unidad, kg, m" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Descripción (opcional)</label>
                    <textarea name="descripcion" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-3">
                    <button type="button" onclick="cerrarModalCrear()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-semibold">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ============================================================
        // Apertura / cierre de modales (helpers globales)
        // ============================================================
        const modalCategoria = document.getElementById('modal-categoria');
        const modalCrear     = document.getElementById('modal-crear');
        function abrirModalCategoria() { modalCategoria.classList.remove('hidden'); }
        function cerrarModalCategoria() { modalCategoria.classList.add('hidden'); cancelarEdicionCategoria(); }
        function abrirModalCrear()      { modalCrear.classList.remove('hidden'); }
        function cerrarModalCrear()      { modalCrear.classList.add('hidden'); }

        // ============================================================
        // Lógica del modal de categorías: alternar entre CREAR y EDITAR
        // ============================================================
        const formCategoria        = document.getElementById('form-categoria');
        const inputCategoriaNombre = document.getElementById('form-categoria-nombre');
        const inputCategoriaMethod = document.getElementById('form-categoria-method');
        const tituloModalCategoria = document.getElementById('modal-categoria-titulo');
        const btnCancelarEdicion   = document.getElementById('btn-cancelar-edicion');

        function cancelarEdicionCategoria() {
            formCategoria.action = '{{ route('categorias.store.web') }}';
            inputCategoriaMethod.value = 'POST';
            inputCategoriaMethod.disabled = true;
            inputCategoriaNombre.value = '';
            tituloModalCategoria.textContent = 'Añadir Nueva Categoría';
            btnCancelarEdicion.classList.add('hidden');
        }

        function editarCategoria(id, nombre) {
            formCategoria.action = '/categorias/' + id;
            inputCategoriaMethod.value = 'PUT';
            inputCategoriaMethod.disabled = false;
            inputCategoriaNombre.value = nombre;
            tituloModalCategoria.textContent = 'Editar Categoría';
            btnCancelarEdicion.classList.remove('hidden');
            inputCategoriaNombre.focus();
            inputCategoriaNombre.select();
        }

        // ============================================================
        // Filtros en tiempo real (buscador + estado + categoría)
        // ============================================================
        const filtroBusqueda  = document.getElementById('filtro-busqueda');
        const filtroEstado    = document.getElementById('filtro-estado');
        const filtroCategoria = document.getElementById('filtro-categoria');
        const totalVisibles   = document.getElementById('total-visibles');
        const mensajeVacio    = document.getElementById('filas-vacias');
        const filasMateriales = document.querySelectorAll('tbody tr[data-estado]');

        function aplicarFiltros() {
            const texto     = (filtroBusqueda.value || '').toLowerCase().trim();
            const estado    = filtroEstado.value;
            const categoria = filtroCategoria.value;
            let visibles = 0;

            filasMateriales.forEach(function (fila) {
                const estadoFila    = fila.getAttribute('data-estado')    || '';
                const categoriaFila = fila.getAttribute('data-categoria') || '';
                const textoFila     = fila.getAttribute('data-search')    || '';

                if ((!texto     || textoFila.indexOf(texto) !== -1) &&
                    (!estado    || estadoFila === estado) &&
                    (!categoria || categoriaFila === categoria)) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            if (totalVisibles) totalVisibles.textContent = visibles;
            if (mensajeVacio)  mensajeVacio.classList.toggle('hidden', visibles !== 0);
        }

        filtroBusqueda.addEventListener('input',   aplicarFiltros);
        filtroEstado.addEventListener('change',   aplicarFiltros);
        filtroCategoria.addEventListener('change', aplicarFiltros);

        // Cierre con tecla ESC (sirve para ambos modales)
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (!modalCategoria.classList.contains('hidden')) cerrarModalCategoria();
            if (!modalCrear.classList.contains('hidden'))     cerrarModalCrear();
        });

        window.addEventListener('click', function(e){
            document.querySelectorAll('.dropdown-export').forEach(function(container){
                if(!container.contains(e.target)){
                    container.querySelector('[id^="dropdown-"]').classList.add('hidden');
                }
            });
        });
    </script>
@endpush
