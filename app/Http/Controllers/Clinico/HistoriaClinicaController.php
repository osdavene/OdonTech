<?php

namespace App\Http\Controllers\Clinico;

use App\Http\Controllers\Controller;
use App\Models\EvolucionClinica;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Services\Auditoria\AuditoriaService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoriaClinicaController extends Controller
{
    /**
     * Consulta una Historia Clínica con auditoría obligatoria de lectura (Ley 1581 de 2012).
     */
    public function ver(int $id): JsonResponse
    {
        $historia = HistoriaClinica::with([
            'paciente',
            'odontologo',
            'diagnosticoPrincipal',
            'diagnosticoRelacionado',
            'evoluciones.procedimientoCups',
            'evoluciones.odontologo',
            'odontogramaUltimo.dientes',
            'periodontogramas.dientes',
            'consentimientos',
        ])->findOrFail($id);

        AuditoriaService::registrarLecturaHistoria($historia->id, $historia->paciente_id);

        return response()->json([
            'exito' => true,
            'historia_clinica' => $historia,
        ]);
    }

    /**
     * Apertura de una nueva Historia Clínica bajo Resolución 1995 de 1999.
     * Valida obligatoriamente la autorización de Habeas Data (Ley 1581 de 2012).
     */
    public function crear(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'motivo_consulta' => ['required', 'string', 'min:5'],
            'enfermedad_actual' => ['required', 'string', 'min:5'],
            'antecedentes_medicos' => ['nullable', 'array'],
            'examen_estomatologico' => ['nullable', 'array'],
            'diagnostico_principal_cie_id' => ['required', 'exists:catalogos_cie,id'],
            'diagnostico_relacionado_cie_id' => ['nullable', 'exists:catalogos_cie,id'],
            'plan_tratamiento' => ['required', 'string'],
            'pronostico' => ['required', 'in:BUENO,FAVORABLE,RESERVADO,DESFAVORABLE'],
        ]);

        $paciente = Paciente::findOrFail($datos['paciente_id']);

        // Validación estricta Ley 1581 de 2012: Consentimiento de Habeas Data requerido
        if (!$paciente->acepta_tratamiento_datos) {
            throw new DomainException('El paciente debe autorizar expresamente el tratamiento de datos personales (Ley 1581 de 2012) antes de abrir su historia clínica odontológica.');
        }

        $numeroHistoria = 'HC-' . $paciente->numero_documento . '-' . date('Y');

        $historia = DB::transaction(function () use ($datos, $numeroHistoria, $paciente) {
            $odontologoId = Auth::id() ?? 1;

            $historiaClinica = HistoriaClinica::create([
                'paciente_id' => $paciente->id,
                'odontologo_id' => $odontologoId,
                'numero_historia' => $numeroHistoria,
                'motivo_consulta' => $datos['motivo_consulta'],
                'enfermedad_actual' => $datos['enfermedad_actual'],
                'antecedentes_medicos' => $datos['antecedentes_medicos'] ?? [],
                'examen_estomatologico' => $datos['examen_estomatologico'] ?? [],
                'diagnostico_principal_cie_id' => $datos['diagnostico_principal_cie_id'],
                'diagnostico_relacionado_cie_id' => $datos['diagnostico_relacionado_cie_id'] ?? null,
                'plan_tratamiento' => $datos['plan_tratamiento'],
                'pronostico' => $datos['pronostico'],
                'estado' => 'ABIERTA',
            ]);

            AuditoriaService::registrar(
                tipoEvento: 'CREACION',
                tablaAfectada: 'historias_clinicas',
                registroId: $historiaClinica->id,
                pacienteId: $paciente->id,
                datosNuevos: $historiaClinica->toArray(),
                justificacion: 'Apertura de historia clínica odontológica'
            );

            return $historiaClinica;
        });

        return response()->json([
            'exito' => true,
            'mensaje' => 'Historia clínica creada exitosamente.',
            'historia_clinica' => $historia,
        ], 201);
    }

    /**
     * Agrega una Evolución o Nota Aclaratoria (Res. 1995/1999).
     * Garantiza la inmutabilidad: NO modifica el registro previo, sino que anexa una nueva nota con sello SHA-256.
     */
    public function agregarEvolucion(Request $request, int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $datos = $request->validate([
            'tipo_registro' => ['required', 'in:EVOLUCION_TRATAMIENTO,NOTA_ACLARATORIA,URGENCIA_ODONTOLOGICA'],
            'descripcion_subjetiva' => ['nullable', 'string'],
            'descripcion_objetiva' => ['nullable', 'string'],
            'analisis_diagnostico' => ['required', 'string'],
            'plan_procedimiento' => ['required', 'string'],
            'cups_id' => ['nullable', 'exists:catalogos_cups,id'],
            'diente_fdi' => ['nullable', 'integer', 'between:11,85'],
            'superficie_diente' => ['nullable', 'string'],
            'complicaciones' => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'proxima_cita_sugerida' => ['nullable', 'date'],
        ]);

        $usuario = Auth::user();
        $odontologoId = $usuario?->id ?? 1;
        $registroProfesional = $usuario?->registro_profesional ?? 'RETHUS-GENERAL';

        $firmaHash = EvolucionClinica::generarSelloDigital($datos, $registroProfesional);

        $evolucion = EvolucionClinica::create([
            'historia_clinica_id' => $historia->id,
            'odontologo_id' => $odontologoId,
            'tipo_registro' => $datos['tipo_registro'],
            'fecha_hora_atencion' => now(),
            'descripcion_subjetiva' => $datos['descripcion_subjetiva'] ?? null,
            'descripcion_objetiva' => $datos['descripcion_objetiva'] ?? null,
            'analisis_diagnostico' => $datos['analisis_diagnostico'],
            'plan_procedimiento' => $datos['plan_procedimiento'],
            'cups_id' => $datos['cups_id'] ?? null,
            'diente_fdi' => $datos['diente_fdi'] ?? null,
            'superficie_diente' => $datos['superficie_diente'] ?? null,
            'complicaciones' => $datos['complicaciones'] ?? null,
            'recomendaciones' => $datos['recomendaciones'] ?? null,
            'proxima_cita_sugerida' => $datos['proxima_cita_sugerida'] ?? null,
            'firma_digital_hash' => $firmaHash,
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'CREACION',
            tablaAfectada: 'evoluciones_clinicas',
            registroId: $evolucion->id,
            pacienteId: $historia->paciente_id,
            datosNuevos: $evolucion->toArray(),
            justificacion: "Registro de {$datos['tipo_registro']} con sellado inalterable"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Evolución / Nota aclaratoria registrada con sellado inmutable.',
            'evolucion' => $evolucion,
        ], 201);
    }
}
