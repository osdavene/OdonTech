<?php

namespace App\Services\Auditoria;

use App\Models\AuditoriaAcceso;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    /**
     * Registra un evento inalterable de auditoría (Ley 1581 de 2012 y Res. 1995 de 1999).
     */
    public static function registrar(
        string $tipoEvento,
        string $tablaAfectada,
        int $registroId,
        ?int $pacienteId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $justificacion = null,
        ?Request $request = null
    ): AuditoriaAcceso {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();
        $request = $request ?? request();

        $usuarioId = $usuario?->id ?? 1;
        $rolNombre = $usuario?->rol?->nombre ?? 'Sistema/Automatizado';
        $ip = $request?->ip() ?? '127.0.0.1';
        $userAgent = $request?->userAgent() ?? 'CLI/Cron';

        // Obtener el último hash para generar la cadena inalterable
        $ultimoRegistro = AuditoriaAcceso::latest('id')->first();
        $hashPrevio = $ultimoRegistro?->hash_integridad;

        $payloadHash = [
            'usuario_id' => $usuarioId,
            'rol_usuario' => $rolNombre,
            'direccion_ip' => $ip,
            'tipo_evento' => $tipoEvento,
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'paciente_id' => $pacienteId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'marca_tiempo' => now()->toIso8601String(),
        ];

        $hashIntegridad = AuditoriaAcceso::generarHashIntegridad($payloadHash, $hashPrevio);

        return AuditoriaAcceso::create([
            'usuario_id' => $usuarioId,
            'rol_usuario' => $rolNombre,
            'direccion_ip' => $ip,
            'navegador_agente' => $userAgent,
            'tipo_evento' => $tipoEvento,
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'paciente_id' => $pacienteId,
            'justificacion_acceso' => $justificacion,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'hash_integridad' => $hashIntegridad,
        ]);
    }

    /**
     * Auditoría específica de lectura o consulta visual de la historia clínica (Habeas Data).
     */
    public static function registrarLecturaHistoria(int $historiaId, int $pacienteId, ?string $justificacion = null): AuditoriaAcceso
    {
        return self::registrar(
            tipoEvento: 'LECTURA_CONSULTA',
            tablaAfectada: 'historias_clinicas',
            registroId: $historiaId,
            pacienteId: $pacienteId,
            justificacion: $justificacion ?? 'Consulta clínica de expediente odontológico en pantalla'
        );
    }
}
