<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 1: Seguridad y Auditoría (Ley 1581 de 2012 - Habeas Data).
     * Creación de roles estrictos y esquema de usuarios en español.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique()->comment('Administrador, Odontólogo, Asistente');
            $table->string('slug', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->string('tipo_documento', 10)->default('CC')->comment('CC, CE, TI, PASAPORTE');
            $table->string('numero_documento', 20)->unique();
            $table->string('nombre_completo', 150);
            $table->string('registro_profesional', 50)->nullable()->comment('Tarjeta Profesional ReTHUS para Odontólogos');
            $table->string('especialidad', 100)->nullable()->comment('Odontología General, Ortodoncia, Endodoncia, Periodoncia, Cirugía Oral, etc.');
            $table->string('correo_electronico', 150)->unique();
            $table->string('contrasena');
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo', 'rol_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('correo_electronico')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sesiones', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('usuario_id')->nullable()->index()->constrained('usuarios')->nullOnDelete();
            $table->string('ip_direccion', 45)->nullable();
            $table->text('navegador_agente')->nullable();
            $table->longText('payload');
            $table->integer('ultima_actividad')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
