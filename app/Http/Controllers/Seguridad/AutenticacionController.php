<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaAcceso;
use App\Models\Usuario;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AutenticacionController extends Controller
{
    /**
     * Muestra la vista del formulario de inicio de sesión.
     */
    public function formularioLogin(): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Inicio de sesión con registro inalterable de auditoría (IP, Usuario, Rol, Timestamp).
     */
    public function ingresar(Request $request): \Illuminate\Http\RedirectResponse|JsonResponse
    {
        $credenciales = $request->validate([
            'correo_electronico' => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $usuario = Usuario::with('rol')
            ->where('correo_electronico', $credenciales['correo_electronico'])
            ->where('activo', true)
            ->first();

        if (!$usuario || !Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
            AuditoriaService::registrar(
                tipoEvento: 'ACCESO_FALLIDO',
                tablaAfectada: 'usuarios',
                registroId: $usuario?->id ?? 0,
                justificacion: "Intento fallido de inicio de sesión para {$credenciales['correo_electronico']}",
                request: $request
            );

            if ($request->wantsJson()) {
                throw ValidationException::withMessages([
                    'correo_electronico' => ['Las credenciales suministradas no coinciden con nuestros registros.'],
                ]);
            }

            return back()->withErrors([
                'correo_electronico' => 'Las credenciales suministradas no coinciden con nuestros registros o el usuario está inactivo.',
            ])->withInput($request->only('correo_electronico'));
        }

        Auth::login($usuario, $request->boolean('recordar', false));
        $request->session()->regenerate();

        AuditoriaService::registrar(
            tipoEvento: 'INICIO_SESION',
            tablaAfectada: 'usuarios',
            registroId: $usuario->id,
            justificacion: "Acceso exitoso al sistema como {$usuario->rol->nombre}",
            request: $request
        );

        if ($request->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => "Bienvenido(a) {$usuario->nombre_completo}",
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'rol' => $usuario->rol->nombre,
                    'registro_profesional' => $usuario->registro_profesional,
                ],
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Cierre de sesión y descarte de token/sesión.
     */
    public function salir(Request $request): \Illuminate\Http\RedirectResponse|JsonResponse
    {
        $usuario = Auth::user();

        if ($usuario) {
            AuditoriaService::registrar(
                tipoEvento: 'CIERRE_SESION',
                tablaAfectada: 'usuarios',
                registroId: $usuario->id,
                justificacion: 'Cierre regular de sesión',
                request: $request
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'Sesión cerrada correctamente.',
            ]);
        }

        return redirect()->route('login')->with('mensaje', 'Sesión cerrada correctamente.');
    }

    /**
     * Consulta el perfil y rol del usuario activo.
     */
    public function usuarioActual(): JsonResponse
    {
        $usuario = Auth::user();

        return response()->json([
            'exito' => true,
            'usuario' => $usuario ? [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre_completo,
                'rol' => $usuario->rol?->nombre,
                'tarjeta_profesional' => $usuario->registro_profesional,
                'especialidad' => $usuario->especialidad,
            ] : null,
        ]);
    }
}
