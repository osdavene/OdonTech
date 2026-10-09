<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramaDiente extends Model
{
    use HasFactory;

    protected $table = 'odontograma_dientes';

    protected $fillable = [
        'odontograma_id',
        'numero_diente_fdi',
        'estado_general_diente',
        'superficie_vestibular',
        'superficie_lingual_palatina',
        'superficie_mesial',
        'superficie_distal',
        'superficie_oclusal_incisal',
        'diagnostico_cie_id',
        'procedimiento_propuesto_cups_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'numero_diente_fdi' => 'integer',
        ];
    }

    public function odontograma(): BelongsTo
    {
        return $this->belongsTo(Odontograma::class, 'odontograma_id');
    }

    public function diagnosticoCie(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'diagnostico_cie_id');
    }

    public function procedimientoCups(): BelongsTo
    {
        return $this->belongsTo(CatalogoCups::class, 'procedimiento_propuesto_cups_id');
    }

    /**
     * Determina si el diente corresponde a dentición temporal (decidua) o permanente según FDI.
     */
    public function esDenticionTemporal(): bool
    {
        $cuadrante = intdiv($this->numero_diente_fdi, 10);
        return in_array($cuadrante, [5, 6, 7, 8]);
    }
}
