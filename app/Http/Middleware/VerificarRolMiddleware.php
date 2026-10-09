<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarRolMiddleware
{
    /**
     * Valida que el usuario autenticado posea uno de los roles autorizados.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$rolesPermitidos
     */
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        if (! Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'No autenticado. Inicie sesión para continuar.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $usuario = Auth::user();
        $rolUsuarioSlug = strtolower($usuario->rol?->slug ?? '');
        $rolUsuarioNombre = strtolower($usuario->rol?->nombre ?? '');

        // Normalizar los roles permitidos
        $rolesNormalizados = array_map(fn ($r) => strtolower(trim($r)), $rolesPermitidos);

        if (! in_array($rolUsuarioSlug, $rolesNormalizados, true) && ! in_array($rolUsuarioNombre, $rolesNormalizados, true)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Acceso denegado. Su rol no tiene permisos para esta acción clínica o administrativa.',
                    'rol_actual' => $usuario->rol?->nombre,
                    'roles_requeridos' => $rolesPermitidos,
                ], 403);
            }

            abort(403, 'Acceso restringido por marco de seguridad sanitaria. Rol no autorizado para este módulo.');
        }

        return $next($request);
    }
}
