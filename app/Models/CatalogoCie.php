<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoCie extends Model
{
    use HasFactory;

    protected $table = 'catalogos_cie';

    protected $fillable = [
        'codigo',
        'version',
        'nombre',
        'descripcion',
        'es_odontologico',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'es_odontologico' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function historiasPrincipales(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'diagnostico_principal_cie_id');
    }

    public function facturaDetalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'cie_id');
    }
}
