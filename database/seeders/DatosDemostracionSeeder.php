<?php

namespace Database\Seeders;

use App\Models\CatalogoCie;
use App\Models\CatalogoCups;
use App\Models\Cita;
use App\Models\EvolucionClinica;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\FormulaMedica;
use App\Models\FormulaMedicamento;
use App\Models\HistoriaClinica;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Odontograma;
use App\Models\OdontogramaDiente;
use App\Models\Paciente;
use App\Models\Periodontograma;
use App\Models\PeriodontogramaDiente;
use App\Models\RipsTransaccion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatosDemostracionSeeder extends Seeder
{
    public function run(): void
    {
        $paciente = Paciente::first();
        $odontologo = Usuario::where('correo_electronico', 'odontologo@odontech.com.co')->first();
        $cupsList = CatalogoCups::all();
        $cieK021 = CatalogoCie::where('codigo', 'K021')->first() ?? CatalogoCie::first();
        $cieK053 = CatalogoCie::where('codigo', 'K053')->first() ?? CatalogoCie::first();

        if (! $paciente || ! $odontologo) {
            return;
        }

        // 1. Historia Clínica Principal
        $historia = HistoriaClinica::firstOrCreate(
            ['numero_historia' => 'HC-1018234567-001'],
            [
                'paciente_id' => $paciente->id,
                'odontologo_id' => $odontologo->id,
                'motivo_consulta' => 'Molestia en molar superior derecho al masticar y sangrado gingival al cepillado.',
                'enfermedad_actual' => 'Paciente femenina de 30 años sin antecedentes patológicos sistémicos de relevancia. Refiere sensibilidad dental a estímulos fríos y dulces en cuadrante superior derecho de 3 semanas de evolución.',
                'antecedentes_medicos' => 'Alergia confirmada a la Penicilina. Niega hipertensión, diabetes o discrasias sanguíneas.',
                'diagnostico_principal_cie_id' => $cieK021->id,
                'plan_tratamiento' => '1. Fase higiénica y detartraje supragingival. 2. Restauración en resina fotocurada diente 16. 3. Terapia periodontal de soporte.',
                'pronostico' => 'FAVORABLE',
                'estado' => 'ABIERTA',
            ]
        );

        // 2. Odontograma Inicial y Dientes
        $odontograma = Odontograma::firstOrCreate(
            ['historia_clinica_id' => $historia->id, 'tipo_odontograma' => 'INICIAL'],
            [
                'odontologo_id' => $odontologo->id,
                'fecha_registro' => now(),
                'observaciones' => 'Higiene bucal regular, presencia de biopelícula y cálculo en caras linguales anteriores.',
            ]
        );

        OdontogramaDiente::updateOrCreate(
            ['odontograma_id' => $odontograma->id, 'numero_diente_fdi' => 16],
            [
                'superficie_oclusal_incisal' => 'CARIES',
                'superficie_mesial' => 'SANO',
                'superficie_distal' => 'SANO',
                'superficie_vestibular' => 'SANO',
                'superficie_lingual_palatina' => 'SANO',
                'estado_general_diente' => 'SANO',
                'observaciones' => 'Cavidad activa en fosa oclusal profunda.',
            ]
        );

        OdontogramaDiente::updateOrCreate(
            ['odontograma_id' => $odontograma->id, 'numero_diente_fdi' => 26],
            [
                'superficie_oclusal_incisal' => 'OBTURACION_RESINA',
                'superficie_mesial' => 'SANO',
                'superficie_distal' => 'SANO',
                'superficie_vestibular' => 'SANO',
                'superficie_lingual_palatina' => 'SANO',
                'estado_general_diente' => 'SANO',
                'observaciones' => 'Restauración oclusal adaptada sin filtración.',
            ]
        );

        // 3. Periodontograma
        $periodontograma = Periodontograma::firstOrCreate(
            ['historia_clinica_id' => $historia->id],
            [
                'odontologo_id' => $odontologo->id,
                'fecha_evaluacion' => now(),
                'indice_placa_porcentaje' => 22.5,
                'indice_sangrado_porcentaje' => 18.0,
                'diagnostico_periodontal' => 'Gingivitis inducida por placa en periodonto intacto',
                'observaciones' => 'Se refuerza técnica de higiene interdental y uso de seda.',
            ]
        );

        PeriodontogramaDiente::updateOrCreate(
            ['periodontograma_id' => $periodontograma->id, 'numero_diente_fdi' => 16],
            [
                'profundidad_vestibular_distal' => 3,
                'profundidad_vestibular_medio' => 2,
                'profundidad_vestibular_mesial' => 3,
                'margen_vestibular' => 0,
                'profundidad_lingual_distal' => 3,
                'profundidad_lingual_medio' => 2,
                'profundidad_lingual_mesial' => 3,
                'margen_lingual' => 0,
                'sangrado_al_sondaje' => true,
                'supuracion' => false,
                'movilidad_dental' => '0',
            ]
        );

        // 4. Evolución Clínica SOAP Inmutable
        $cupsRestauracion = CatalogoCups::where('codigo', '232101')->first() ?? $cupsList->first();
        EvolucionClinica::firstOrCreate(
            ['historia_clinica_id' => $historia->id, 'plan_procedimiento' => 'Aislamiento absoluto con grapa #14, remoción de caries con fresa redonda de baja velocidad, grabado ácido total 15 seg, adhesivo monocapa y obturación con resina Filtek Z350 XT color A2. Pulido con discos Sof-Lex y control de oclusión con papel articular.'],
            [
                'odontologo_id' => $odontologo->id,
                'cups_id' => $cupsRestauracion->id,
                'tipo_registro' => 'EVOLUCION_TRATAMIENTO',
                'fecha_hora_atencion' => now()->subHours(2),
                'descripcion_subjetiva' => 'Paciente asiste tranquila, refiere no sentir dolor agudo el día de hoy.',
                'descripcion_objetiva' => 'Diente 16 presenta cavitación oclusal pigmentada, sensibilidad negativa a la percusión vertical y horizontal. Vitalidad pulpar positiva normal.',
                'analisis_diagnostico' => 'Caries de la dentina localizada en cara oclusal de diente 16 (CIE-10: K021). Pronóstico favorable.',
                'diente_fdi' => 16,
                'superficie_diente' => 'Oclusal (O)',
                'complicaciones' => null,
                'proxima_cita_sugerida' => now()->addDays(7)->format('Y-m-d'),
                'firma_digital_hash' => hash('sha256', "EVO-16-{$historia->id}-" . now()->toIso8601String()),
            ]
        );

        // 5. Prescripción Médica Farmacológica
        $formula = FormulaMedica::firstOrCreate(
            ['historia_clinica_id' => $historia->id],
            [
                'paciente_id' => $paciente->id,
                'odontologo_id' => $odontologo->id,
                'diagnostico_cie_id' => $cieK021->id,
                'fecha_emision' => now(),
                'vigencia_dias' => 30,
                'recomendaciones_generales' => 'Tomar analgésico solo en caso de presentar dolor postoperatorio. Mantener buena higiene dental sin cepillar con fuerza excesiva la zona restaurada durante las primeras 24 horas.',
                'firma_digital_hash' => hash('sha256', "RECETA-0001-" . now()->toIso8601String()),
            ]
        );

        FormulaMedicamento::firstOrCreate(
            ['formula_medica_id' => $formula->id, 'medicamento_nombre' => 'Ibuprofeno'],
            [
                'concentracion' => '400 mg',
                'forma_farmaceutica' => 'Tabletas recubiertas',
                'via_administracion' => 'Oral',
                'dosis' => '1 tableta cada 8 horas únicamente si presenta molestia dental',
                'duracion_tratamiento' => '3 días',
                'cantidad_total' => '6 tabletas',
                'indicaciones_uso' => 'Consumir siempre después de los alimentos para proteger la mucosa gástrica.',
            ]
        );

        // 6. Citas del Día (Agenda Odontológica)
        Cita::firstOrCreate(
            ['paciente_id' => $paciente->id, 'fecha_hora_inicio' => now()->startOfHour()],
            [
                'odontologo_id' => $odontologo->id,
                'usuario_creador_id' => $odontologo->id,
                'historia_clinica_id' => $historia->id,
                'cups_propuesto_id' => $cupsRestauracion->id,
                'fecha_hora_fin' => now()->startOfHour()->addMinutes(45),
                'unidad_sillon_numero' => 1,
                'motivo_consulta' => 'Restauración estética con resina molar superior (Diente 16)',
                'estado' => 'EN_ATENCION',
                'observaciones' => 'Paciente puntual en recepción.',
            ]
        );

        // 7. Factura Electrónica de Salud DIAN & RIPS
        $factura = Factura::firstOrCreate(
            ['numero_factura' => 'FEV-1001'],
            [
                'paciente_id' => $paciente->id,
                'historia_clinica_id' => $historia->id,
                'usuario_id' => $odontologo->id,
                'tipo_documento' => 'FACTURA_ELECTRONICA_VENTA',
                'prefijo' => 'FEV',
                'consecutivo' => 1001,
                'resolucion_dian' => '18764000001 del 2026-01-10',
                'fecha_emision' => now()->subDay(),
                'fecha_vencimiento' => now()->addDays(30),
                'medio_pago_codigo' => '10',
                'subtotal' => 110000,
                'descuento' => 0,
                'iva' => 0,
                'copago' => 0,
                'cuota_moderadora' => 0,
                'total_pagar' => 110000,
                'cufe' => '7b39a4f912c74d81e05d2c730248a31e8bf29e71ab470123cde89104fa3948b2',
                'qr_cadena' => 'NumFac=FEV-1001;FecFac=2026-10-08;NitFac=901234567;DocAdq=1018234567;ValFac=110000.00;ValIva=0.00;ValTotal=110000.00;CUFE=7b39a4f912c74d81e05d2c730248a31e8bf29e71ab470123cde89104fa3948b2',
                'estado_dian' => 'VALIDADA_DIAN',
                'dian_respuesta_codigo' => '00',
                'dian_respuesta_mensaje' => 'Factura electrónica validada y aprobada por la DIAN en ambiente de producción.',
                'dian_fecha_validacion' => now()->subDay(),
                'rips_estado' => 'VALIDADO_CUV',
                'rips_cuv' => 'CUV-ODONTECH-2026-94812401',
                'rips_fecha_generacion' => now()->subDay(),
                'rips_fecha_validacion' => now()->subDay(),
            ]
        );

        FacturaDetalle::firstOrCreate(
            ['factura_id' => $factura->id, 'cups_id' => $cupsRestauracion->id],
            [
                'cie_id' => $cieK021->id,
                'descripcion_concepto' => 'Obturación dental con resina de fotocurado - Diente 16 Oclusal',
                'cantidad' => 1,
                'valor_unitario' => 110000,
                'valor_descuento' => 0,
                'valor_total' => 110000,
                'diente_fdi' => 16,
            ]
        );

        RipsTransaccion::firstOrCreate(
            ['factura_id' => $factura->id],
            [
                'num_documento_id_obligado' => '901234567',
                'num_factura' => 'FEV-1001',
                'cufe' => '7b39a4f912c74d81e05d2c730248a31e8bf29e71ab470123cde89104fa3948b2',
                'cuv_sispro' => 'CUV-ODONTECH-2026-94812401',
                'estado' => 'VALIDADO_APROBADO',
                'json_sispro_respuesta' => 'Archivo JSON RIPS estructurado y validado conforme a Resolución 2275 de 2023.',
                'json_rips_enviado' => json_encode([
                    'numFactura' => 'FEV-1001',
                    'prestador' => '110010000001',
                    'tipoNota' => null,
                    'numNota' => null,
                    'cuv' => 'CUV-ODONTECH-2026-94812401',
                ]),
            ]
        );
    }
}
