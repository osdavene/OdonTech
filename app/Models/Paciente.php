<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'sexo_biologico',
        'grupo_sanguineo_rh',
        'pais_residencia_codigo',
        'departamento_codigo',
        'municipio_codigo',
        'zona_residencia',
        'direccion_residencia',
        'telefono_principal',
        'telefono_secundario',
        'correo_electronico',
        'ocupacion',
        'estado_civil',
        'tipo_usuario_rips',
        'aseguradora_eps',
        'codigo_eps',
        'incapacidad',
        'nombre_acompanante',
        'telefono_acompanante',
        'nombre_responsable',
        'parentesco_responsable',
        'telefono_responsable',
        // Ley 1581 de 2012 (Habeas Data)
        'acepta_tratamiento_datos',
        'fecha_aceptacion_datos',
        'medio_aceptacion_datos',
        'ip_aceptacion_datos',
        'autorizacion_envio_mensajes',
        'soporte_autorizacion_url',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_aceptacion_datos' => 'datetime',
            'acepta_tratamiento_datos' => 'boolean',
            'autorizacion_envio_mensajes' => 'boolean',
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->primer_apellido} {$this->segundo_apellido}")
        );
    }

    public function historiasClinicas(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'paciente_id');
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(ConsentimientoInformado::class, 'paciente_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'paciente_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(AuditoriaAcceso::class, 'paciente_id');
    }

    /**
     * Scope para filtrar únicamente pacientes que hayan otorgado
     * autorización explícita de tratamiento de datos personales.
     */
    public function scopeConTratamientoDatosAceptado(Builder $query): Builder
    {
        return $query->where('acepta_tratamiento_datos', true);
    }
}
