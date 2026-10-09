<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Services\Auditoria\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    /**
     * Lista las citas de un día específico o del día en curso, organizadas cronológicamente.
     */
    public function listarPorFecha(Request $request): \Illuminate\Http\Response|JsonResponse|\Illuminate\View\View
    {
        $fecha = $request->query('fecha', Carbon::today()->format('Y-m-d'));
        $odontologoId = $request->query('odontologo_id');

        $query = Cita::with(['paciente', 'odontologo', 'cupsPropuesto'])
            ->paraFecha($fecha)
            ->orderBy('fecha_hora_inicio', 'asc');

        if ($odontologoId) {
            $query->delOdontologo((int) $odontologoId);
        }

        $citas = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'exito' => true,
                'fecha' => $fecha,
                'total_citas' => $citas->count(),
                'citas' => $citas,
            ]);
        }

        return view('agenda.index', [
            'fecha' => $fecha,
            'citas' => $citas,
        ]);
    }

    /**
     * Agenda una nueva cita odontológica validando disponibilidad de sillón y profesional.
     */
    public function agendarCita(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'odontologo_id' => ['required', 'exists:usuarios,id'],
            'fecha_hora_inicio' => ['required', 'date', 'after:yesterday'],
            'duracion_minutos' => ['required', 'integer', 'min:15', 'max:180'],
            'unidad_sillon_numero' => ['required', 'integer', 'min:1', 'max:10'],
            'motivo_consulta' => ['required', 'string', 'max:200'],
            'cups_propuesto_id' => ['nullable', 'exists:catalogos_cups,id'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $inicio = Carbon::parse($datos['fecha_hora_inicio']);
        $fin = (clone $inicio)->addMinutes($datos['duracion_minutos']);

        // Validación de no traslape en el mismo sillón dental
        $conflictoSillon = Cita::where('unidad_sillon_numero', $datos['unidad_sillon_numero'])
            ->whereNotIn('estado', ['CANCELADA', 'INASISTENCIA'])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_hora_inicio', [$inicio, $fin])
                  ->orWhereBetween('fecha_hora_fin', [$inicio, $fin])
                  ->orWhere(fn ($sub) => $sub->where('fecha_hora_inicio', '<=', $inicio)->where('fecha_hora_fin', '>=', $fin));
            })->exists();

        if ($conflictoSillon) {
            return response()->json([
                'exito' => false,
                'mensaje' => "El sillón dental No. {$datos['unidad_sillon_numero']} ya se encuentra ocupado en ese horario."
            ], 422);
        }

        $cita = Cita::create([
            'paciente_id' => $datos['paciente_id'],
            'odontologo_id' => $datos['odontologo_id'],
            'usuario_creador_id' => Auth::id() ?? 1,
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'unidad_sillon_numero' => $datos['unidad_sillon_numero'],
            'motivo_consulta' => $datos['motivo_consulta'],
            'cups_propuesto_id' => $datos['cups_propuesto_id'] ?? null,
            'estado' => 'AGENDADA',
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        AuditoriaService::registrar(
            tipoEvento: 'CREACION',
            tablaAfectada: 'citas_odontologicas',
            registroId: $cita->id,
            pacienteId: $datos['paciente_id'],
            justificacion: "Agendamiento de cita odontológica para {$inicio->format('d/m/Y H:i')}"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cita odontológica agendada con éxito.',
            'cita' => $cita->load(['paciente', 'odontologo']),
        ], 201);
    }

    /**
     * Actualiza el estado de la cita (ej. PACIENTE EN SALA DE ESPERA, CONFIRMADA, CANCELADA).
     */
    public function actualizarEstado(Request $request, int $id): JsonResponse
    {
        $cita = Cita::findOrFail($id);

        $datos = $request->validate([
            'estado' => ['required', 'in:CONFIRMADA,EN_SALA_ESPERA,EN_ATENCION,ATENDIDA,CANCELADA,INASISTENCIA'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $cita->update([
            'estado' => $datos['estado'],
            'observaciones' => $datos['observaciones'] ?? $cita->observaciones,
        ]);

        return response()->json([
            'exito' => true,
            'mensaje' => "Estado de la cita actualizado a: {$datos['estado']}",
            'cita' => $cita,
        ]);
    }

    /**
     * Vinculación directa: Pasa la cita a 'EN_ATENCION' y conecta en 1 clic
     * con la historia clínica activa del paciente.
     */
    public function iniciarAtencionClinica(int $id): JsonResponse
    {
        $cita = Cita::with('paciente')->findOrFail($id);

        $cita->update(['estado' => 'EN_ATENCION']);

        // Obtener o referenciar la historia clínica del paciente
        $historia = HistoriaClinica::where('paciente_id', $cita->paciente_id)
            ->whereIn('estado', ['ABIERTA', 'EN_TRATAMIENTO'])
            ->latest()
            ->first();

        if ($historia) {
            $cita->update(['historia_clinica_id' => $historia->id]);
        }

        AuditoriaService::registrar(
            tipoEvento: 'MODIFICACION',
            tablaAfectada: 'citas_odontologicas',
            registroId: $cita->id,
            pacienteId: $cita->paciente_id,
            justificacion: "Inicio de atención odontológica en sillón {$cita->unidad_sillon_numero}"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Atención clínica iniciada.',
            'cita' => $cita,
            'historia_clinica_id' => $historia?->id,
            'tiene_historia' => !is_null($historia),
        ]);
    }
}
