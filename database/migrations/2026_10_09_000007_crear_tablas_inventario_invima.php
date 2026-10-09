<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Módulo 4: Inventario y Trazabilidad de Dispositivos Médicos e Insumos.
     * Cumplimiento estricto de Normativa INVIMA:
     * - Registro Sanitario INVIMA obligatorio.
     * - Lote de fabricación para Tecnovigilancia y Reactivovigilancia.
     * - Fecha de Vencimiento y alertas de prevención de uso de materiales caducados.
     * - Control de Stock Mínimo.
     */
    public function up(): void
    {
        // 1. Maestro de Insumos Odontológicos
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno', 30)->unique();
            $table->string('nombre_comercial', 150);
            $table->string('principio_activo_material', 150)->comment('Composición química o material (ej. Resina Nanohíbrida, Lidocaína 2%)');
            $table->string('presentacion', 100)->comment('ej. Jeringa 4g, Caja x 50 cárpules');
            $table->enum('unidad_medida', [
                'UNIDAD',
                'CAJA',
                'TUBO',
                'FRASCO',
                'JERINGA',
                'KIT',
                'GRAMO',
                'MILILITRO'
            ])->default('UNIDAD');
            $table->enum('categoria', [
                'BIOMATERIAL_RESTAURADOR',
                'ANESTESICO_FARMACO',
                'ENDODONCIA',
                'PERIODONCIA',
                'ORTODONCIA',
                'BIOSEGURIDAD_ESTERILIZACION',
                'IMPLANTOLOGIA'
            ]);

            $table->unsignedInteger('stock_minimo')->default(5)->comment('Umbral para disparo de alertas de reabastecimiento');
            $table->unsignedInteger('stock_actual')->default(0);
            $table->boolean('requiere_cadena_frio')->default(false);
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['categoria', 'activo']);
        });

        // 2. Lotes con trazabilidad INVIMA y Caducidad
        Schema::create('lotes_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->restrictOnDelete();
            $table->string('lote_invima', 50)->comment('Número de lote del fabricante para trazabilidad');
            $table->string('registro_sanitario_invima', 50)->comment('Registro Sanitario INVIMA obligatorio');
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->comment('Fecha límite de uso clínico según INVIMA');
            $table->unsignedInteger('cantidad_recibida');
            $table->unsignedInteger('cantidad_disponible');
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->enum('estado', [
                'VIGENTE',
                'PROXIMO_A_VENCER',
                'VENCIDO',
                'AGOTADO',
                'CUARENTENA_INVIMA'
            ])->default('VIGENTE');

            $table->timestamps();

            $table->index(['insumo_id', 'estado']);
            $table->index('fecha_vencimiento');
        });

        // 3. Registro de Movimientos (Kárdex y Tecnovigilancia)
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes_inventario')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('historia_clinica_id')->nullable()->constrained('historias_clinicas')->nullOnDelete()->comment('Enlace de tecnovigilancia: insumo implantado o aplicado al paciente');

            $table->enum('tipo_movimiento', [
                'ENTRADA_COMPRA',
                'SALIDA_ATENCION_CLINICA',
                'AJUSTE_INVENTARIO',
                'BAJA_VENCIMIENTO_INVIMA',
                'RETIRO_ALERTA_SANITARIA'
            ]);
            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('saldo_anterior');
            $table->unsignedInteger('saldo_posterior');
            $table->text('motivo_observaciones');
            $table->timestamps();

            $table->index(['insumo_id', 'tipo_movimiento']);
        });

        // 4. Alertas Preventivas de Caducidad y Quiebre de Stock
        Schema::create('alertas_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes_inventario')->cascadeOnDelete();
            $table->enum('tipo_alerta', [
                'STOCK_MINIMO_CRITICO',
                'LOTE_PROXIMO_A_VENCER_30_DIAS',
                'LOTE_VENCIDO_BLOQUEADO',
                'ALERTA_SANITARIA_INVIMA'
            ]);
            $table->text('mensaje_alerta');
            $table->enum('nivel_severidad', [
                'INFORMATIVA',
                'MEDIA',
                'ALTA',
                'CRITICA_BLOQUEANTE'
            ])->default('MEDIA');
            $table->boolean('leida')->default(false);
            $table->boolean('resuelta')->default(false);
            $table->foreignId('usuario_resolucion_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->timestamps();

            $table->index(['resuelta', 'nivel_severidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_inventario');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('lotes_inventario');
        Schema::dropIfExists('insumos');
    }
};
