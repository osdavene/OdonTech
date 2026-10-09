<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaInventario extends Model
{
    use HasFactory;

    protected $table = 'alertas_inventario';

    protected $fillable = [
        'insumo_id',
        'lote_id',
        'tipo_alerta',
        'mensaje_alerta',
        'nivel_severidad',
        'leida',
        'resuelta',
        'usuario_resolucion_id',
        'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'resuelta' => 'boolean',
            'fecha_resolucion' => 'datetime',
        ];
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteInventario::class, 'lote_id');
    }

    public function usuarioResolucion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_resolucion_id');
    }
}
