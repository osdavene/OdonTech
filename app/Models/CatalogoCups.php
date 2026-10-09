<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoCups extends Model
{
    use HasFactory;

    protected $table = 'catalogos_cups';

    protected $fillable = [
        'codigo',
        'descripcion',
        'seccion_odontologia',
        'tipo_servicio_rips',
        'tarifa_base_particular',
        'requiere_diente_fdi',
        'requiere_superficie_fdi',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'seccion_odontologia' => 'boolean',
            'requiere_diente_fdi' => 'boolean',
            'requiere_superficie_fdi' => 'boolean',
            'activo' => 'boolean',
            'tarifa_base_particular' => 'decimal:2',
        ];
    }

    public function evoluciones(): HasMany
    {
        return $this->hasMany(EvolucionClinica::class, 'cups_id');
    }

    public function facturaDetalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'cups_id');
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(ConsentimientoInformado::class, 'cups_id');
    }
}
