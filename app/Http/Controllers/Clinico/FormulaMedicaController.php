<?php

namespace App\Http\Controllers\Clinico;

use App\Http\Controllers\Controller;
use App\Models\FormulaMedica;
use App\Models\FormulaMedicamento;
use App\Models\HistoriaClinica;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FormulaMedicaController extends Controller
{
    /**
     * Emite una fórmula médica odontológica con sello digital del profesional ReTHUS.
     */
    public function crear(Request $request, int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::findOrFail($historiaId);

        $datos = $request->validate([
            'diagnostico_cie_id' => ['nullable', 'exists:catalogos_cie,id'],
            'vigencia_dias' => ['nullable', 'integer', 'min:1', 'max:90'],
            'recomendaciones_generales' => ['nullable', 'string'],
            'medicamentos' => ['required', 'array', 'min:1'],
            'medicamentos.*.medicamento_nombre' => ['required', 'string', 'max:150'],
            'medicamentos.*.concentracion' => ['required', 'string', 'max:50'],
            'medicamentos.*.forma_farmaceutica' => ['required', 'string', 'max:50'],
            'medicamentos.*.via_administracion' => ['nullable', 'string', 'max:30'],
            'medicamentos.*.dosis' => ['required', 'string', 'max:80'],
            'medicamentos.*.duracion_tratamiento' => ['required', 'string', 'max:50'],
            'medicamentos.*.cantidad_total' => ['required', 'string', 'max:30'],
            'medicamentos.*.indicaciones_uso' => ['nullable', 'string'],
        ]);

        $usuario = Auth::user();
        $odontologoId = $usuario?->id ?? 1;
        $rethus = $usuario?->registro_profesional ?? 'RETHUS-PROV';

        $firmaHash = FormulaMedica::generarSelloPrescripcion($datos['medicamentos'], $rethus);

        $formula = DB::transaction(function () use ($datos, $historia, $odontologoId, $firmaHash) {
            $nuevaFormula = FormulaMedica::create([
                'historia_clinica_id' => $historia->id,
                'paciente_id' => $historia->paciente_id,
                'odontologo_id' => $odontologoId,
                'diagnostico_cie_id' => $datos['diagnostico_cie_id'] ?? $historia->diagnostico_principal_cie_id,
                'fecha_emision' => now(),
                'vigencia_dias' => $datos['vigencia_dias'] ?? 30,
                'recomendaciones_generales' => $datos['recomendaciones_generales'] ?? null,
                'firma_digital_hash' => $firmaHash,
            ]);

            foreach ($datos['medicamentos'] as $med) {
                FormulaMedicamento::create([
                    'formula_medica_id' => $nuevaFormula->id,
                    'medicamento_nombre' => $med['medicamento_nombre'],
                    'concentracion' => $med['concentracion'],
                    'forma_farmaceutica' => $med['forma_farmaceutica'],
                    'via_administracion' => $med['via_administracion'] ?? 'Oral',
                    'dosis' => $med['dosis'],
                    'duracion_tratamiento' => $med['duracion_tratamiento'],
                    'cantidad_total' => $med['cantidad_total'],
                    'indicaciones_uso' => $med['indicaciones_uso'] ?? null,
                ]);
            }

            AuditoriaService::registrar(
                tipoEvento: 'CREACION',
                tablaAfectada: 'formulas_medicas',
                registroId: $nuevaFormula->id,
                pacienteId: $historia->paciente_id,
                justificacion: 'Emisión de fórmula médica odontológica con sello ReTHUS'
            );

            return $nuevaFormula->load('medicamentos');
        });

        return response()->json([
            'exito' => true,
            'mensaje' => 'Fórmula médica prescrita exitosamente.',
            'formula' => $formula,
        ], 201);
    }

    /**
     * Renderiza la fórmula médica para impresión oficial con datos del profesional y paciente.
     */
    public function imprimir(int $id): Response
    {
        $formula = FormulaMedica::with(['paciente', 'odontologo', 'diagnosticoCie', 'medicamentos'])
            ->findOrFail($id);

        return response()->view('clinico.formula_imprimible', ['formula' => $formula]);
    }
}
