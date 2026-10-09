<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvolucionClinica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evoluciones_clinicas';

    protected $fillable = [
        'historia_clinica_id',
        'odontologo_id',
        'tipo_registro',
        'fecha_hora_atencion',
        'descripcion_subjetiva',
        'descripcion_objetiva',
        'analisis_diagnostico',
        'plan_procedimiento',
        'cups_id',
        'diente_fdi',
        'superficie_diente',
        'complicaciones',
        'recomendaciones',
        'proxima_cita_sugerida',
        'firma_digital_hash',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_atencion' => 'datetime',
            'proxima_cita_sugerida' => 'date',
            'diente_fdi' => 'integer',
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

    public function procedimientoCups(): BelongsTo
    {
        return $this->belongsTo(CatalogoCups::class, 'cups_id');
    }

    /**
     * Sellar digitalmente la nota médica/aclaratoria para blindar su inalterabilidad.
     */
    public static function generarSelloDigital(array $datos, string $tarjetaProfesional): string
    {
        $contenido = json_encode($datos) . '|' . $tarjetaProfesional . '|' . now()->toIso8601String();
        return hash('sha256', $contenido);
    }
}
