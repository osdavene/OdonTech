<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo Clínico: Fórmulas Médicas y Prescripciones Odontológicas.
     * Cumplimiento del Decreto 2200 de 2005 y Resolución 1995 de 1999 (Prescripción Médica y ReTHUS).
     */
    public function up(): void
    {
        Schema::create('formulas_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->restrictOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('diagnostico_cie_id')->nullable()->constrained('catalogos_cie')->nullOnDelete();

            $table->dateTime('fecha_emision');
            $table->unsignedSmallInteger('vigencia_dias')->default(30);
            $table->text('recomendaciones_generales')->nullable();
            $table->string('firma_digital_hash', 64)->comment('Sello criptográfico de la prescripción con tarjeta profesional ReTHUS');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paciente_id', 'fecha_emision']);
        });

        Schema::create('formula_medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_medica_id')->constrained('formulas_medicas')->cascadeOnDelete();

            $table->string('medicamento_nombre', 150)->comment('Denominación Común Internacional o Nombre Comercial');
            $table->string('concentracion', 50)->comment('Ej: 500mg, 875mg/125mg, 0.12%');
            $table->string('forma_farmaceutica', 50)->comment('Tabletas, Cápsulas, Solución oral, Gel bucal');
            $table->string('via_administracion', 30)->default('Oral');
            $table->string('dosis', 80)->comment('Ej: 1 tableta cada 8 horas');
            $table->string('duracion_tratamiento', 50)->comment('Ej: Por 7 días');
            $table->string('cantidad_total', 30)->comment('Ej: 21 tabletas');
            $table->text('indicaciones_uso')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_medicamentos');
        Schema::dropIfExists('formulas_medicas');
    }
};
