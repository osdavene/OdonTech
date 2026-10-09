<?php

use App\Http\Controllers\Agenda\CitaController;
use App\Http\Controllers\Clinico\ConsentimientoController;
use App\Http\Controllers\Clinico\HistoriaClinicaController;
use App\Http\Controllers\Clinico\OdontogramaController;
use App\Http\Controllers\Clinico\PeriodontogramaController;
use App\Http\Controllers\Facturacion\FacturacionRipsController;
use App\Http\Controllers\Inventario\InventarioInvimaController;
use App\Http\Controllers\Seguridad\AutenticacionController;
use App\Models\AlertaInventario;
use App\Models\Cita;
use App\Models\Factura;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Redirección al Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ─── 0. DASHBOARD PRINCIPAL ──────────────────────────────────────────────────
Route::get('/dashboard', function () {
    $totalCitasHoy = Cita::hoy()->count();
    $totalFacturasCufe = Factura::whereNotNull('cufe')->count();
    $alertasInvimaCount = AlertaInventario::where('resuelta', false)->where('nivel_severidad', 'CRITICA_BLOQUEANTE')->count();

    return view('dashboard', [
        'totalCitasHoy' => $totalCitasHoy,
        'totalFacturasCufe' => $totalFacturasCufe,
        'alertasInvimaCount' => $alertasInvimaCount,
    ]);
})->name('dashboard');

// ─── 1. MÓDULO DE AGENDA Y CITAS MÉDICAS ──────────────────────────────────────
Route::prefix('agenda')->group(function () {
    Route::get('/citas', [CitaController::class, 'listarPorFecha'])->name('agenda.citas.listar');
    Route::post('/citas', [CitaController::class, 'agendarCita'])->name('agenda.citas.agendar');
    Route::patch('/citas/{id}/estado', [CitaController::class, 'actualizarEstado'])->name('agenda.citas.estado');
    Route::post('/citas/{id}/iniciar-atencion', [CitaController::class, 'iniciarAtencionClinica'])->name('agenda.citas.iniciar');
});

// ─── 2. MÓDULO DE ADMISIONES & HABEAS DATA (Ley 1581 de 2012) ─────────────────
Route::prefix('pacientes')->group(function () {
    Route::get('/nuevo', function () {
        return view('pacientes.crear');
    })->name('pacientes.nuevo');

    Route::post('/', function (Request $request) {
        $datos = $request->validate([
            'tipo_documento' => ['required', 'string'],
            'numero_documento' => ['required', 'string', 'unique:pacientes,numero_documento'],
            'primer_nombre' => ['required', 'string'],
            'segundo_nombre' => ['nullable', 'string'],
            'primer_apellido' => ['required', 'string'],
            'segundo_apellido' => ['nullable', 'string'],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo_biologico' => ['required', 'in:F,M,INDETERMINADO'],
            'telefono_principal' => ['required', 'string'],
            'correo_electronico' => ['nullable', 'email'],
            'direccion_residencia' => ['required', 'string'],
            'tipo_usuario_rips' => ['required', 'string'],
            'acepta_tratamiento_datos' => ['required', 'boolean'],
            'firma_digital_base64' => ['nullable', 'string'],
        ]);

        $paciente = Paciente::create([
            ...$datos,
            'pais_residencia_codigo' => '170',
            'departamento_codigo' => '11',
            'municipio_codigo' => '11001',
            'zona_residencia' => '01_URBANO',
            'fecha_aceptacion_datos' => now(),
            'medio_aceptacion_datos' => 'FIRMA_DIGITAL',
            'ip_aceptacion_datos' => $request->ip(),
        ]);

        return redirect()->route('dashboard')->with('exito', 'Paciente admitido con autorización de Habeas Data firmada.');
    })->name('pacientes.guardar');
});

// ─── 3. MÓDULO DE SEGURIDAD Y AUDITORÍA ───────────────────────────────────────
Route::prefix('seguridad')->group(function () {
    Route::post('/ingresar', [AutenticacionController::class, 'ingresar'])->name('seguridad.ingresar');
    Route::post('/salir', [AutenticacionController::class, 'salir'])->name('seguridad.salir');
    Route::get('/usuario-actual', [AutenticacionController::class, 'usuarioActual'])->name('seguridad.usuario');
});

// ─── 4. MÓDULO CLÍNICO (Historias, Odontograma FDI & Periodontograma) ─────────
Route::prefix('clinico')->group(function () {
    // Vista del Odontograma Interactivo FDI
    Route::get('/historias/{id}/odontograma/vista', function ($id) {
        $paciente = Paciente::first();
        return view('odontograma.interactivo', [
            'historia' => (object)['id' => $id, 'numero_historia' => "HC-{$paciente?->numero_documento}-2026"],
            'paciente' => $paciente,
        ]);
    })->name('odontograma.vista');

    // Historia Clínica y Evoluciones Inmutables
    Route::get('/historias/{id}', [HistoriaClinicaController::class, 'ver'])->name('clinico.historias.ver');
    Route::post('/historias', [HistoriaClinicaController::class, 'crear'])->name('clinico.historias.crear');
    Route::post('/historias/{id}/evoluciones', [HistoriaClinicaController::class, 'agregarEvolucion'])->name('clinico.historias.evolucion');

    // Odontograma FDI
    Route::get('/historias/{id}/odontograma', [OdontogramaController::class, 'obtenerPorHistoria'])->name('clinico.odontograma.obtener');
    Route::post('/historias/{id}/odontograma', [OdontogramaController::class, 'guardar'])->name('clinico.odontograma.guardar');

    // Periodontograma
    Route::get('/historias/{id}/periodontograma', [PeriodontogramaController::class, 'obtenerPorHistoria'])->name('clinico.periodontograma.obtener');
    Route::post('/historias/{id}/periodontograma', [PeriodontogramaController::class, 'guardar'])->name('clinico.periodontograma.guardar');

    // Consentimientos Informados
    Route::post('/historias/{id}/consentimientos', [ConsentimientoController::class, 'crear'])->name('clinico.consentimientos.crear');
    Route::post('/consentimientos/{id}/firmar', [ConsentimientoController::class, 'firmar'])->name('clinico.consentimientos.firmar');
    Route::post('/consentimientos/{id}/revocar', [ConsentimientoController::class, 'revocar'])->name('clinico.consentimientos.revocar');
});

// ─── 5. MÓDULO DE FACTURACIÓN Y RIPS ──────────────────────────────────────────
Route::prefix('facturacion')->group(function () {
    Route::get('/panel', function () {
        $facturas = Factura::with(['paciente', 'detalles.cups', 'detalles.diagnosticoCie'])
            ->latest('fecha_emision')
            ->get();
        return view('facturacion.index', ['facturas' => $facturas]);
    })->name('facturacion.panel');

    Route::post('/facturas', [FacturacionRipsController::class, 'crearFactura'])->name('facturacion.crear');
    Route::post('/facturas/{id}/enviar-dian', [FacturacionRipsController::class, 'enviarDian'])->name('facturacion.enviar.dian');
    Route::post('/facturas/{id}/generar-rips', [FacturacionRipsController::class, 'generarRips'])->name('facturacion.generar.rips');
    Route::get('/facturas/{id}/descargar-rips', [FacturacionRipsController::class, 'descargarJsonRips'])->name('facturacion.descargar.rips');
    Route::post('/facturas/{id}/validar-sispro', [FacturacionRipsController::class, 'validarSispro'])->name('facturacion.validar.sispro');

    // Representación gráfica oficial imprimible (Anexo 1.9 DIAN)
    Route::get('/facturas/{id}/imprimir', function ($id) {
        $factura = Factura::with(['paciente', 'detalles.cups', 'detalles.diagnosticoCie'])->findOrFail($id);
        return view('facturacion.representacion_grafica', ['factura' => $factura]);
    })->name('facturacion.imprimir');
});

// ─── 6. MÓDULO DE INVENTARIO Y CONTROL INVIMA ─────────────────────────────────
Route::prefix('inventario')->group(function () {
    Route::get('/insumos', [InventarioInvimaController::class, 'listarInsumos'])->name('inventario.insumos.listar');
    Route::post('/insumos', [InventarioInvimaController::class, 'crearInsumo'])->name('inventario.insumos.crear');
    Route::post('/insumos/{id}/lotes', [InventarioInvimaController::class, 'registrarLote'])->name('inventario.lotes.registrar');
    Route::post('/inspeccionar-sanidad', [InventarioInvimaController::class, 'inspeccionarSanidad'])->name('inventario.inspeccion');
    Route::get('/alertas', [InventarioInvimaController::class, 'alertasPendientes'])->name('inventario.alertas');
});
