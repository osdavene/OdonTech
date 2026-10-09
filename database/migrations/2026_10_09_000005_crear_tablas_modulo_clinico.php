<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 2: Módulo Clínico Odontológico.
     * Cumplimiento estricto de:
     * - Resolución 1995 de 1999 (Estructura y diligenciamiento de Historia Clínica).
     * - Resolución 839 de 2017 (Custodia, retención y prohibición de eliminación física).
     * - Nomenclatura FDI para Odontograma y Periodontograma.
     * - Gestión legal de Consentimientos Informados.
     */
    public function up(): void
    {
        // 1. Historia Clínica Principal
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('numero_historia', 30)->unique();
            $table->text('motivo_consulta');
            $table->text('enfermedad_actual');

            // Antecedentes Médicos Sistémicos y Estomatológicos (JSON estructurado)
            $table->json('antecedentes_medicos')->nullable()->comment('Patológicos, quirúrgicos, alérgicos, farmacológicos, toxicológicos, familiares');
            $table->json('examen_estomatologico')->nullable()->comment('Labios, carrillos, paladar, lengua, piso boca, ATM, ganglios');

            // Diagnósticos de apertura
            $table->foreignId('diagnostico_principal_cie_id')->constrained('catalogos_cie')->restrictOnDelete();
            $table->foreignId('diagnostico_relacionado_cie_id')->nullable()->constrained('catalogos_cie')->nullOnDelete();

            $table->text('plan_tratamiento');
            $table->enum('pronostico', ['BUENO', 'FAVORABLE', 'RESERVADO', 'DESFAVORABLE'])->default('FAVORABLE');
            $table->enum('estado', ['ABIERTA', 'EN_TRATAMIENTO', 'FINALIZADA', 'ARCHIVO_GESTION', 'ARCHIVO_CENTRAL'])->default('ABIERTA');
            $table->dateTime('fecha_cierre')->nullable();

            $table->timestamps();
            // OBLIGATORIO: SoftDeletes (Prohibida eliminación física por Ley de Historias Clínicas)
            $table->softDeletes()->comment('Custodia 15 años requerida por Resolución 839 de 2017');

            $table->index(['paciente_id', 'estado']);
        });

        // 2. Evoluciones Clínicas y Notas Aclaratorias (Inmutabilidad del registro previo)
        Schema::create('evoluciones_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo_registro', [
                'EVOLUCION_TRATAMIENTO',
                'NOTA_ACLARATORIA',
                'URGENCIA_ODONTOLOGICA'
            ])->default('EVOLUCION_TRATAMIENTO')->comment('Permite aclaraciones cronológicas sin alterar la nota original');
            $table->dateTime('fecha_hora_atencion');

            // Estructura SOAP (Subjetivo, Objetivo, Análisis, Plan)
            $table->text('descripcion_subjetiva')->nullable()->comment('Relato del paciente en la sesión');
            $table->text('descripcion_objetiva')->nullable()->comment('Hallazgos clínicos y exploración');
            $table->text('analisis_diagnostico')->comment('Juicio clínico del profesional');
            $table->text('plan_procedimiento')->comment('Procedimiento ejecutado y próximos pasos');

            // Procedimiento realizado
            $table->foreignId('cups_id')->nullable()->constrained('catalogos_cups')->nullOnDelete();
            $table->unsignedTinyInteger('diente_fdi')->nullable()->comment('Diente tratado según sistema FDI (11 a 85)');
            $table->string('superficie_diente', 20)->nullable()->comment('V, L/P, M, D, O/I');
            $table->text('complicaciones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->date('proxima_cita_sugerida')->nullable();

            // Hash de integridad para firma electrónica y blindaje legal
            $table->string('firma_digital_hash', 64)->comment('Sellado SHA-256 que garantiza inalterabilidad de la nota médica');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['historia_clinica_id', 'fecha_hora_atencion']);
        });

        // 3. Odontogramas (Inicial y de Seguimiento / Evolución)
        Schema::create('odontogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo_odontograma', ['INICIAL', 'EVOLUCION'])->default('INICIAL');
            $table->dateTime('fecha_registro');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['historia_clinica_id', 'tipo_odontograma']);
        });

        // 4. Detalle por Diente y Superficie FDI en Odontograma
        Schema::create('odontograma_dientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontograma_id')->constrained('odontogramas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_diente_fdi')->comment('Nomenclatura FDI: 11-18, 21-28, 31-38, 41-48, 51-55, 61-65, 71-75, 81-85');
            $table->enum('estado_general_diente', [
                'SANO',
                'AUSENTE',
                'EXODONCIA_INDICADA',
                'CORONA',
                'ENDODONCIA_REALIZADA',
                'ENDODONCIA_REQUERIDA',
                'IMPLANTE',
                'PROTESIS_FIJA',
                'PROTESIS_REMOVIBLE',
                'FRACTURA',
                'EROSION',
                'SEMI_ERUPCIONADO'
            ])->default('SANO');

            // 5 Superficies anatómicas del diente
            $table->enum('superficie_vestibular', ['SANO', 'CARIES', 'OBTURACION_RESINA', 'OBTURACION_AMALGAMA', 'SELLANTE', 'FRACTURA'])->default('SANO');
            $table->enum('superficie_lingual_palatina', ['SANO', 'CARIES', 'OBTURACION_RESINA', 'OBTURACION_AMALGAMA', 'SELLANTE', 'FRACTURA'])->default('SANO');
            $table->enum('superficie_mesial', ['SANO', 'CARIES', 'OBTURACION_RESINA', 'OBTURACION_AMALGAMA', 'SELLANTE', 'FRACTURA'])->default('SANO');
            $table->enum('superficie_distal', ['SANO', 'CARIES', 'OBTURACION_RESINA', 'OBTURACION_AMALGAMA', 'SELLANTE', 'FRACTURA'])->default('SANO');
            $table->enum('superficie_oclusal_incisal', ['SANO', 'CARIES', 'OBTURACION_RESINA', 'OBTURACION_AMALGAMA', 'SELLANTE', 'FRACTURA'])->default('SANO');

            $table->foreignId('diagnostico_cie_id')->nullable()->constrained('catalogos_cie')->nullOnDelete();
            $table->foreignId('procedimiento_propuesto_cups_id')->nullable()->constrained('catalogos_cups')->nullOnDelete();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->unique(['odontograma_id', 'numero_diente_fdi'], 'unq_odon_diente');
        });

        // 5. Periodontograma
        Schema::create('periodontogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->dateTime('fecha_evaluacion');
            $table->decimal('indice_placa_porcentaje', 5, 2)->default(0);
            $table->decimal('indice_sangrado_porcentaje', 5, 2)->default(0);
            $table->text('diagnostico_periodontal')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['historia_clinica_id', 'fecha_evaluacion']);
        });

        // 6. Detalle de Sondaje por Diente (FDI)
        Schema::create('periodontograma_dientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodontograma_id')->constrained('periodontogramas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_diente_fdi');

            // Profundidad de sondaje en milímetros (6 puntos clínicos universales)
            $table->unsignedTinyInteger('profundidad_vestibular_distal')->default(0);
            $table->unsignedTinyInteger('profundidad_vestibular_medio')->default(0);
            $table->unsignedTinyInteger('profundidad_vestibular_mesial')->default(0);
            $table->unsignedTinyInteger('profundidad_lingual_distal')->default(0);
            $table->unsignedTinyInteger('profundidad_lingual_medio')->default(0);
            $table->unsignedTinyInteger('profundidad_lingual_mesial')->default(0);

            // Margen gingival en milímetros (- agrandamiento, + recesión)
            $table->tinyInteger('margen_vestibular')->default(0);
            $table->tinyInteger('margen_lingual')->default(0);

            // Indicadores periodontales
            $table->boolean('sangrado_al_sondaje')->default(false);
            $table->boolean('supuracion')->default(false);
            $table->enum('movilidad_dental', ['0', '1', '2', '3'])->default('0');
            $table->enum('lesion_furca', ['NO_APLICA', 'CLASE_I', 'CLASE_II', 'CLASE_III'])->default('NO_APLICA');

            $table->timestamps();
            $table->unique(['periodontograma_id', 'numero_diente_fdi'], 'unq_periodon_diente');
        });

        // 7. Consentimientos Informados Odontológicos
        Schema::create('consentimientos_informados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->restrictOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('cups_id')->nullable()->constrained('catalogos_cups')->nullOnDelete();

            $table->string('titulo_procedimiento', 200);
            $table->longText('cuerpo_consentimiento')->comment('Explicación clara de riesgos, molestias, beneficios, anestesia y alternativas');
            $table->text('declaracion_aceptacion')->comment('Texto juramentado de aceptación voluntaria');

            // Datos del firmante
            $table->string('nombre_firmante', 120);
            $table->string('documento_firmante', 20);
            $table->enum('calidad_firmante', ['PACIENTE', 'REPRESENTANTE_LEGAL', 'TUTOR', 'FAMILIAR_RESPONSABLE'])->default('PACIENTE');
            $table->longText('firma_digital_base64')->nullable()->comment('Trazo gráfico digitalizado o firma en pantalla táctil');
            $table->string('huella_biometrica_hash', 64)->nullable();
            $table->string('archivo_soporte_url', 255)->nullable()->comment('PDF firmado o documento escaneado');

            $table->enum('estado', ['PENDIENTE', 'FIRMADO', 'REVOCADO'])->default('PENDIENTE');
            $table->dateTime('fecha_hora_firma')->nullable();
            $table->string('ip_firmante', 45)->nullable();

            $table->text('motivo_revocacion')->nullable();
            $table->dateTime('fecha_hora_revocacion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paciente_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimientos_informados');
        Schema::dropIfExists('periodontograma_dientes');
        Schema::dropIfExists('periodontogramas');
        Schema::dropIfExists('odontograma_dientes');
        Schema::dropIfExists('odontogramas');
        Schema::dropIfExists('evoluciones_clinicas');
        Schema::dropIfExists('historias_clinicas');
    }
};
