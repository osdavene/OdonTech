<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 3: Catálogos Maestros de Salud (Resolución 2275 de 2023 y 510 de 2024).
     * Códigos CUPS (Procedimientos) y CIE-10 / CIE-11 (Diagnósticos).
     */
    public function up(): void
    {
        // Catálogo CUPS (Clasificación Única de Procedimientos en Salud)
        Schema::create('catalogos_cups', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 6)->unique()->comment('Código estándar de 6 caracteres alfanuméricos');
            $table->string('descripcion', 255);
            $table->boolean('seccion_odontologia')->default(true);
            $table->enum('tipo_servicio_rips', ['01_CONSULTA', '02_PROCEDIMIENTO'])->default('02_PROCEDIMIENTO');
            $table->decimal('tarifa_base_particular', 12, 2)->default(0);
            $table->boolean('requiere_diente_fdi')->default(false)->comment('Indica si el procedimiento exige especificar diente o cavidad');
            $table->boolean('requiere_superficie_fdi')->default(false)->comment('Indica si exige superficie dental');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['codigo', 'activo']);
        });

        // Catálogo CIE (Clasificación Internacional de Enfermedades CIE-10 / CIE-11)
        Schema::create('catalogos_cie', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 6)->unique()->comment('Código CIE (ej. K021 Caries de dentina)');
            $table->enum('version', ['CIE-10', 'CIE-11'])->default('CIE-10');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->boolean('es_odontologico')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['codigo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos_cie');
        Schema::dropIfExists('catalogos_cups');
    }
};
