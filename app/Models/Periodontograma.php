<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periodontograma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periodontogramas';

    protected $fillable = [
        'historia_clinica_id',
        'odontologo_id',
        'fecha_evaluacion',
        'indice_placa_porcentaje',
        'indice_sangrado_porcentaje',
        'diagnostico_periodontal',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evaluacion' => 'datetime',
            'indice_placa_porcentaje' => 'decimal:2',
            'indice_sangrado_porcentaje' => 'decimal:2',
        ];
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'odontologo_id');
    }

    public function dientes(): HasMany
    {
        return $this->hasMany(PeriodontogramaDiente::class, 'periodontograma_id')->orderBy('numero_diente_fdi');
    }
}
