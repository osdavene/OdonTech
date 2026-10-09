<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormulaMedica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formulas_medicas';

    protected $fillable = [
        'historia_clinica_id',
        'paciente_id',
        'odontologo_id',
        'diagnostico_cie_id',
        'fecha_emision',
        'vigencia_dias',
        'recomendaciones_generales',
        'firma_digital_hash',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'vigencia_dias' => 'integer',
        ];
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'odontologo_id');
    }

    public function diagnosticoCie(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'diagnostico_cie_id');
    }

    public function medicamentos(): HasMany
    {
        return $this->hasMany(FormulaMedicamento::class, 'formula_medica_id');
    }

    /**
     * Sellar digitalmente la prescripción con el ReTHUS del profesional.
     */
    public static function generarSelloPrescripcion(array $medicamentos, string $rethus): string
    {
        return hash('sha256', json_encode($medicamentos) . '|' . $rethus . '|' . now()->toIso8601String());
    }
}
