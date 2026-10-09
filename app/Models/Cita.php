<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cita extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'citas_odontologicas';

    protected $fillable = [
        'paciente_id',
        'odontologo_id',
        'usuario_creador_id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'unidad_sillon_numero',
        'motivo_consulta',
        'cups_propuesto_id',
        'estado',
        'observaciones',
        'historia_clinica_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_inicio' => 'datetime',
            'fecha_hora_fin' => 'datetime',
            'unidad_sillon_numero' => 'integer',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function odontologo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'odontologo_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_creador_id');
    }

    public function cupsPropuesto(): BelongsTo
    {
        return $this->belongsTo(CatalogoCups::class, 'cups_propuesto_id');
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function scopeParaFecha(Builder $query, string $fecha): Builder
    {
        return $query->whereDate('fecha_hora_inicio', $fecha);
    }

    public function scopeHoy(Builder $query): Builder
    {
        return $query->whereDate('fecha_hora_inicio', Carbon::today());
    }

    public function scopeDelOdontologo(Builder $query, int $odontologoId): Builder
    {
        return $query->where('odontologo_id', $odontologoId);
    }
}
