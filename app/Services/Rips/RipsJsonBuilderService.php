<?php

namespace App\Services\Rips;

use App\Models\Factura;
use App\Models\RipsTransaccion;
use App\Services\Auditoria\AuditoriaService;
use DomainException;
use Illuminate\Support\Carbon;

class RipsJsonBuilderService
{
    /**
     * Construye el payload JSON oficial de RIPS bajo Resoluciones 2275 de 2023 y 510 de 2024.
     * Exige obligatoriamente la presencia y validez del CUFE de la DIAN.
     */
    public function generarJson(Factura $factura): array
    {
        // Validación de regla de negocio y normativa colombiana:
        // No se puede generar RIPS sin CUFE de la DIAN previamente validado.
        if (empty($factura->cufe)) {
            throw new DomainException("La factura {$factura->numero_factura} no posee un CUFE válido. La Resolución 2275 de 2023 exige validar primero la Factura Electrónica ante la DIAN antes de generar los RIPS.");
        }

        $factura->loadMissing([
            'paciente',
            'detalles.cups',
            'detalles.diagnosticoCie',
            'detalles.diagnosticoRelacionadoCie',
            'detalles.evolucion'
        ]);

        $prestador = config('dian.prestador_salud');
        $paciente = $factura->paciente;

        $consultas = [];
        $procedimientos = [];
        $consecutivoServicio = 1;

        foreach ($factura->detalles as $detalle) {
            $esConsulta = ($detalle->tipo_servicio_rips === '01_CONSULTA' || $detalle->cups->tipo_servicio_rips === '01_CONSULTA');
            $fechaAtencion = $detalle->evolucion?->fecha_hora_atencion
                ? Carbon::parse($detalle->evolucion->fecha_hora_atencion)->format('Y-m-d H:i')
                : Carbon::parse($factura->fecha_emision)->format('Y-m-d H:i');

            if ($esConsulta) {
                $consultas[] = [
                    'codPrestador' => (string) $prestador['codigo_reps'],
                    'fechaInicioAtencion' => $fechaAtencion,
                    'numAutorizacion' => null,
                    'codConsulta' => (string) $detalle->cups->codigo,
                    'modalidadGrupoServicioTecSal' => '01', // Intramural
                    'grupoServicios' => '01', // Consulta Externa
                    'codServicio' => 360, // Odontología General (Código REPS)
                    'finalidadTecnologiaSalud' => (string) ($detalle->finalidad_tecnologia_salud ?? '10'),
                    'causaMotivoAtencion' => (string) ($detalle->causa_motivo_atencion ?? '38'),
                    'codDiagnosticoPrincipal' => (string) $detalle->diagnosticoCie->codigo,
                    'codDiagnosticoRelacionado1' => $detalle->diagnosticoRelacionadoCie?->codigo,
                    'codDiagnosticoRelacionado2' => null,
                    'codDiagnosticoRelacionado3' => null,
                    'tipoDiagnosticoPrincipal' => (string) ($detalle->tipo_diagnostico_principal ?? '01'),
                    'tipoDocumentoIdentificacion' => (string) $paciente->tipo_documento,
                    'numDocumentoIdentificacion' => (string) $paciente->numero_documento,
                    'vrServicio' => (float) $detalle->valor_total,
                    'conceptoRecaudo' => '05', // Cuota o pago particular
                    'valorPagoModerador' => (float) $detalle->copago,
                    'numFEVPagoModerador' => (string) $factura->numero_factura,
                    'consecutivo' => $consecutivoServicio++,
                ];
            } else {
                $procedimientos[] = [
                    'codPrestador' => (string) $prestador['codigo_reps'],
                    'fechaInicioAtencion' => $fechaAtencion,
                    'idMIPRES' => null,
                    'numAutorizacion' => null,
                    'codProcedimiento' => (string) $detalle->cups->codigo,
                    'viaIngresoServicioSalud' => '01', // Demanda espontánea
                    'modalidadGrupoServicioTecSal' => '01', // Intramural
                    'grupoServicios' => '01', // Consulta externa / Ambulatorio
                    'codServicio' => 360, // Procedimiento odontológico
                    'finalidadTecnologiaSalud' => (string) ($detalle->finalidad_tecnologia_salud ?? '15'),
                    'causaMotivoAtencion' => (string) ($detalle->causa_motivo_atencion ?? '38'),
                    'codDiagnosticoPrincipal' => (string) $detalle->diagnosticoCie->codigo,
                    'codDiagnosticoRelacionado' => $detalle->diagnosticoRelacionadoCie?->codigo,
                    'codComplicacion' => null,
                    'vrServicio' => (float) $detalle->valor_total,
                    'conceptoRecaudo' => '05',
                    'valorPagoModerador' => (float) $detalle->copago,
                    'numFEVPagoModerador' => (string) $factura->numero_factura,
                    'consecutivo' => $consecutivoServicio++,
                ];
            }
        }

        // Estructura oficial del Ministerio de Salud (SISPRO)
        $payloadRips = [
            'numDocumentoIdObligado' => (string) $prestador['nit'],
            'numFactura' => (string) $factura->numero_factura,
            'tipoNota' => null,
            'numNota' => null,
            'usuarios' => [
                [
                    'tipoDocumentoIdentificacion' => (string) $paciente->tipo_documento,
                    'numDocumentoIdentificacion' => (string) $paciente->numero_documento,
                    'tipoUsuario' => (string) $paciente->tipo_usuario_rips,
                    'fechaNacimiento' => Carbon::parse($paciente->fecha_nacimiento)->format('Y-m-d'),
                    'codSexo' => (string) $paciente->sexo_biologico,
                    'codPaisResidencia' => (string) $paciente->pais_residencia_codigo,
                    'codMunicipioResidencia' => (string) $paciente->municipio_codigo,
                    'codZonaTerritorialResidencia' => $paciente->zona_residencia === '01_URBANO' ? '01' : '02',
                    'incapacidad' => (string) $paciente->incapacidad,
                    'consecutivo' => 1,
                    'servicios' => [
                        'consultas' => $consultas,
                        'procedimientos' => $procedimientos,
                        'urgencias' => [],
                        'hospitalizacion' => [],
                        'recienNacidos' => [],
                        'medicamentos' => [],
                        'otrosServicios' => [],
                    ],
                ],
            ],
        ];

        return $payloadRips;
    }

    /**
     * Genera y guarda la transacción de RIPS en la base de datos asociada a la factura y su CUFE.
     */
    public function procesarYGuardarRips(Factura $factura): RipsTransaccion
    {
        $jsonRips = $this->generarJson($factura);
        $prestador = config('dian.prestador_salud');

        $jsonCodificado = json_encode($jsonRips, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $transaccion = RipsTransaccion::updateOrCreate(
            ['factura_id' => $factura->id],
            [
                'num_documento_id_obligado' => $prestador['nit'],
                'num_factura' => $factura->numero_factura,
                'cufe' => $factura->cufe,
                'tipo_nota' => null,
                'num_nota' => null,
                'json_rips_enviado' => $jsonCodificado,
                'estado' => 'GENERADO',
            ]
        );

        $factura->update([
            'rips_estado' => 'GENERADO',
            'rips_fecha_generacion' => now(),
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'TRANSMISION_RIPS',
            tablaAfectada: 'rips_transacciones',
            registroId: $transaccion->id,
            pacienteId: $factura->paciente_id,
            datosNuevos: [
                'factura' => $factura->numero_factura,
                'cufe' => $factura->cufe,
                'estado' => 'GENERADO'
            ],
            justificacion: 'Generación estandarizada de RIPS bajo Resolución 2275 de 2023 con enlace a CUFE'
        );

        return $transaccion;
    }

    /**
     * Simula o ejecuta la validación del RIPS ante el mecanismo SISPRO / MinSalud
     * generando el CUV (Código Único de Validación).
     */
    public function validarRipsEnSispro(Factura $factura): array
    {
        $transaccion = $factura->transaccionRips ?? $this->procesarYGuardarRips($factura);

        // Generar CUV estandarizado (Código Único de Validación retornado por la plataforma SISPRO)
        $cuvSimulado = 'CUV-' . strtoupper(substr(hash('sha256', $transaccion->cufe . $transaccion->num_factura . now()->timestamp), 0, 32));

        $respuestaSispro = [
            'codigoRespuesta' => '200',
            'mensaje' => 'Validación de estructura y consistencia RIPS satisfactoria con la DIAN',
            'CUV' => $cuvSimulado,
            'fechaValidacion' => now()->toIso8601String(),
            'errores' => [],
        ];

        $transaccion->update([
            'cuv_sispro' => $cuvSimulado,
            'json_sispro_respuesta' => json_encode($respuestaSispro),
            'estado' => 'VALIDADO_APROBADO',
        ]);

        $factura->update([
            'rips_estado' => 'VALIDADO_CUV',
            'rips_cuv' => $cuvSimulado,
            'rips_fecha_validacion' => now(),
        ]);

        return [
            'exitoso' => true,
            'cuv' => $cuvSimulado,
            'respuesta' => $respuestaSispro,
        ];
    }
}
