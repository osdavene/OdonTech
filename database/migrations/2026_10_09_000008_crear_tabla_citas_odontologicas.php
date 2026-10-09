<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo de Agenda y Citas Odontológicas.
     * Gestión de turnos, asignación de unidades dentales y vinculación a atención clínica.
     */
    public function up(): void
    {
        Schema::create('citas_odontologicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('usuario_creador_id')->constrained('usuarios')->restrictOnDelete();

            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->unsignedTinyInteger('unidad_sillon_numero')->default(1)->comment('Número de unidad / sillón odontológico');

            $table->string('motivo_consulta', 200);
            $table->foreignId('cups_propuesto_id')->nullable()->constrained('catalogos_cups')->nullOnDelete();

            $table->enum('estado', [
                'AGENDADA',
                'CONFIRMADA',
                'EN_SALA_ESPERA',
                'EN_ATENCION',
                'ATENDIDA',
                'CANCELADA',
                'INASISTENCIA'
            ])->default('AGENDADA');

            $table->text('observaciones')->nullable();
            $table->foreignId('historia_clinica_id')->nullable()->constrained('historias_clinicas')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['odontologo_id', 'fecha_hora_inicio']);
            $table->index(['paciente_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas_odontologicas');
    }
};
