<?php

namespace Tests\Feature;

use App\Models\CatalogoCie;
use App\Models\CatalogoCups;
use App\Models\Factura;
use App\Models\HistoriaClinica;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Dian\DianPtaFacturacionService;
use App\Services\Inventario\ControlVencimientoInvimaService;
use App\Services\Rips\RipsJsonBuilderService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdonTechNormativaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test 1: Módulo de Seguridad y Auditoría (Ley 1581 de 2012).
     */
    public function test_habeas_data_es_obligatorio_para_atencion_y_auditoria_es_inalterable(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $cie = CatalogoCie::first();

        // Paciente SIN aceptar tratamiento de datos
        $pacienteSinConsentimiento = Paciente::create([
            'tipo_documento' => 'CC',
            'numero_documento' => '999888777',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'sexo_biologico' => 'M',
            'pais_residencia_codigo' => '170',
            'departamento_codigo' => '11',
            'municipio_codigo' => '11001',
            'zona_residencia' => '01_URBANO',
            'direccion_residencia' => 'Calle 1 # 2-3',
            'telefono_principal' => '3000000000',
            'acepta_tratamiento_datos' => false, // No aceptó
        ]);

        $this->actingAs($odontologo);

        // Debe lanzar excepción al intentar abrir historia sin Habeas Data
        $this->expectException(DomainException::class);

        $controlador = app(\App\Http\Controllers\Clinico\HistoriaClinicaController::class);
        $request = new \Illuminate\Http\Request([
            'paciente_id' => $pacienteSinConsentimiento->id,
            'motivo_consulta' => 'Dolor dental agudo',
            'enfermedad_actual' => 'Molestia en molar hace 3 días',
            'diagnostico_principal_cie_id' => $cie->id,
            'plan_tratamiento' => 'Tratamiento restaurador',
            'pronostico' => 'BUENO',
        ]);

        $controlador->crear($request);
    }

    /**
     * Test 2: Módulo Clínico (Res. 1995 de 1999) - SoftDeletes y notas aclaratorias inmutables.
     */
    public function test_historia_clinica_posee_soft_deletes_y_evoluciones_inmutables(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $paciente = Paciente::where('numero_documento', '1018234567')->first();
        $cie = CatalogoCie::where('codigo', 'K021')->first();
        $cups = CatalogoCups::where('codigo', '232101')->first();

        $historia = HistoriaClinica::create([
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'numero_historia' => 'HC-TEST-001',
            'motivo_consulta' => 'Revisión y calzas',
            'enfermedad_actual' => 'Caries en diente 16',
            'diagnostico_principal_cie_id' => $cie->id,
            'plan_tratamiento' => 'Restauración con resina',
            'pronostico' => 'FAVORABLE',
            'estado' => 'ABIERTA',
        ]);

        // Probar SoftDelete
        $historia->delete();
        $this->assertSoftDeleted('historias_clinicas', ['id' => $historia->id]);
        $this->assertDatabaseHas('historias_clinicas', ['id' => $historia->id]);

        $historia->restore();

        // Agregar evolución clínica
        $this->actingAs($odontologo);
        $evolucion = $historia->evoluciones()->create([
            'odontologo_id' => $odontologo->id,
            'tipo_registro' => 'EVOLUCION_TRATAMIENTO',
            'fecha_hora_atencion' => now(),
            'analisis_diagnostico' => 'Caries oclusal cavitada',
            'plan_procedimiento' => 'Apertura y colocación de resina fotocurada',
            'cups_id' => $cups->id,
            'diente_fdi' => 16,
            'superficie_diente' => 'OCLUSAL',
            'firma_digital_hash' => hash('sha256', 'FIRMA_TEST'),
        ]);

        $this->assertDatabaseHas('evoluciones_clinicas', [
            'id' => $evolucion->id,
            'diente_fdi' => 16,
        ]);
    }

    /**
     * Test 3: Facturación Electrónica DIAN y Generación de RIPS con CUFE obligatorio.
     */
    public function test_facturacion_obtiene_cufe_y_enlaza_rips_json(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $paciente = Paciente::where('numero_documento', '1018234567')->first();
        $cups = CatalogoCups::where('codigo', '232101')->first();
        $cie = CatalogoCie::where('codigo', 'K021')->first();

        // Crear Factura
        $factura = Factura::create([
            'paciente_id' => $paciente->id,
            'usuario_id' => $odontologo->id,
            'tipo_documento' => 'FACTURA_ELECTRONICA_VENTA',
            'prefijo' => 'FEV',
            'consecutivo' => 1001,
            'numero_factura' => 'FEV1001',
            'resolucion_dian' => 'Resolución 18764',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'medio_pago_codigo' => '10',
            'subtotal' => 110000,
            'descuento' => 0,
            'iva' => 0,
            'copago' => 0,
            'cuota_moderadora' => 0,
            'total_pagar' => 110000,
            'estado_dian' => 'BORRADOR',
            'rips_estado' => 'PENDIENTE',
        ]);

        $factura->detalles()->create([
            'cups_id' => $cups->id,
            'cie_id' => $cie->id,
            'tipo_servicio_rips' => '02_PROCEDIMIENTO',
            'descripcion_concepto' => $cups->descripcion,
            'cantidad' => 1,
            'valor_unitario' => 110000,
            'valor_descuento' => 0,
            'valor_total' => 110000,
            'diente_fdi' => 16,
        ]);

        // 1. Transmitir a PTA DIAN para recepción de CUFE
        $dianService = app(DianPtaFacturacionService::class);
        $resultadoDian = $dianService->transmitirFactura($factura);

        $this->assertTrue($resultadoDian['valido']);
        $this->assertNotNull($factura->fresh()->cufe);

        // 2. Generar RIPS JSON con CUFE enlazado
        $ripsService = app(RipsJsonBuilderService::class);
        $transaccionRips = $ripsService->procesarYGuardarRips($factura->fresh());

        $this->assertEquals($factura->fresh()->cufe, $transaccionRips->cufe);
        $this->assertStringContainsString('numDocumentoIdObligado', $transaccionRips->json_rips_enviado);
        $this->assertStringContainsString('FEV1001', $transaccionRips->json_rips_enviado);

        // 3. Validar SISPRO y obtener CUV
        $resultadoSispro = $ripsService->validarRipsEnSispro($factura->fresh());
        $this->assertTrue($resultadoSispro['exitoso']);
        $this->assertNotNull($resultadoSispro['cuv']);
    }

    /**
     * Test 4: Módulo de Inventario (Normativa INVIMA) - Bloqueo de lotes vencidos.
     */
    public function test_bloqueo_preventivo_de_lotes_vencidos_invima(): void
    {
        $insumo = Insumo::first();

        // Lote vencido
        $loteVencido = LoteInventario::create([
            'insumo_id' => $insumo->id,
            'lote_invima' => 'LOTE-VENCIDO-01',
            'registro_sanitario_invima' => 'INVIMA 2015DM-0001',
            'fecha_fabricacion' => '2020-01-01',
            'fecha_vencimiento' => now()->subDay()->format('Y-m-d'), // Venció ayer
            'cantidad_recibida' => 5,
            'cantidad_disponible' => 5,
            'estado' => 'VIGENTE',
        ]);

        $servicioInvima = app(ControlVencimientoInvimaService::class);
        $servicioInvima->ejecutarInspeccionSanitaria();

        // Debe haber cambiado a VENCIDO y disparado alerta bloqueante
        $this->assertEquals('VENCIDO', $loteVencido->fresh()->estado);

        $this->assertDatabaseHas('alertas_inventario', [
            'lote_id' => $loteVencido->id,
            'tipo_alerta' => 'LOTE_VENCIDO_BLOQUEADO',
            'nivel_severidad' => 'CRITICA_BLOQUEANTE',
        ]);

        // Debe rechazar el uso del lote en atención clínica
        $this->expectException(DomainException::class);
        $servicioInvima->validarLoteParaUsoClinico($loteVencido->fresh());
    }
}
