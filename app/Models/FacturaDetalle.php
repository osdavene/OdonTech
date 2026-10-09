<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    use HasFactory;

    protected $table = 'factura_detalles';

    protected $fillable = [
        'factura_id',
        'cups_id',
        'cie_id',
        'cie_relacionado_id',
        'tipo_servicio_rips',
        'descripcion_concepto',
        'cantidad',
        'valor_unitario',
        'valor_descuento',
        'valor_total',
        'copago',
        'cuota_moderadora',
        'diente_fdi',
        'finalidad_tecnologia_salud',
        'causa_motivo_atencion',
        'tipo_diagnostico_principal',
        'evolucion_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'valor_unitario' => 'decimal:2',
            'valor_descuento' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'copago' => 'decimal:2',
            'cuota_moderadora' => 'decimal:2',
            'diente_fdi' => 'integer',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function cups(): BelongsTo
    {
        return $this->belongsTo(CatalogoCups::class, 'cups_id');
    }

    public function diagnosticoCie(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'cie_id');
    }

    public function diagnosticoRelacionadoCie(): BelongsTo
    {
        return $this->belongsTo(CatalogoCie::class, 'cie_relacionado_id');
    }

    public function evolucion(): BelongsTo
    {
        return $this->belongsTo(EvolucionClinica::class, 'evolucion_id');
    }
}
