<?php

namespace App\Http\Controllers\Clinico;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinica;
use App\Models\Odontograma;
use App\Models\OdontogramaDiente;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdontogramaController extends Controller
{
    /**
     * Consulta el odontograma actual (o inicial) de la historia clínica.
     */
    public function obtenerPorHistoria(int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $odontograma = Odontograma::with('dientes.diagnosticoCie', 'dientes.procedimientoCups')
            ->where('historia_clinica_id', $historia->id)
            ->latest('fecha_registro')
            ->first();

        AuditoriaService::registrarLecturaHistoria($historia->id, $historia->paciente_id, 'Consulta visual del odontograma FDI');

        return response()->json([
            'exito' => true,
            'odontograma' => $odontograma,
            'tiene_odontograma' => !is_null($odontograma),
        ]);
    }

    /**
     * Guarda o actualiza el odontograma (tipo INICIAL o EVOLUCION) bajo nomenclatura FDI.
     */
    public function guardar(Request $request, int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $datos = $request->validate([
            'tipo_odontograma' => ['required', 'in:INICIAL,EVOLUCION'],
            'observaciones' => ['nullable', 'string'],
            'dientes' => ['required', 'array', 'min:1'],
            'dientes.*.numero_diente_fdi' => ['required', 'integer', 'between:11,85'],
            'dientes.*.estado_general_diente' => ['required', 'string'],
            'dientes.*.superficie_vestibular' => ['required', 'in:SANO,CARIES,OBTURACION_RESINA,OBTURACION_AMALGAMA,SELLANTE,FRACTURA'],
            'dientes.*.superficie_lingual_palatina' => ['required', 'in:SANO,CARIES,OBTURACION_RESINA,OBTURACION_AMALGAMA,SELLANTE,FRACTURA'],
            'dientes.*.superficie_mesial' => ['required', 'in:SANO,CARIES,OBTURACION_RESINA,OBTURACION_AMALGAMA,SELLANTE,FRACTURA'],
            'dientes.*.superficie_distal' => ['required', 'in:SANO,CARIES,OBTURACION_RESINA,OBTURACION_AMALGAMA,SELLANTE,FRACTURA'],
            'dientes.*.superficie_oclusal_incisal' => ['required', 'in:SANO,CARIES,OBTURACION_RESINA,OBTURACION_AMALGAMA,SELLANTE,FRACTURA'],
            'dientes.*.diagnostico_cie_id' => ['nullable', 'exists:catalogos_cie,id'],
            'dientes.*.procedimiento_propuesto_cups_id' => ['nullable', 'exists:catalogos_cups,id'],
            'dientes.*.observaciones' => ['nullable', 'string'],
        ]);

        $odontograma = DB::transaction(function () use ($datos, $historia) {
            $odontologoId = Auth::id() ?? 1;

            $nuevoOdontograma = Odontograma::create([
                'historia_clinica_id' => $historia->id,
                'odontologo_id' => $odontologoId,
                'tipo_odontograma' => $datos['tipo_odontograma'],
                'fecha_registro' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($datos['dientes'] as $dienteData) {
                OdontogramaDiente::create([
                    'odontograma_id' => $nuevoOdontograma->id,
                    'numero_diente_fdi' => $dienteData['numero_diente_fdi'],
                    'estado_general_diente' => $dienteData['estado_general_diente'],
                    'superficie_vestibular' => $dienteData['superficie_vestibular'],
                    'superficie_lingual_palatina' => $dienteData['superficie_lingual_palatina'],
                    'superficie_mesial' => $dienteData['superficie_mesial'],
                    'superficie_distal' => $dienteData['superficie_distal'],
                    'superficie_oclusal_incisal' => $dienteData['superficie_oclusal_incisal'],
                    'diagnostico_cie_id' => $dienteData['diagnostico_cie_id'] ?? null,
                    'procedimiento_propuesto_cups_id' => $dienteData['procedimiento_propuesto_cups_id'] ?? null,
                    'observaciones' => $dienteData['observaciones'] ?? null,
                ]);
            }

            AuditoriaService::registrar(
                tipoEvento: 'CREACION',
                tablaAfectada: 'odontogramas',
                registroId: $nuevoOdontograma->id,
                pacienteId: $historia->paciente_id,
                justificacion: "Guardado de odontograma FDI ({$datos['tipo_odontograma']})"
            );

            return $nuevoOdontograma->load('dientes');
        });

        return response()->json([
            'exito' => true,
            'mensaje' => 'Odontograma FDI guardado exitosamente.',
            'odontograma' => $odontograma,
        ], 201);
    }
}
