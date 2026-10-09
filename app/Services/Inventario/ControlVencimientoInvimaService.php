<?php

namespace App\Services\Inventario;

use App\Models\AlertaInventario;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ControlVencimientoInvimaService
{
    /**
     * Escanea sistemáticamente los lotes e insumos según normativa INVIMA y dispara alertas preventivas.
     */
    public function ejecutarInspeccionSanitaria(): array
    {
        $hoy = Carbon::today();
        $lotes = LoteInventario::with('insumo')->whereIn('estado', ['VIGENTE', 'PROXIMO_A_VENCER'])->get();

        $vencidosCount = 0;
        $proximosCount = 0;

        foreach ($lotes as $lote) {
            $fechaVencimiento = Carbon::parse($lote->fecha_vencimiento);

            if ($fechaVencimiento->isPast()) {
                // Bloqueo preventivo inmediato: prohibido su uso en pacientes
                $lote->estado = 'VENCIDO';
                $lote->save();

                AlertaInventario::firstOrCreate(
                    [
                        'insumo_id' => $lote->insumo_id,
                        'lote_id' => $lote->id,
                        'tipo_alerta' => 'LOTE_VENCIDO_BLOQUEADO',
                    ],
                    [
                        'mensaje_alerta' => "ATENCIÓN SANITARIA: El lote '{$lote->lote_invima}' del insumo '{$lote->insumo->nombre_comercial}' venció el {$fechaVencimiento->format('d/m/Y')}. Ha sido bloqueado para impedir su aplicación en pacientes.",
                        'nivel_severidad' => 'CRITICA_BLOQUEANTE',
                        'resuelta' => false,
                    ]
                );

                $vencidosCount++;
            } elseif ($fechaVencimiento->diffInDays($hoy) <= 30) {
                // Alerta preventiva: vence en menos de 30 días
                $lote->estado = 'PROXIMO_A_VENCER';
                $lote->save();

                $diasRestantes = (int) $fechaVencimiento->diffInDays($hoy);

                AlertaInventario::firstOrCreate(
                    [
                        'insumo_id' => $lote->insumo_id,
                        'lote_id' => $lote->id,
                        'tipo_alerta' => 'LOTE_PROXIMO_A_VENCER_30_DIAS',
                    ],
                    [
                        'mensaje_alerta' => "ALERTA PREVENTIVA: El lote '{$lote->lote_invima}' de '{$lote->insumo->nombre_comercial}' vence en {$diasRestantes} días ({$fechaVencimiento->format('d/m/Y')}). Consumir prioritariamente bajo técnica FEFO.",
                        'nivel_severidad' => 'ALTA',
                        'resuelta' => false,
                    ]
                );

                $proximosCount++;
            }
        }

        // Inspección de stock mínimo
        $insumos = Insumo::where('activo', true)->get();
        $stockBajoCount = 0;

        foreach ($insumos as $insumo) {
            $insumo->recalcularStock();

            if ($insumo->tieneStockBajo()) {
                AlertaInventario::firstOrCreate(
                    [
                        'insumo_id' => $insumo->id,
                        'lote_id' => null,
                        'tipo_alerta' => 'STOCK_MINIMO_CRITICO',
                    ],
                    [
                        'mensaje_alerta' => "QUIEBRE DE STOCK: El insumo '{$insumo->nombre_comercial}' cuenta con {$insumo->stock_actual} unidades disponibles (Stock Mínimo: {$insumo->stock_minimo}).",
                        'nivel_severidad' => 'MEDIA',
                        'resuelta' => false,
                    ]
                );
                $stockBajoCount++;
            }
        }

        return [
            'lotes_bloqueados_vencidos' => $vencidosCount,
            'lotes_proximos_a_vencer' => $proximosCount,
            'insumos_stock_minimo' => $stockBajoCount,
        ];
    }

    /**
     * Valida de manera estricta que un lote no se encuentre caducado antes de aplicarlo a un paciente.
     */
    public function validarLoteParaUsoClinico(LoteInventario $lote): void
    {
        if ($lote->estado === 'VENCIDO' || Carbon::parse($lote->fecha_vencimiento)->isPast()) {
            throw new DomainException(
                "VIOLACIÓN DE BIOSEGURIDAD: El lote '{$lote->lote_invima}' de '{$lote->insumo->nombre_comercial}' se encuentra VENCIDO desde el {$lote->fecha_vencimiento->format('d/m/Y')}. No se permite su uso en atenciones clínicas bajo normativa INVIMA."
            );
        }

        if ($lote->cantidad_disponible <= 0) {
            throw new DomainException("El lote '{$lote->lote_invima}' no cuenta con existencias disponibles.");
        }
    }

    /**
     * Registra la salida de un insumo/lote utilizado en una atención médica odontológica,
     * garantizando la trazabilidad de tecnovigilancia paciente-lote.
     */
    public function registrarUsoEnPaciente(
        int $loteId,
        int $historiaClinicaId,
        int $cantidad,
        int $usuarioId,
        string $motivo = 'Uso clínico en tratamiento odontológico'
    ): MovimientoInventario {
        return DB::transaction(function () use ($loteId, $historiaClinicaId, $cantidad, $usuarioId, $motivo) {
            $lote = LoteInventario::lockForUpdate()->findOrFail($loteId);

            // Validar restricción sanitaria antes de despachar
            $this->validarLoteParaUsoClinico($lote);

            if ($lote->cantidad_disponible < $cantidad) {
                throw new DomainException("Stock insuficiente en el lote '{$lote->lote_invima}'. Disponible: {$lote->cantidad_disponible}, Solicitado: {$cantidad}.");
            }

            $saldoAnterior = $lote->cantidad_disponible;
            $saldoPosterior = $saldoAnterior - $cantidad;

            $lote->cantidad_disponible = $saldoPosterior;
            if ($saldoPosterior === 0) {
                $lote->estado = 'AGOTADO';
            }
            $lote->save();

            $movimiento = MovimientoInventario::create([
                'insumo_id' => $lote->insumo_id,
                'lote_id' => $lote->id,
                'usuario_id' => $usuarioId,
                'historia_clinica_id' => $historiaClinicaId,
                'tipo_movimiento' => 'SALIDA_ATENCION_CLINICA',
                'cantidad' => $cantidad,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'motivo_observaciones' => $motivo . " (Lote INVIMA: {$lote->lote_invima}, Reg. Sanitario: {$lote->registro_sanitario_invima})",
            ]);

            $lote->insumo->recalcularStock();

            return $movimiento;
        });
    }
}
