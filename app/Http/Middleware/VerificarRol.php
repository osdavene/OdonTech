<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Valida que el usuario autenticado posea uno de los roles autorizados.
     */
    public function handle(Request $request, Closure $next, ...$rolesPermitidos): Response
    {
        $usuario = $request->user();

        if (!$usuario || !in_array($usuario->rol?->nombre, $rolesPermitidos)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Acceso denegado: Su rol (' . ($usuario?->rol?->nombre ?? 'Sin Rol') . ') no tiene autorización para este recurso clínico.'
                ], 403);
            }

            abort(403, 'Acceso denegado: Rol no autorizado.');
        }

        return $next($request);
    }
}
