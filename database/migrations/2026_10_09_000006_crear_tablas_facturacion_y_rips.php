<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 3: Facturación Electrónica en Salud y RIPS.
     * Cumplimiento estricto de:
     * - Resolución 2275 de 2023 y Resolución 510 de 2024 (RIPS JSON Ministerio de Salud).
     * - Anexo Técnico de Factura Electrónica en Salud DIAN y enlace mandatorio con CUFE.
     */
    public function up(): void
    {
        // 1. Facturas de Venta Electrónicas
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('historia_clinica_id')->nullable()->constrained('historias_clinicas')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete()->comment('Usuario emisor');

            $table->enum('tipo_documento', [
                'FACTURA_ELECTRONICA_VENTA',
                'NOTA_CREDITO',
                'NOTA_DEBITO'
            ])->default('FACTURA_ELECTRONICA_VENTA');

            $table->string('prefijo', 5)->default('FEV');
            $table->unsignedInteger('consecutivo');
            $table->string('numero_factura', 25)->unique()->comment('Prefijo + Consecutivo (ej. FEV1020)');
            $table->string('resolucion_dian', 50)->comment('Número de habilitación de numeración DIAN');
            $table->dateTime('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('medio_pago_codigo', 2)->default('10')->comment('10: Efectivo, 48: T. Crédito, 49: T. Débito, 47: Transferencia');

            // Valores financieros
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('copago', 12, 2)->default(0)->comment('Copago paciente según régimen SGSSS');
            $table->decimal('cuota_moderadora', 12, 2)->default(0);
            $table->decimal('total_pagar', 12, 2);

            // Integración DIAN - Proveedor Tecnológico Autorizado (PTA)
            $table->string('cufe', 100)->nullable()->unique()->comment('Código Único de Factura Electrónica (Mandatorio DIAN)');
            $table->text('qr_cadena')->nullable();
            $table->string('xml_url', 255)->nullable()->comment('Ruta al XML ApplicationResponse firmado por DIAN');
            $table->string('pdf_url', 255)->nullable()->comment('Representación gráfica oficial');
            $table->enum('estado_dian', [
                'BORRADOR',
                'ENVIADA_PTA',
                'VALIDADA_DIAN',
                'RECHAZADA_DIAN',
                'ANULADA'
            ])->default('BORRADOR');
            $table->string('dian_respuesta_codigo', 50)->nullable();
            $table->text('dian_respuesta_mensaje')->nullable();
            $table->dateTime('dian_fecha_validacion')->nullable();

            // Integración RIPS MinSalud (Resolución 2275 de 2023)
            $table->enum('rips_estado', [
                'PENDIENTE',
                'GENERADO',
                'ENVIADO_SISPRO',
                'VALIDADO_CUV',
                'RECHAZADO'
            ])->default('PENDIENTE');
            $table->string('rips_cuv', 100)->nullable()->comment('Código Único de Validación retornado por MinSalud SISPRO');
            $table->dateTime('rips_fecha_generacion')->nullable();
            $table->dateTime('rips_fecha_validacion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paciente_id', 'estado_dian']);
            $table->index('cufe');
        });

        // 2. Líneas de detalle de la Factura
        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('cups_id')->constrained('catalogos_cups')->restrictOnDelete();
            $table->foreignId('cie_id')->constrained('catalogos_cie')->restrictOnDelete();
            $table->foreignId('cie_relacionado_id')->nullable()->constrained('catalogos_cie')->nullOnDelete();

            $table->enum('tipo_servicio_rips', ['01_CONSULTA', '02_PROCEDIMIENTO'])->default('02_PROCEDIMIENTO');
            $table->string('descripcion_concepto', 255);
            $table->decimal('cantidad', 6, 2)->default(1);
            $table->decimal('valor_unitario', 12, 2);
            $table->decimal('valor_descuento', 12, 2)->default(0);
            $table->decimal('valor_total', 12, 2);
            $table->decimal('copago', 12, 2)->default(0);
            $table->decimal('cuota_moderadora', 12, 2)->default(0);

            // Requerimientos técnicos RIPS Odontología
            $table->unsignedTinyInteger('diente_fdi')->nullable();
            $table->string('finalidad_tecnologia_salud', 2)->default('15')->comment('10: Diagnóstico, 15: Tratamiento, 44: Promoción');
            $table->string('causa_motivo_atencion', 2)->default('38')->comment('38: Enfermedad general');
            $table->string('tipo_diagnostico_principal', 2)->default('02')->comment('01: Impresión, 02: Confirmado nuevo, 03: Confirmado repetido');

            $table->foreignId('evolucion_id')->nullable()->constrained('evoluciones_clinicas')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Trazabilidad de Transmisión RIPS (SISPRO)
        Schema::create('rips_transacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete();
            $table->string('num_documento_id_obligado', 20)->comment('NIT del prestador de salud');
            $table->string('num_factura', 25)->comment('Número de factura electrónica');
            $table->string('cufe', 100)->comment('CUFE de la DIAN enlazado obligatoriamente en el RIPS');
            $table->string('tipo_nota', 2)->nullable();
            $table->string('num_nota', 20)->nullable();
            $table->longText('json_rips_enviado')->comment('JSON estandarizado MinSalud Res 2275/2023');
            $table->longText('json_sispro_respuesta')->nullable();
            $table->string('cuv_sispro', 100)->nullable()->comment('CUV obtenido tras validación exitosa');
            $table->enum('estado', [
                'GENERADO',
                'ENVIADO',
                'VALIDADO_APROBADO',
                'CON_ERRORES'
            ])->default('GENERADO');
            $table->json('errores_validacion')->nullable();
            $table->timestamps();

            $table->index(['num_factura', 'cufe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rips_transacciones');
        Schema::dropIfExists('factura_detalles');
        Schema::dropIfExists('facturas');
    }
};
