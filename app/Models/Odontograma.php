<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odontograma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'odontogramas';

    protected $fillable = [
        'historia_clinica_id',
        'odontologo_id',
        'tipo_odontograma',
        'fecha_registro',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
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
        return $this->hasMany(OdontogramaDiente::class, 'odontograma_id')->orderBy('numero_diente_fdi');
    }
}
