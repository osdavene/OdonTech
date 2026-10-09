<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RipsTransaccion extends Model
{
    use HasFactory;

    protected $table = 'rips_transacciones';

    protected $fillable = [
        'factura_id',
        'num_documento_id_obligado',
        'num_factura',
        'cufe',
        'tipo_nota',
        'num_nota',
        'json_rips_enviado',
        'json_sispro_respuesta',
        'cuv_sispro',
        'estado',
        'errores_validacion',
    ];

    protected function casts(): array
    {
        return [
            'errores_validacion' => 'array',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}
