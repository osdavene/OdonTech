<?php

namespace App\Http\Controllers\Clinico;

use App\Http\Controllers\Controller;
use App\Models\ConsentimientoInformado;
use App\Models\HistoriaClinica;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentimientoController extends Controller
{
    /**
     * Registra un nuevo documento de consentimiento informado previo al procedimiento odontológico.
     */
    public function crear(Request $request, int $historiaId): JsonResponse
    {
        $historia = HistoriaClinica::with('paciente')->findOrFail($historiaId);

        $datos = $request->validate([
            'cups_id' => ['nullable', 'exists:catalogos_cups,id'],
            'titulo_procedimiento' => ['required', 'string', 'max:200'],
            'cuerpo_consentimiento' => ['required', 'string'],
            'declaracion_aceptacion' => ['required', 'string'],
            'nombre_firmante' => ['required', 'string', 'max:120'],
            'documento_firmante' => ['required', 'string', 'max:20'],
            'calidad_firmante' => ['required', 'in:PACIENTE,REPRESENTANTE_LEGAL,TUTOR,FAMILIAR_RESPONSABLE'],
        ]);

        $consentimiento = ConsentimientoInformado::create([
            'historia_clinica_id' => $historia->id,
            'paciente_id' => $historia->paciente_id,
            'odontologo_id' => Auth::id() ?? 1,
            'cups_id' => $datos['cups_id'] ?? null,
            'titulo_procedimiento' => $datos['titulo_procedimiento'],
            'cuerpo_consentimiento' => $datos['cuerpo_consentimiento'],
            'declaracion_aceptacion' => $datos['declaracion_aceptacion'],
            'nombre_firmante' => $datos['nombre_firmante'],
            'documento_firmante' => $datos['documento_firmante'],
            'calidad_firmante' => $datos['calidad_firmante'],
            'estado' => 'PENDIENTE',
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'CREACION',
            tablaAfectada: 'consentimientos_informados',
            registroId: $consentimiento->id,
            pacienteId: $historia->paciente_id,
            justificacion: "Generación de consentimiento informado para {$datos['titulo_procedimiento']}"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Consentimiento informado redactado. Pendiente de firma del paciente.',
            'consentimiento' => $consentimiento,
        ], 201);
    }

    /**
     * Almacena la firma digital o biométrica capturada en pantalla/dispositivo.
     */
    public function firmar(Request $request, int $id): JsonResponse
    {
        $consentimiento = ConsentimientoInformado::findOrFail($id);

        $datos = $request->validate([
            'firma_digital_base64' => ['required', 'string'],
            'huella_biometrica_hash' => ['nullable', 'string'],
        ]);

        $consentimiento->update([
            'firma_digital_base64' => $datos['firma_digital_base64'],
            'huella_biometrica_hash' => $datos['huella_biometrica_hash'] ?? null,
            'estado' => 'FIRMADO',
            'fecha_hora_firma' => now(),
            'ip_firmante' => $request->ip(),
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'FIRMA_LEGAL',
            tablaAfectada: 'consentimientos_informados',
            registroId: $consentimiento->id,
            pacienteId: $consentimiento->paciente_id,
            justificacion: 'Firma y perfeccionamiento legal del consentimiento informado',
            request: $request
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Consentimiento firmado satisfactoriamente.',
            'consentimiento' => $consentimiento,
        ]);
    }

    /**
     * Revoca un consentimiento previamente otorgado (derecho del paciente bajo ley colombiana).
     */
    public function revocar(Request $request, int $id): JsonResponse
    {
        $consentimiento = ConsentimientoInformado::findOrFail($id);

        $datos = $request->validate([
            'motivo_revocacion' => ['required', 'string', 'min:10'],
        ]);

        $consentimiento->update([
            'estado' => 'REVOCADO',
            'motivo_revocacion' => $datos['motivo_revocacion'],
            'fecha_hora_revocacion' => now(),
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'REVOCACION',
            tablaAfectada: 'consentimientos_informados',
            registroId: $consentimiento->id,
            pacienteId: $consentimiento->paciente_id,
            justificacion: "Revocatoria de consentimiento informado por parte del paciente: {$datos['motivo_revocacion']}",
            request: $request
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Consentimiento informado revocado conforme a la voluntad del paciente.',
            'consentimiento' => $consentimiento,
        ]);
    }
}
