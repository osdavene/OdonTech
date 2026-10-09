<?php

namespace App\Http\Middleware;

use App\Services\Auditoria\AuditoriaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditoriaHabeasDataMiddleware
{
    /**
     * Registra automáticamente en la tabla de auditoría inalterable
     * la lectura o modificación de expedientes clínicos y pacientes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si la petición fue exitosa y está relacionada con pacientes o historias clínicas
        if ($response->isSuccessful() && $request->user()) {
            $pacienteId = $request->route('paciente')?->id ?? $request->input('paciente_id');
            $historiaId = $request->route('historia')?->id ?? $request->input('historia_clinica_id');

            $metodo = $request->method();
            $tipoEvento = match ($metodo) {
                'GET' => 'LECTURA_CONSULTA',
                'POST' => 'CREACION',
                'PUT', 'PATCH' => 'MODIFICACION',
                'DELETE' => 'ELIMINACION_LOGICA',
                default => 'ACCESO_GENERAL'
            };

            if ($pacienteId || $historiaId) {
                AuditoriaService::registrar(
                    tipoEvento: $tipoEvento,
                    tablaAfectada: $historiaId ? 'historias_clinicas' : 'pacientes',
                    registroId: (int) ($historiaId ?? $pacienteId),
                    pacienteId: $pacienteId ? (int) $pacienteId : null,
                    justificacion: 'Acceso regulado a expediente médico digital bajo Ley 1581 de 2012',
                    request: $request
                );
            }
        }

        return $response;
    }
}
