<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CorrespondenciaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas de autenticación
Route::get('/', function () {
    return view('auth.login');
})->name('login.form');

Route::post('/login', [AuthController::class, 'login'])->name('login');

// Cierre de sesión nativo (vía formulario POST desde la modal del dashboard)
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Vista del panel de control (Dashboard) - libre de middleware para pruebas
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Actualización de perfil desde la modal del dashboard (PUT)
Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
Route::post('/change-password', [AuthController::class, 'changePassword'])->name('profile.password');

// Vistas web de los 4 módulos (control de acceso por rol desde el controlador)
Route::middleware('auth')->group(function () {

    // Productos / Materiales (CRUD completo, solo Administrador)
    Route::get('/productos', [MaterialController::class, 'webIndex'])->name('materials.index');
    Route::post('/productos', [MaterialController::class, 'webStore'])->name('materials.store.web');
    Route::get('/productos/{id}/editar', [MaterialController::class, 'webEdit'])->name('materials.edit');
    Route::put('/productos/{id}', [MaterialController::class, 'webUpdate'])->name('materials.update.web');
    Route::delete('/productos/{id}', [MaterialController::class, 'webDestroy'])->name('materials.destroy.web');
    Route::post('/productos/{id}/reactivar', [MaterialController::class, 'webReactivar'])->name('materials.reactivar.web');

    // Pedidos (multi-rol, incluyendo creación para Admin)
    Route::get('/pedidos', [PedidoController::class, 'webIndex'])->name('orders.index');
    Route::get('/pedidos/nuevo', [PedidoController::class, 'webCreate'])->name('orders.create');
    Route::post('/pedidos', [PedidoController::class, 'webStore'])->name('orders.store.web');
    Route::post('/pedidos/{id}/autorizar', [PedidoController::class, 'webAutorizar'])->name('orders.autorizar.web');
    Route::post('/pedidos/{id}/rechazar', [PedidoController::class, 'webRechazar'])->name('orders.rechazar.web');

    // Aliases con el nombre corto solicitado en la vista
    Route::post('/pedidos/{id}/aprobar',   [PedidoController::class, 'webAutorizar'])->name('pedidos.aprobar');
    Route::post('/pedidos/{id}/rechazar', [PedidoController::class, 'rechazar'])->name('pedidos.rechazar');

    // Exportación de pedidos e inventario (Sprint 4)
    Route::get('/pedidos/{id}/exportar-pdf', [PedidoController::class, 'exportarPDF'])->name('pedidos.exportar.pdf');
    Route::get('/pedidos/{id}/exportar-word', [PedidoController::class, 'exportarWord'])->name('pedidos.exportar.word');

    Route::get('/inventario/exportar-excel', [MaterialController::class, 'exportarExcel'])->name('inventario.exportar.excel');
    Route::get('/inventario/exportar-word', [MaterialController::class, 'exportarWord'])->name('inventario.exportar.word');
    Route::get('/inventario/exportar-pdf', [MaterialController::class, 'exportarPDF'])->name('inventario.exportar.pdf');

    // Categorias (CRUD web completo para soporte de materiales)
    Route::post('/categorias',                 [CategoriaController::class, 'webStore'])->name('categorias.store.web');
    Route::put('/categorias/{id}',             [CategoriaController::class, 'webUpdate'])->name('categorias.update.web');
    Route::delete('/categorias/{id}',          [CategoriaController::class, 'webDestroy'])->name('categorias.destroy.web');

    // Cites / Correspondencia (CRUD completo)
    Route::get('/cites', [CorrespondenciaController::class, 'webIndex'])->name('correspondences.index');
    Route::get('/cites/nuevo', [CorrespondenciaController::class, 'webCreate'])->name('correspondences.create');
    Route::post('/cites', [CorrespondenciaController::class, 'webStore'])->name('correspondences.store.web');
    Route::get('/cites/{id}/editar', [CorrespondenciaController::class, 'webEdit'])->name('correspondences.edit');
    Route::put('/cites/{id}', [CorrespondenciaController::class, 'webUpdate'])->name('correspondences.update.web');
    Route::delete('/cites/{id}', [CorrespondenciaController::class, 'webDestroy'])->name('correspondences.destroy.web');
    Route::get('/cites/{id}/descargar', [CorrespondenciaController::class, 'webDownload'])->name('correspondences.download.web');
    Route::get('/cites/siguiente-codigo', [CorrespondenciaController::class, 'nextCode'])->name('correspondences.nextCode.web');

    // Almacén (vista independiente que agrupa Pedidos y Productos)
    Route::get('/almacen', function () { return view('almacen.index'); })->name('almacen.index');

    // Usuarios (CRUD completo, solo Administrador)
    Route::get('/usuarios', [UserController::class, 'webIndex'])->name('users.index');
    Route::get('/usuarios/nuevo', [UserController::class, 'webCreate'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'webStore'])->name('users.store.web');
    Route::get('/usuarios/{id}/editar', [UserController::class, 'webEdit'])->name('users.edit');
    Route::put('/usuarios/{id}', [UserController::class, 'webUpdate'])->name('users.update.web');
    Route::delete('/usuarios/{id}', [UserController::class, 'webDestroy'])->name('users.destroy.web');
    Route::post('/usuarios/{id}/toggle', [UserController::class, 'webToggleActive'])->name('users.toggle.web');
});

// ==================== API REST ====================

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // ==================== SPRINT 1: AUTENTICACIÓN Y GESTIÓN DE USUARIOS ====================

    // HU3: Cerrar sesión
    Route::post('/api/logout', [AuthController::class, 'logout']);

    // HU5: Administrar perfil de usuario
    Route::get('/api/profile', [AuthController::class, 'profile']);
    Route::put('/api/profile', [AuthController::class, 'updateProfile']);

    // HU9: Cambiar contraseña
    Route::post('/api/change-password', [AuthController::class, 'changePassword']);

    // HU6: Gestionar usuarios del sistema
    Route::get('/api/users', [UserController::class, 'index']);
    Route::post('/api/users', [UserController::class, 'store']);
    Route::get('/api/users/{id}', [UserController::class, 'show']);
    Route::put('/api/users/{id}', [UserController::class, 'update']);
    Route::delete('/api/users/{id}', [UserController::class, 'destroy']);

    // HU7: Asignar rol a un usuario
    Route::put('/api/users/{id}/role', [UserController::class, 'assignRole']);

    // HU8: Activar y desactivar usuario
    Route::put('/api/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

    // HU10: Gestionar categorías de materiales
    Route::get('/api/categorias', [CategoriaController::class, 'index']);
    Route::post('/api/categorias', [CategoriaController::class, 'store']);
    Route::get('/api/categorias/{id}', [CategoriaController::class, 'show']);
    Route::put('/api/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/api/categorias/{id}', [CategoriaController::class, 'destroy']);

    // HU11: Gestionar materiales de oficina
    Route::get('/api/materiales', [MaterialController::class, 'index']);
    Route::post('/api/materiales', [MaterialController::class, 'store']);
    Route::get('/api/materiales/{id}', [MaterialController::class, 'show']);
    Route::put('/api/materiales/{id}', [MaterialController::class, 'update']);
    Route::delete('/api/materiales/{id}', [MaterialController::class, 'destroy']);

    // ==================== SPRINT 2: INVENTARIO Y CORRESPONDENCIA ====================

    // HU15: Registrar ingreso de materiales al inventario
    Route::post('/api/inventario/ingreso', [InventarioController::class, 'registrarIngreso']);

    // HU16: Registrar salida de materiales (manual)
    Route::post('/api/inventario/salida', [InventarioController::class, 'registrarSalida']);

    // HU17: Consultar inventario actual
    Route::get('/api/inventario/actual', [InventarioController::class, 'inventarioActual']);

    // HU18: Consultar historial de movimientos
    Route::get('/api/inventario/movimientos', [InventarioController::class, 'historialMovimientos']);
    Route::get('/api/inventario/movimientos/{materialId}', [InventarioController::class, 'historialMovimientos']);

    // HU19: Configurar alerta de stock mínimo
    Route::put('/api/materiales/{id}/stock-minimo', [InventarioController::class, 'configurarStockMinimo']);

    // HU20: Visualizar alertas en el panel principal
    Route::get('/api/inventario/alertas', [InventarioController::class, 'alertasStock']);

    // Correspondencia
    Route::get('/api/correspondencia', [CorrespondenciaController::class, 'index']);
    Route::post('/api/correspondencia/ingreso', [CorrespondenciaController::class, 'storeIngreso']);
    Route::post('/api/correspondencia/egreso', [CorrespondenciaController::class, 'storeEgreso']);
    Route::get('/api/correspondencia/{id}', [CorrespondenciaController::class, 'show']);
    Route::put('/api/correspondencia/{id}', [CorrespondenciaController::class, 'update']);
    Route::delete('/api/correspondencia/{id}', [CorrespondenciaController::class, 'destroy']);
    Route::put('/api/correspondencia/{id}/estado', [CorrespondenciaController::class, 'cambiarEstado']);
    Route::post('/api/correspondencia/{id}/adjuntar', [CorrespondenciaController::class, 'adjuntarArchivo']);

    // ==================== SPRINT 3: PEDIDOS Y REPORTES ====================

    // HU34, HU35, HU36: Realizar pedido con múltiples materiales
    Route::get('/api/pedidos', [PedidoController::class, 'index']);
    Route::post('/api/pedidos', [PedidoController::class, 'store']);
    Route::get('/api/pedidos/{id}', [PedidoController::class, 'show']);
    Route::put('/api/pedidos/{id}', [PedidoController::class, 'update']);
    Route::delete('/api/pedidos/{id}', [PedidoController::class, 'destroy']);

    // HU39, HU40: Bandeja de pedidos pendientes (Admin)
    Route::get('/api/pedidos-pendientes', [PedidoController::class, 'pedidosPendientes']);
    Route::get('/api/pedidos-pendientes/{id}', [PedidoController::class, 'showPendiente']);

    // HU41: Autorizar pedido
    Route::put('/api/pedidos/{id}/autorizar', [PedidoController::class, 'autorizar']);

    // HU42: Rechazar pedido
    Route::put('/api/pedidos/{id}/rechazar', [PedidoController::class, 'rechazar']);

    // HU43: Despachar materiales
    Route::put('/api/pedidos/{id}/despachar', [PedidoController::class, 'despachar']);

    // HU44, HU45, HU46: Reportes
    Route::get('/api/reportes/correspondencia', [ReportsController::class, 'reporteCorrespondencia']);
    Route::get('/api/reportes/inventario', [ReportsController::class, 'reporteInventarioActual']);
    Route::get('/api/reportes/pedidos', [ReportsController::class, 'reportePedidos']);
    Route::get('/api/reportes/movimientos', [ReportsController::class, 'reporteMovimientos']);
    Route::get('/api/reportes/exportar/{tipo}', [ReportsController::class, 'exportarCSV']);
});
