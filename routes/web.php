<?php

use App\Http\Controllers\Clinico\ConsentimientoController;
use App\Http\Controllers\Clinico\HistoriaClinicaController;
use App\Http\Controllers\Clinico\OdontogramaController;
use App\Http\Controllers\Facturacion\FacturacionRipsController;
use App\Http\Controllers\Inventario\InventarioInvimaController;
use App\Http\Controllers\Seguridad\AutenticacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ─── 1. MÓDULO DE SEGURIDAD Y AUDITORÍA (Ley 1581 de 2012) ───────────────────
Route::prefix('seguridad')->group(function () {
    Route::post('/ingresar', [AutenticacionController::class, 'ingresar'])->name('seguridad.ingresar');
    Route::post('/salir', [AutenticacionController::class, 'salir'])->name('seguridad.salir');
    Route::get('/usuario-actual', [AutenticacionController::class, 'usuarioActual'])->name('seguridad.usuario');
});

// ─── 2. MÓDULO CLÍNICO (Resoluciones 1995/1999 y 839/2017) ───────────────────
Route::prefix('clinico')->group(function () {
    // Vista y manipulación visual del Odontograma Interactivo FDI
    Route::get('/historias/{id}/odontograma/vista', function ($id) {
        return view('odontograma.interactivo', ['historia' => (object)['id' => $id]]);
    })->name('odontograma.vista');

    // Historia Clínica y Evoluciones Inmutables
    Route::get('/historias/{id}', [HistoriaClinicaController::class, 'ver'])->name('clinico.historias.ver');
    Route::post('/historias', [HistoriaClinicaController::class, 'crear'])->name('clinico.historias.crear');
    Route::post('/historias/{id}/evoluciones', [HistoriaClinicaController::class, 'agregarEvolucion'])->name('clinico.historias.evolucion');

    // Odontograma FDI
    Route::get('/historias/{id}/odontograma', [OdontogramaController::class, 'obtenerPorHistoria'])->name('clinico.odontograma.obtener');
    Route::post('/historias/{id}/odontograma', [OdontogramaController::class, 'guardar'])->name('clinico.odontograma.guardar');

    // Consentimientos Informados Odontológicos
    Route::post('/historias/{id}/consentimientos', [ConsentimientoController::class, 'crear'])->name('clinico.consentimientos.crear');
    Route::post('/consentimientos/{id}/firmar', [ConsentimientoController::class, 'firmar'])->name('clinico.consentimientos.firmar');
    Route::post('/consentimientos/{id}/revocar', [ConsentimientoController::class, 'revocar'])->name('clinico.consentimientos.revocar');
});

// ─── 3. MÓDULO DE FACTURACIÓN Y RIPS (Res. 2275/2023, 510/2024 y DIAN) ──────
Route::prefix('facturacion')->group(function () {
    Route::post('/facturas', [FacturacionRipsController::class, 'crearFactura'])->name('facturacion.crear');
    Route::post('/facturas/{id}/enviar-dian', [FacturacionRipsController::class, 'enviarDian'])->name('facturacion.enviar.dian');
    Route::post('/facturas/{id}/generar-rips', [FacturacionRipsController::class, 'generarRips'])->name('facturacion.generar.rips');
    Route::get('/facturas/{id}/descargar-rips', [FacturacionRipsController::class, 'descargarJsonRips'])->name('facturacion.descargar.rips');
    Route::post('/facturas/{id}/validar-sispro', [FacturacionRipsController::class, 'validarSispro'])->name('facturacion.validar.sispro');
});

// ─── 4. MÓDULO DE INVENTARIO Y CONTROL INVIMA ───────────────────────────────
Route::prefix('inventario')->group(function () {
    Route::get('/insumos', [InventarioInvimaController::class, 'listarInsumos'])->name('inventario.insumos.listar');
    Route::post('/insumos', [InventarioInvimaController::class, 'crearInsumo'])->name('inventario.insumos.crear');
    Route::post('/insumos/{id}/lotes', [InventarioInvimaController::class, 'registrarLote'])->name('inventario.lotes.registrar');
    Route::post('/inspeccionar-sanidad', [InventarioInvimaController::class, 'inspeccionarSanidad'])->name('inventario.inspeccion');
    Route::get('/alertas', [InventarioInvimaController::class, 'alertasPendientes'])->name('inventario.alertas');
});
