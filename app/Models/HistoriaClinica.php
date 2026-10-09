<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'paciente_id',
        'odontologo_id',
        'numero_historia',
        'motivo_consulta',
        'enfermedad_actual',
        'antecedentes_medicos',
        'examen_estomatologico',
        'diagnostico_principal_cie_id',
        'diagnostico_relacionado_cie_id',
        'plan_tratamiento',
        'pronostico',
        'estado',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'antecedentes_medicos' => 'array',
            'examen_estomatologico' => 'array',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'odontologo_id');
    }

    public function diagnosticoPrincipal(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'diagnostico_principal_cie_id');
    }

    public function diagnosticoRelacionado(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'diagnostico_relacionado_cie_id');
    }

    public function evoluciones(): HasMany
    {
        return $this->hasMany(EvolucionClinica::class, 'historia_clinica_id')->orderBy('fecha_hora_atencion', 'asc');
    }

    public function odontogramas(): HasMany
    {
        return $this->hasMany(Odontograma::class, 'historia_clinica_id');
    }

    public function odontogramaInicial(): HasOne
    {
        return $this->hasOne(Odontograma::class, 'historia_clinica_id')->where('tipo_odontograma', 'INICIAL');
    }

    public function odontogramaUltimo(): HasOne
    {
        return $this->hasOne(Odontograma::class, 'historia_clinica_id')->latestOfMany('fecha_registro');
    }

    public function periodontogramas(): HasMany
    {
        return $this->hasMany(Periodontograma::class, 'historia_clinica_id')->orderBy('fecha_evaluacion', 'desc');
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(ConsentimientoInformado::class, 'historia_clinica_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'historia_clinica_id');
    }
}
