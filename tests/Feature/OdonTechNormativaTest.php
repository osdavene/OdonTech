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

    /**
     * Test 5: Módulo de Agenda - Agendamiento y transición en 1 clic a atención clínica.
     */
    public function test_agendamiento_cita_y_transicion_a_atencion_clinica(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $paciente = Paciente::where('numero_documento', '1018234567')->first();

        $this->actingAs($odontologo);

        $response = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'fecha_hora_inicio' => now()->addHour()->toDateTimeString(),
            'duracion_minutos' => 45,
            'unidad_sillon_numero' => 1,
            'motivo_consulta' => 'Control de ortodoncia',
        ]);

        $response->assertStatus(201)
            ->assertJson(['exito' => true]);

        $citaId = $response->json('cita.id');

        // Transición en 1 clic a atención clínica
        $resAtencion = $this->postJson("/agenda/citas/{$citaId}/iniciar-atencion");
        $resAtencion->assertStatus(200)
            ->assertJson(['exito' => true]);

        $this->assertDatabaseHas('citas_odontologicas', [
            'id' => $citaId,
            'estado' => 'EN_ATENCION',
        ]);
    }

    /**
     * Test 5.1: Prevención estricta de conflictos de agenda (Sillón ocupado, Odontólogo ocupado y Paciente ocupado).
     */
    public function test_prevencion_conflictos_agenda_sillon_odontologo_y_paciente(): void
    {
        $odontologo1 = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $admin = Usuario::where('correo_electronico', 'admin@odontech.com.co')->first();
        $paciente1 = Paciente::where('numero_documento', '1018234567')->first();

        // Crear un segundo paciente para pruebas
        $paciente2 = Paciente::create([
            'tipo_documento' => 'CC',
            'numero_documento' => '52111222',
            'primer_nombre' => 'Camilo',
            'primer_apellido' => 'Duque',
            'fecha_nacimiento' => '1988-03-20',
            'sexo_biologico' => 'M',
            'pais_residencia_codigo' => '170',
            'departamento_codigo' => '11',
            'municipio_codigo' => '11001',
            'zona_residencia' => '01_URBANO',
            'direccion_residencia' => 'Calle 50 # 10-20',
            'telefono_principal' => '3123456789',
            'tipo_usuario_rips' => '01',
            'acepta_tratamiento_datos' => true,
        ]);

        $this->actingAs($odontologo1);

        $horaInicio = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);

        // 1. Cita Base: Paciente 1 con Odontólogo 1 en Sillón 1 de 10:00 AM a 10:45 AM (45 min)
        $resBase = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente1->id,
            'odontologo_id' => $odontologo1->id,
            'fecha_hora_inicio' => $horaInicio->toDateTimeString(),
            'duracion_minutos' => 45,
            'unidad_sillon_numero' => 1,
            'motivo_consulta' => 'Cita Base 10:00 a 10:45',
        ]);
        $resBase->assertStatus(201);

        // 2. Conflicto de Sillón: Paciente 2 con Admin en Sillón 1 a las 10:15 AM (dentro de 10:00-10:45)
        $resConflictoSillon = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente2->id,
            'odontologo_id' => $admin->id,
            'fecha_hora_inicio' => $horaInicio->copy()->addMinutes(15)->toDateTimeString(),
            'duracion_minutos' => 30,
            'unidad_sillon_numero' => 1, // Mismo sillón 1
            'motivo_consulta' => 'Conflicto por sillón',
        ]);
        $resConflictoSillon->assertStatus(422)
            ->assertJson(['exito' => false]);
        $this->assertStringContainsString('Conflicto de Sillón', $resConflictoSillon->json('mensaje'));

        // 3. Conflicto de Profesional: Odontólogo 1 asignado al Sillón 2 a las 10:15 AM
        $resConflictoOdo = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente2->id,
            'odontologo_id' => $odontologo1->id, // Mismo odontólogo
            'fecha_hora_inicio' => $horaInicio->copy()->addMinutes(15)->toDateTimeString(),
            'duracion_minutos' => 30,
            'unidad_sillon_numero' => 2, // Diferente sillón
            'motivo_consulta' => 'Conflicto por profesional',
        ]);
        $resConflictoOdo->assertStatus(422)
            ->assertJson(['exito' => false]);
        $this->assertStringContainsString('Conflicto de Profesional', $resConflictoOdo->json('mensaje'));

        // 4. Conflicto de Paciente: Paciente 1 intentando tener otra cita a las 10:15 AM
        $resConflictoPac = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente1->id, // Mismo paciente
            'odontologo_id' => $admin->id,
            'fecha_hora_inicio' => $horaInicio->copy()->addMinutes(15)->toDateTimeString(),
            'duracion_minutos' => 30,
            'unidad_sillon_numero' => 2,
            'motivo_consulta' => 'Conflicto por paciente',
        ]);
        $resConflictoPac->assertStatus(422)
            ->assertJson(['exito' => false]);
        $this->assertStringContainsString('Conflicto de Paciente', $resConflictoPac->json('mensaje'));

        // 5. Cita Consecutiva Válida: Empieza exactamente a las 10:45 AM (cuando finaliza la primera)
        $resConsecutiva = $this->postJson('/agenda/citas', [
            'paciente_id' => $paciente2->id,
            'odontologo_id' => $odontologo1->id,
            'fecha_hora_inicio' => $horaInicio->copy()->addMinutes(45)->toDateTimeString(),
            'duracion_minutos' => 30,
            'unidad_sillon_numero' => 1,
            'motivo_consulta' => 'Cita consecutiva válida a las 10:45 AM',
        ]);
        $resConsecutiva->assertStatus(201)
            ->assertJson(['exito' => true]);
    }

    /**
     * Test 6: Periodontograma - Sondaje de 6 puntos y cálculo de nivel de inserción clínica.
     */
    public function test_periodontograma_registra_sondaje_y_calcula_cal(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $paciente = Paciente::where('numero_documento', '1018234567')->first();
        $cie = CatalogoCie::where('codigo', 'K053')->first();

        $historia = HistoriaClinica::create([
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'numero_historia' => 'HC-PERIO-001',
            'motivo_consulta' => 'Sangrado de encías',
            'enfermedad_actual' => 'Movilidad dental e inflamación',
            'diagnostico_principal_cie_id' => $cie->id,
            'plan_tratamiento' => 'Terapia periodontal',
            'pronostico' => 'RESERVADO',
            'estado' => 'ABIERTA',
        ]);

        $this->actingAs($odontologo);

        $response = $this->postJson("/clinico/historias/{$historia->id}/periodontograma", [
            'indice_placa_porcentaje' => 25.5,
            'indice_sangrado_porcentaje' => 30.0,
            'diagnostico_periodontal' => 'Periodontitis crónica estadio II grado B',
            'dientes' => [
                [
                    'numero_diente_fdi' => 16,
                    'profundidad_vestibular_distal' => 5,
                    'profundidad_vestibular_medio' => 3,
                    'profundidad_vestibular_mesial' => 4,
                    'margen_vestibular' => 2, // 2mm de recesión
                    'sangrado_al_sondaje' => true,
                    'movilidad_dental' => '1',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson(['exito' => true]);

        $this->assertDatabaseHas('periodontograma_dientes', [
            'numero_diente_fdi' => 16,
            'profundidad_vestibular_distal' => 5,
            'margen_vestibular' => 2,
            'sangrado_al_sondaje' => true,
        ]);
    }

    /**
     * Test 7: Prescripción Médica Farmacológica con sello ReTHUS (Decreto 2200 de 2005).
     */
    public function test_prescripcion_farmacologica_emision_con_rethus(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $paciente = Paciente::where('numero_documento', '1018234567')->first();
        $cie = CatalogoCie::first();

        $historia = HistoriaClinica::create([
            'paciente_id' => $paciente->id,
            'odontologo_id' => $odontologo->id,
            'numero_historia' => 'HC-RECETA-001',
            'motivo_consulta' => 'Infección dental',
            'enfermedad_actual' => 'Absceso periapical',
            'diagnostico_principal_cie_id' => $cie->id,
            'plan_tratamiento' => 'Terapia antimicrobiana y endodoncia',
            'pronostico' => 'FAVORABLE',
            'estado' => 'ABIERTA',
        ]);

        $this->actingAs($odontologo);

        $response = $this->postJson("/clinico/historias/{$historia->id}/formulas", [
            'diagnostico_cie_id' => $cie->id,
            'vigencia_dias' => 30,
            'recomendaciones_generales' => 'Tomar con abundante agua',
            'medicamentos' => [
                [
                    'medicamento_nombre' => 'Amoxicilina',
                    'concentracion' => '500mg',
                    'forma_farmaceutica' => 'Cápsulas',
                    'via_administracion' => 'Oral',
                    'dosis' => '1 cápsula cada 8 horas',
                    'duracion_tratamiento' => '7 días',
                    'cantidad_total' => '21 cápsulas',
                    'indicaciones_uso' => 'Completar el ciclo antibiótico',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson(['exito' => true]);

        $formulaId = $response->json('formula.id');

        $this->assertDatabaseHas('formulas_medicas', [
            'id' => $formulaId,
            'paciente_id' => $paciente->id,
        ]);

        $this->assertDatabaseHas('formula_medicamentos', [
            'formula_medica_id' => $formulaId,
            'medicamento_nombre' => 'Amoxicilina',
            'cantidad_total' => '21 cápsulas',
        ]);

        // Verificar vista imprimible
        $resImpresion = $this->get("/clinico/formulas/{$formulaId}/imprimir");
        $resImpresion->assertStatus(200)
            ->assertSee('Amoxicilina')
            ->assertSee($odontologo->registro_profesional);
    }

    /**
     * Test 8: Renderizado de la Estación Clínica Unificada con pestañas clínicas.
     */
    public function test_estacion_clinica_unificada_renderiza_correctamente(): void
    {
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $this->actingAs($odontologo);

        $response = $this->get('/clinico/estacion');
        $response->assertStatus(200)
            ->assertSee('Estación Clínica Odontológica Unificada')
            ->assertSee('Odontograma Anatómico FDI')
            ->assertSee('Sondaje Periodontal Milimétrico');
    }

    /**
     * Test 9: Autenticación web, sesiones seguras y auditoría inalterable de acceso (Ley 1581 de 2012).
     */
    public function test_inicio_sesion_web_roles_y_cierre(): void
    {
        // 1. Intento fallido con contraseña incorrecta
        $resFallido = $this->post('/login', [
            'correo_electronico' => 'odontologo@odontech.com.co',
            'contrasena' => 'ContrasenaIncorrecta123*',
        ]);
        $resFallido->assertSessionHasErrors('correo_electronico');
        $this->assertGuest();

        // 2. Inicio de sesión exitoso
        $resExito = $this->post('/login', [
            'correo_electronico' => 'odontologo@odontech.com.co',
            'contrasena' => 'Odonto2026*',
        ]);
        $resExito->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Verificar registro de auditoría de inicio de sesión
        $this->assertDatabaseHas('auditorias_accesos', [
            'tipo_evento' => 'INICIO_SESION',
            'rol_usuario' => 'Odontólogo',
        ]);

        // 3. Cierre de sesión
        $resSalir = $this->post('/salir');
        $resSalir->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test 10: Protección de rutas sanitarias para usuarios no autenticados.
     */
    public function test_proteccion_rutas_sanitarias_redirecciona_a_login(): void
    {
        // Un usuario anónimo no puede ingresar a la estación clínica ni al dashboard
        $resDashboard = $this->get('/dashboard');
        $resDashboard->assertRedirect(route('login'));

        $resEstacion = $this->get('/clinico/estacion');
        $resEstacion->assertRedirect(route('login'));

        $resFacturacion = $this->get('/facturacion/panel');
        $resFacturacion->assertRedirect(route('login'));
    }
}


