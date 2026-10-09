<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodontogramaDiente extends Model
{
    use HasFactory;

    protected $table = 'periodontograma_dientes';

    protected $fillable = [
        'periodontograma_id',
        'numero_diente_fdi',
        'profundidad_vestibular_distal',
        'profundidad_vestibular_medio',
        'profundidad_vestibular_mesial',
        'profundidad_lingual_distal',
        'profundidad_lingual_medio',
        'profundidad_lingual_mesial',
        'margen_vestibular',
        'margen_lingual',
        'sangrado_al_sondaje',
        'supuracion',
        'movilidad_dental',
        'lesion_furca',
    ];

    protected function casts(): array
    {
        return [
            'numero_diente_fdi' => 'integer',
            'sangrado_al_sondaje' => 'boolean',
            'supuracion' => 'boolean',
        ];
    }

    public function periodontograma(): BelongsTo
    {
        return $this->belongsTo(Periodontograma::class, 'periodontograma_id');
    }

    /**
     * Calcula la pérdida del nivel de inserción clínica (CAL) para un punto dado.
     * Nivel de inserción = Profundidad de sondaje + Margen gingival (recesión).
     */
    public function calcularNivelInsercion(int $profundidad, int $margen): int
    {
        return $profundidad + $margen;
    }
}
