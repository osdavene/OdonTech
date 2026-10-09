<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insumos';

    protected $fillable = [
        'codigo_interno',
        'nombre_comercial',
        'principio_activo_material',
        'presentacion',
        'unidad_medida',
        'categoria',
        'stock_minimo',
        'stock_actual',
        'requiere_cadena_frio',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'stock_minimo' => 'integer',
            'stock_actual' => 'integer',
            'requiere_cadena_frio' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class, 'insumo_id');
    }

    public function lotesDisponibles(): HasMany
    {
        return $this->hasMany(LoteInventario::class, 'insumo_id')
            ->whereIn('estado', ['VIGENTE', 'PROXIMO_A_VENCER'])
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc'); // FEFO: First Expired, First Out
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'insumo_id')->latest();
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaInventario::class, 'insumo_id');
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function recalcularStock(): void
    {
        $this->stock_actual = (int) $this->lotes()
            ->whereIn('estado', ['VIGENTE', 'PROXIMO_A_VENCER'])
            ->sum('cantidad_disponible');
        $this->save();
    }
}
