<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Paciente;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Dian\DianPtaFacturacionService;
use App\Services\Rips\RipsJsonBuilderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FacturacionRipsController extends Controller
{
    /**
     * Emite una nueva factura de servicios de salud odontológicos.
     */
    public function crearFactura(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'historia_clinica_id' => ['nullable', 'exists:historias_clinicas,id'],
            'medio_pago_codigo' => ['required', 'string', 'in:10,48,49,47'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.cups_id' => ['required', 'exists:catalogos_cups,id'],
            'detalles.*.cie_id' => ['required', 'exists:catalogos_cie,id'],
            'detalles.*.cie_relacionado_id' => ['nullable', 'exists:catalogos_cie,id'],
            'detalles.*.tipo_servicio_rips' => ['required', 'in:01_CONSULTA,02_PROCEDIMIENTO'],
            'detalles.*.descripcion_concepto' => ['required', 'string', 'max:255'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:1'],
            'detalles.*.valor_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.valor_descuento' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.copago' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.cuota_moderadora' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.diente_fdi' => ['nullable', 'integer', 'between:11,85'],
            'detalles.*.finalidad_tecnologia_salud' => ['nullable', 'string'],
            'detalles.*.causa_motivo_atencion' => ['nullable', 'string'],
            'detalles.*.tipo_diagnostico_principal' => ['nullable', 'string'],
            'detalles.*.evolucion_id' => ['nullable', 'exists:evoluciones_clinicas,id'],
        ]);

        $paciente = Paciente::findOrFail($datos['paciente_id']);

        $factura = DB::transaction(function () use ($datos, $paciente) {
            $ultimoConsecutivo = Factura::max('consecutivo') ?? 1000;
            $consecutivo = $ultimoConsecutivo + 1;
            $prefijo = 'FEV';
            $numeroFactura = $prefijo . $consecutivo;

            $subtotal = 0;
            $descuentos = 0;
            $copagos = 0;
            $cuotasModeradoras = 0;

            foreach ($datos['detalles'] as $item) {
                $cant = (float) $item['cantidad'];
                $valUnit = (float) $item['valor_unitario'];
                $desc = (float) ($item['valor_descuento'] ?? 0);
                $copago = (float) ($item['copago'] ?? 0);
                $cuota = (float) ($item['cuota_moderadora'] ?? 0);

                $subtotal += ($cant * $valUnit);
                $descuentos += $desc;
                $copagos += $copago;
                $cuotasModeradoras += $cuota;
            }

            // Exclusión de IVA para servicios médicos/odontológicos humanos (Art. 476 ET)
            $iva = 0;
            $totalPagar = ($subtotal - $descuentos);

            $nuevaFactura = Factura::create([
                'paciente_id' => $paciente->id,
                'historia_clinica_id' => $datos['historia_clinica_id'] ?? null,
                'usuario_id' => Auth::id() ?? 1,
                'tipo_documento' => 'FACTURA_ELECTRONICA_VENTA',
                'prefijo' => $prefijo,
                'consecutivo' => $consecutivo,
                'numero_factura' => $numeroFactura,
                'resolucion_dian' => 'Resolución DIAN No. 18764000001 de 2026',
                'fecha_emision' => now(),
                'fecha_vencimiento' => now()->addDays(30),
                'medio_pago_codigo' => $datos['medio_pago_codigo'],
                'subtotal' => $subtotal,
                'descuento' => $descuentos,
                'iva' => $iva,
                'copago' => $copagos,
                'cuota_moderadora' => $cuotasModeradoras,
                'total_pagar' => $totalPagar,
                'estado_dian' => 'BORRADOR',
                'rips_estado' => 'PENDIENTE',
            ]);

            foreach ($datos['detalles'] as $item) {
                $cant = (float) $item['cantidad'];
                $valUnit = (float) $item['valor_unitario'];
                $desc = (float) ($item['valor_descuento'] ?? 0);
                $valTotal = ($cant * $valUnit) - $desc;

                FacturaDetalle::create([
                    'factura_id' => $nuevaFactura->id,
                    'cups_id' => $item['cups_id'],
                    'cie_id' => $item['cie_id'],
                    'cie_relacionado_id' => $item['cie_relacionado_id'] ?? null,
                    'tipo_servicio_rips' => $item['tipo_servicio_rips'],
                    'descripcion_concepto' => $item['descripcion_concepto'],
                    'cantidad' => $cant,
                    'valor_unitario' => $valUnit,
                    'valor_descuento' => $desc,
                    'valor_total' => $valTotal,
                    'copago' => (float) ($item['copago'] ?? 0),
                    'cuota_moderadora' => (float) ($item['cuota_moderadora'] ?? 0),
                    'diente_fdi' => $item['diente_fdi'] ?? null,
                    'finalidad_tecnologia_salud' => $item['finalidad_tecnologia_salud'] ?? '15',
                    'causa_motivo_atencion' => $item['causa_motivo_atencion'] ?? '38',
                    'tipo_diagnostico_principal' => $item['tipo_diagnostico_principal'] ?? '02',
                    'evolucion_id' => $item['evolucion_id'] ?? null,
                ]);
            }

            AuditoriaService::registrar(
                tipoEvento: 'CREACION',
                tablaAfectada: 'facturas',
                registroId: $nuevaFactura->id,
                pacienteId: $paciente->id,
                justificacion: "Elaboración de Factura Electrónica {$numeroFactura}"
            );

            return $nuevaFactura->load('detalles');
        });

        return response()->json([
            'exito' => true,
            'mensaje' => 'Factura creada en estado borrador. Lista para validación con el PTA de la DIAN.',
            'factura' => $factura,
        ], 201);
    }

    /**
     * Paso 1 de la integración legal: Envía la factura al Proveedor Tecnológico Autorizado (PTA)
     * y obtiene el CUFE y XML validado por la DIAN.
     */
    public function enviarDian(int $id, DianPtaFacturacionService $dianService): JsonResponse
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estaValidadaDian()) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'La factura ya se encuentra validada ante la DIAN.',
                'cufe' => $factura->cufe,
                'xml_url' => $factura->xml_url,
                'pdf_url' => $factura->pdf_url,
            ]);
        }

        $resultado = $dianService->transmitirFactura($factura);

        return response()->json([
            'exito' => $resultado['valido'],
            'mensaje' => $resultado['mensaje'],
            'cufe' => $resultado['cufe'] ?? null,
            'factura' => $factura->fresh(),
        ], $resultado['valido'] ? 200 : 422);
    }

    /**
     * Paso 2 de la integración legal: Genera el RIPS JSON oficial (Res. 2275/2023)
     * enlazando mandatoriamente el CUFE obtenido de la DIAN.
     */
    public function generarRips(int $id, RipsJsonBuilderService $ripsService): JsonResponse
    {
        $factura = Factura::findOrFail($id);

        try {
            $transaccion = $ripsService->procesarYGuardarRips($factura);

            return response()->json([
                'exito' => true,
                'mensaje' => 'RIPS JSON generado exitosamente con enlace obligatorio a CUFE.',
                'cufe_enlazado' => $transaccion->cufe,
                'num_factura' => $transaccion->num_factura,
                'payload_rips' => json_decode($transaccion->json_rips_enviado, true),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Descarga el archivo JSON oficial para entrega o auditoría ante SISPRO.
     */
    public function descargarJsonRips(int $id): Response
    {
        $factura = Factura::with('transaccionRips')->findOrFail($id);

        if (!$factura->transaccionRips) {
            return response()->json(['mensaje' => 'No se ha generado el archivo RIPS para esta factura.'], 404);
        }

        $nombreArchivo = "RIPS_{$factura->numero_factura}.json";

        return response($factura->transaccionRips->json_rips_enviado, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * Paso 3: Validación del RIPS ante SISPRO y recepción del CUV (Código Único de Validación).
     */
    public function validarSispro(int $id, RipsJsonBuilderService $ripsService): JsonResponse
    {
        $factura = Factura::findOrFail($id);

        try {
            $resultado = $ripsService->validarRipsEnSispro($factura);

            return response()->json([
                'exito' => true,
                'mensaje' => 'RIPS validado exitosamente en SISPRO.',
                'cuv' => $resultado['cuv'],
                'respuesta_sispro' => $resultado['respuesta'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }
}
