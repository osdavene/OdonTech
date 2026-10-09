<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaMedicamento extends Model
{
    use HasFactory;

    protected $table = 'formula_medicamentos';

    protected $fillable = [
        'formula_medica_id',
        'medicamento_nombre',
        'concentracion',
        'forma_farmaceutica',
        'via_administracion',
        'dosis',
        'duracion_tratamiento',
        'cantidad_total',
        'indicaciones_uso',
    ];

    public function formulaMedica(): BelongsTo
    {
        return $this->belongsTo(FormulaMedica::class, 'formula_medica_id');
    }
}
