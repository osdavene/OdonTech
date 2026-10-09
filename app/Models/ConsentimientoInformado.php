<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentimientoInformado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consentimientos_informados';

    protected $fillable = [
        'historia_clinica_id',
        'paciente_id',
        'odontologo_id',
        'cups_id',
        'titulo_procedimiento',
        'cuerpo_consentimiento',
        'declaracion_aceptacion',
        'nombre_firmante',
        'documento_firmante',
        'calidad_firmante',
        'firma_digital_base64',
        'huella_biometrica_hash',
        'archivo_soporte_url',
        'estado',
        'fecha_hora_firma',
        'ip_firmante',
        'motivo_revocacion',
        'fecha_hora_revocacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_firma' => 'datetime',
            'fecha_hora_revocacion' => 'datetime',
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

    public function procedimientoCups(): BelongsTo
    {
        return $this->belongsTo(CatalogoCups::class, 'cups_id');
    }

    public function estaFirmado(): bool
    {
        return $this->estado === 'FIRMADO';
    }

    public function estaRevocado(): bool
    {
        return $this->estado === 'REVOCADO';
    }
}
