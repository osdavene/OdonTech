<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 1 y 2: Pacientes (Ley 1581 de 2012 Habeas Data y MinSalud SISPRO).
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Identificación oficial MinSalud (Resolución 2275 de 2023)
            $table->string('tipo_documento', 2)->comment('CC, TI, RC, CE, PA, NV, CD, SC, PR, PE, AS');
            $table->string('numero_documento', 20)->unique();
            $table->string('primer_nombre', 60);
            $table->string('segundo_nombre', 60)->nullable();
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo_biologico', ['M', 'F', 'INDETERMINADO'])->comment('M: Masculino, F: Femenino');
            $table->string('grupo_sanguineo_rh', 5)->nullable()->comment('O+, O-, A+, A-, B+, B-, AB+, AB-');

            // Datos demográficos y ubicación (DANE)
            $table->string('pais_residencia_codigo', 3)->default('170')->comment('170: Colombia (ISO 3166-1)');
            $table->string('departamento_codigo', 2)->default('11')->comment('Código DANE 2 dígitos (ej. 11 Bogotá, 05 Antioquia)');
            $table->string('municipio_codigo', 5)->default('11001')->comment('Código DANE 5 dígitos (ej. 11001 Bogotá)');
            $table->enum('zona_residencia', ['01_URBANO', '02_RURAL'])->default('01_URBANO');
            $table->string('direccion_residencia', 200);
            $table->string('telefono_principal', 25);
            $table->string('telefono_secundario', 25)->nullable();
            $table->string('correo_electronico', 150)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->string('estado_civil', 30)->nullable();

            // Afiliación al Sistema General de Seguridad Social en Salud (SGSSS)
            $table->string('tipo_usuario_rips', 2)->default('05')->comment('01: Cotizante, 02: Beneficiario, 03: Adicional, 04: Subsidiado, 05: Particular');
            $table->string('aseguradora_eps', 120)->nullable();
            $table->string('codigo_eps', 10)->nullable();
            $table->enum('incapacidad', ['NO', 'SI'])->default('NO');

            // Contactos de emergencia y acompañante
            $table->string('nombre_acompanante', 120)->nullable();
            $table->string('telefono_acompanante', 25)->nullable();
            $table->string('nombre_responsable', 120)->nullable();
            $table->string('parentesco_responsable', 50)->nullable();
            $table->string('telefono_responsable', 25)->nullable();

            // OBLIGATORIO: Habeas Data y Consentimiento de Tratamiento de Datos (Ley 1581 de 2012)
            $table->boolean('acepta_tratamiento_datos')->default(false)->comment('Aceptación expresa de la política de tratamiento de datos personales');
            $table->dateTime('fecha_aceptacion_datos')->nullable()->comment('Marca temporal exacta de la autorización');
            $table->enum('medio_aceptacion_datos', [
                'FIRMA_DIGITAL',
                'FORMATO_FISICO_ESCANEADO',
                'VERBAL_GRABADO',
                'PORTAL_PACIENTE'
            ])->nullable()->comment('Mecanismo de captura del consentimiento informado de datos');
            $table->string('ip_aceptacion_datos', 45)->nullable()->comment('Dirección IP desde donde se otorgó la autorización');
            $table->boolean('autorizacion_envio_mensajes')->default(false)->comment('Permiso para recordatorios por WhatsApp/Email');
            $table->string('soporte_autorizacion_url', 255)->nullable()->comment('Ruta al documento firmado digitalizado');

            $table->timestamps();
            $table->softDeletes()->comment('Custodia y conservación legal de datos según Resolución 839 de 2017');

            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('acepta_tratamiento_datos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
