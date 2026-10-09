<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 1: Seguridad y Auditoría (Ley 1581 de 2012 - Habeas Data).
     * Tabla inalterable (Append-Only) para trazabilidad forense de accesos a datos clínicos.
     */
    public function up(): void
    {
        Schema::create('auditorias_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('rol_usuario', 50)->comment('Rol del usuario en el momento exacto del evento');
            $table->string('direccion_ip', 45);
            $table->text('navegador_agente')->nullable();
            $table->string('tipo_evento', 50)->comment('CREACION, LECTURA_CONSULTA, MODIFICACION, ELIMINACION_LOGICA, EXPORTACION_PDF, TRANSMISION_RIPS');
            $table->string('tabla_afectada', 80)->index();
            $table->unsignedBigInteger('registro_id')->index();
            $table->unsignedBigInteger('paciente_id')->nullable()->index()->comment('Paciente titular para trazabilidad unificada del expediente');
            $table->text('justificacion_acceso')->nullable()->comment('Motivo del acceso clínico o auditoría');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('hash_integridad', 64)->comment('Firma criptográfica SHA-256 inalterable encadenada');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora inmutable de registro');

            $table->index(['paciente_id', 'created_at']);
            $table->index(['usuario_id', 'tipo_evento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias_accesos');
    }
};
