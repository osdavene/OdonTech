<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'tipo_documento',
        'numero_documento',
        'nombre_completo',
        'registro_profesional',
        'especialidad',
        'correo_electronico',
        'contrasena',
        'telefono',
        'activo',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contrasena' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Compatibilidad con el motor de autenticación de Laravel.
     */
    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function historiasClinicas(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'odontologo_id');
    }

    public function evolucionesClinicas(): HasMany
    {
        return $this->hasMany(EvolucionClinica::class, 'odontologo_id');
    }

    public function odontogramas(): HasMany
    {
        return $this->hasMany(Odontograma::class, 'odontologo_id');
    }

    public function periodontogramas(): HasMany
    {
        return $this->hasMany(Periodontograma::class, 'odontologo_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(AuditoriaAcceso::class, 'usuario_id');
    }

    public function facturasEmitidas(): HasMany
    {
        return $this->hasMany(Factura::class, 'usuario_id');
    }

    // Métodos de verificación de roles estrictos
    public function esAdministrador(): bool
    {
        return $this->rol?->nombre === Rol::ADMIN;
    }

    public function esOdontologo(): bool
    {
        return $this->rol?->nombre === Rol::ODONTOLOGO;
    }

    public function esAsistente(): bool
    {
        return $this->rol?->nombre === Rol::ASISTENTE;
    }
}
