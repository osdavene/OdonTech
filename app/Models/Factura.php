<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'paciente_id',
        'historia_clinica_id',
        'usuario_id',
        'tipo_documento',
        'prefijo',
        'consecutivo',
        'numero_factura',
        'resolucion_dian',
        'fecha_emision',
        'fecha_vencimiento',
        'medio_pago_codigo',
        'subtotal',
        'descuento',
        'iva',
        'copago',
        'cuota_moderadora',
        'total_pagar',
        'cufe',
        'qr_cadena',
        'xml_url',
        'pdf_url',
        'estado_dian',
        'dian_respuesta_codigo',
        'dian_respuesta_mensaje',
        'dian_fecha_validacion',
        'rips_estado',
        'rips_cuv',
        'rips_fecha_generacion',
        'rips_fecha_validacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'fecha_vencimiento' => 'date',
            'dian_fecha_validacion' => 'datetime',
            'rips_fecha_generacion' => 'datetime',
            'rips_fecha_validacion' => 'datetime',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'iva' => 'decimal:2',
            'copago' => 'decimal:2',
            'cuota_moderadora' => 'decimal:2',
            'total_pagar' => 'decimal:2',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function usuarioEmisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'factura_id');
    }

    public function transaccionRips(): HasOne
    {
        return $this->hasOne(RipsTransaccion::class, 'factura_id');
    }

    public function estaValidadaDian(): bool
    {
        return $this->estado_dian === 'VALIDADA_DIAN' && filled($this->cufe);
    }

    public function tieneRipsCuv(): bool
    {
        return $this->rips_estado === 'VALIDADO_CUV' && filled($this->rips_cuv);
    }
}
