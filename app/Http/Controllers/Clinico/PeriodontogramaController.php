<?php

namespace App\Http\Controllers\Clinico;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinica;
use App\Models\Periodontograma;
use App\Models\PeriodontogramaDiente;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodontogramaController extends Controller
{
    /**
     * Consulta el último periodontograma de la historia clínica.
     */
    public function obtenerPorHistoria(int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $periodontograma = Periodontograma::with('dientes')
            ->where('historia_clinica_id', $historia->id)
            ->latest('fecha_evaluacion')
            ->first();

        AuditoriaService::registrarLecturaHistoria($historia->id, $historia->paciente_id, 'Consulta de periodontograma');

        return response()->json([
            'exito' => true,
            'periodontograma' => $periodontograma,
            'tiene_periodontograma' => !is_null($periodontograma),
        ]);
    }

    /**
     * Guarda el registro de sondaje periodontal y cálculo de inserción clínica.
     */
    public function guardar(Request $request, int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $datos = $request->validate([
            'indice_placa_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'indice_sangrado_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diagnostico_periodontal' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'dientes' => ['required', 'array', 'min:1'],
            'dientes.*.numero_diente_fdi' => ['required', 'integer'],
            'dientes.*.profundidad_vestibular_distal' => ['required', 'integer', 'min:0', 'max:15'],
            'dientes.*.profundidad_vestibular_medio' => ['required', 'integer', 'min:0', 'max:15'],
            'dientes.*.profundidad_vestibular_mesial' => ['required', 'integer', 'min:0', 'max:15'],
            'dientes.*.margen_vestibular' => ['required', 'integer', 'min:-10', 'max:10'],
            'dientes.*.sangrado_al_sondaje' => ['boolean'],
            'dientes.*.movilidad_dental' => ['in:0,1,2,3'],
        ]);

        $periodontograma = DB::transaction(function () use ($datos, $historia) {
            $odontologoId = Auth::id() ?? 1;

            $nuevo = Periodontograma::create([
                'historia_clinica_id' => $historia->id,
                'odontologo_id' => $odontologoId,
                'fecha_evaluacion' => now(),
                'indice_placa_porcentaje' => $datos['indice_placa_porcentaje'] ?? 0,
                'indice_sangrado_porcentaje' => $datos['indice_sangrado_porcentaje'] ?? 0,
                'diagnostico_periodontal' => $datos['diagnostico_periodontal'] ?? 'Evaluación de soporte periodontal',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($datos['dientes'] as $d) {
                PeriodontogramaDiente::create([
                    'periodontograma_id' => $nuevo->id,
                    'numero_diente_fdi' => $d['numero_diente_fdi'],
                    'profundidad_vestibular_distal' => $d['profundidad_vestibular_distal'],
                    'profundidad_vestibular_medio' => $d['profundidad_vestibular_medio'],
                    'profundidad_vestibular_mesial' => $d['profundidad_vestibular_mesial'],
                    'profundidad_lingual_distal' => $d['profundidad_lingual_distal'] ?? 2,
                    'profundidad_lingual_medio' => $d['profundidad_lingual_medio'] ?? 2,
                    'profundidad_lingual_mesial' => $d['profundidad_lingual_mesial'] ?? 2,
                    'margen_vestibular' => $d['margen_vestibular'],
                    'margen_lingual' => $d['margen_lingual'] ?? 0,
                    'sangrado_al_sondaje' => $d['sangrado_al_sondaje'] ?? false,
                    'supuracion' => $d['supuracion'] ?? false,
                    'movilidad_dental' => $d['movilidad_dental'] ?? '0',
                    'lesion_furca' => $d['lesion_furca'] ?? 'NO_APLICA',
                ]);
            }

            AuditoriaService::registrar(
                tipoEvento: 'CREACION',
                tablaAfectada: 'periodontogramas',
                registroId: $nuevo->id,
                pacienteId: $historia->paciente_id,
                justificacion: 'Registro clínico de periodontograma y sondaje milimétrico'
            );

            return $nuevo->load('dientes');
        });

        return response()->json([
            'exito' => true,
            'mensaje' => 'Periodontograma guardado exitosamente.',
            'periodontograma' => $periodontograma,
        ], 201);
    }
}
