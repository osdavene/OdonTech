<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaAcceso extends Model
{
    use HasFactory;

    protected $table = 'auditorias_accesos';

    // Append-only inmutable: no tiene columna updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'rol_usuario',
        'direccion_ip',
        'navegador_agente',
        'tipo_evento',
        'tabla_afectada',
        'registro_id',
        'paciente_id',
        'justificacion_acceso',
        'datos_anteriores',
        'datos_nuevos',
        'hash_integridad',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    /**
     * Genera un hash criptográfico inalterable combinando la cadena del evento
     * con el hash previo para garantizar encadenamiento forense.
     */
    public static function generarHashIntegridad(array $payload, ?string $hashAnterior = null): string
    {
        $semilla = ($hashAnterior ?? 'ODONTECH_BLOQUE_GENESIS') . '|' . json_encode($payload);
        return hash('sha256', $semilla);
    }
}
