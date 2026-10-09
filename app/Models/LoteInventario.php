<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteInventario extends Model
{
    use HasFactory;

    protected $table = 'lotes_inventario';

    protected $fillable = [
        'insumo_id',
        'lote_invima',
        'registro_sanitario_invima',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'cantidad_recibida',
        'cantidad_disponible',
        'costo_unitario',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_fabricacion' => 'date',
            'fecha_vencimiento' => 'date',
            'cantidad_recibida' => 'integer',
            'cantidad_disponible' => 'integer',
            'costo_unitario' => 'decimal:2',
        ];
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaInventario::class, 'lote_id');
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento->isPast();
    }

    public function estaProximoAVencer(int $dias = 30): bool
    {
        return !$this->estaVencido() && $this->fecha_vencimiento->diffInDays(Carbon::today()) <= $dias;
    }

    /**
     * Evalúa y actualiza automáticamente el estado del lote según la normativa INVIMA.
     */
    public function actualizarEstadoSanitario(): void
    {
        if ($this->cantidad_disponible <= 0) {
            $this->estado = 'AGOTADO';
        } elseif ($this->estaVencido()) {
            $this->estado = 'VENCIDO';
        } elseif ($this->estaProximoAVencer(30)) {
            $this->estado = 'PROXIMO_A_VENCER';
        } else {
            $this->estado = 'VIGENTE';
        }

        $this->save();
    }
}
