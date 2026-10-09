<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\AlertaInventario;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Inventario\ControlVencimientoInvimaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventarioInvimaController extends Controller
{
    /**
     * Lista los insumos y biomateriales con su stock actual y estado sanitario.
     */
    public function listarInsumos(): JsonResponse
    {
        $insumos = Insumo::with(['lotesDisponibles', 'alertas' => fn ($q) => $q->where('resuelta', false)])
            ->where('activo', true)
            ->get();

        return response()->json([
            'exito' => true,
            'insumos' => $insumos,
        ]);
    }

    /**
     * Registra un nuevo insumo o biomaterial odontológico en el catálogo.
     */
    public function crearInsumo(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'codigo_interno' => ['required', 'string', 'max:30', 'unique:insumos,codigo_interno'],
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'principio_activo_material' => ['required', 'string', 'max:150'],
            'presentacion' => ['required', 'string', 'max:100'],
            'unidad_medida' => ['required', 'in:UNIDAD,CAJA,TUBO,FRASCO,JERINGA,KIT,GRAMO,MILILITRO'],
            'categoria' => ['required', 'in:BIOMATERIAL_RESTAURADOR,ANESTESICO_FARMACO,ENDODONCIA,PERIODONCIA,ORTODONCIA,BIOSEGURIDAD_ESTERILIZACION,IMPLANTOLOGIA'],
            'stock_minimo' => ['required', 'integer', 'min:1'],
            'requiere_cadena_frio' => ['boolean'],
        ]);

        $insumo = Insumo::create($datos);

        AuditoriaService::registrar(
            tipoEvento: 'CREACION',
            tablaAfectada: 'insumos',
            registroId: $insumo->id,
            justificacion: "Alta de insumo odontológico '{$insumo->nombre_comercial}'"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Insumo registrado correctamente.',
            'insumo' => $insumo,
        ], 201);
    }

    /**
     * Da ingreso a un lote de insumos con Lote INVIMA, Registro Sanitario y Fecha de Vencimiento.
     */
    public function registrarLote(Request $request, int $insumoId): JsonResponse
    {
        $insumo = Insumo::findOrFail($insumoId);

        $datos = $request->validate([
            'lote_invima' => ['required', 'string', 'max:50'],
            'registro_sanitario_invima' => ['required', 'string', 'max:50'],
            'fecha_fabricacion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after:today'],
            'cantidad_recibida' => ['required', 'integer', 'min:1'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lote = LoteInventario::create([
            'insumo_id' => $insumo->id,
            'lote_invima' => $datos['lote_invima'],
            'registro_sanitario_invima' => $datos['registro_sanitario_invima'],
            'fecha_fabricacion' => $datos['fecha_fabricacion'] ?? null,
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'cantidad_recibida' => $datos['cantidad_recibida'],
            'cantidad_disponible' => $datos['cantidad_recibida'],
            'costo_unitario' => $datos['costo_unitario'] ?? null,
            'estado' => 'VIGENTE',
        ]);

        // Actualizar stock global del insumo
        $insumo->recalcularStock();

        AuditoriaService::registrar(
            tipoEvento: 'CREACION',
            tablaAfectada: 'lotes_inventario',
            registroId: $lote->id,
            justificacion: "Ingreso de lote INVIMA {$lote->lote_invima} para {$insumo->nombre_comercial}"
        );

        return response()->json([
            'exito' => true,
            'mensaje' => 'Lote registrado con trazabilidad INVIMA y fecha de caducidad validada.',
            'lote' => $lote,
        ], 201);
    }

    /**
     * Ejecuta la rutina sanitaria periódica de detección de vencimientos y quiebres de stock.
     */
    public function inspeccionarSanidad(ControlVencimientoInvimaService $invimaService): JsonResponse
    {
        $resumen = $invimaService->ejecutarInspeccionSanitaria();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Inspección de caducidad y normativa INVIMA ejecutada.',
            'resumen' => $resumen,
        ]);
    }

    /**
     * Consulta las alertas sanitarias no resueltas.
     */
    public function alertasPendientes(): JsonResponse
    {
        $alertas = AlertaInventario::with(['insumo', 'lote'])
            ->where('resuelta', false)
            ->orderByRaw("CASE nivel_severidad 
                WHEN 'CRITICA_BLOQUEANTE' THEN 1 
                WHEN 'ALTA' THEN 2 
                WHEN 'MEDIA' THEN 3 
                WHEN 'INFORMATIVA' THEN 4 
                ELSE 5 END")
            ->get();

        return response()->json([
            'exito' => true,
            'total_alertas' => $alertas->count(),
            'alertas' => $alertas,
        ]);
    }
}
