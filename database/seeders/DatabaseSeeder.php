<?php

namespace Database\Seeders;

use App\Models\CatalogoCie;
use App\Models\CatalogoCups;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Población de datos iniciales maestros y normativos para OdonTech Colombia.
     */
    public function run(): void
    {
        // 1. Roles Estrictos (Módulo de Seguridad)
        $rolAdmin = Rol::create([
            'nombre' => Rol::ADMIN,
            'slug' => 'administrador',
            'descripcion' => 'Acceso total gerencial, auditorías de Habeas Data, facturación y reportes.',
        ]);

        $rolOdonto = Rol::create([
            'nombre' => Rol::ODONTOLOGO,
            'slug' => 'odontologo',
            'descripcion' => 'Diligenciamiento de historias clínicas, evoluciones, odontograma FDI y consentimientos.',
        ]);

        $rolAsistente = Rol::create([
            'nombre' => Rol::ASISTENTE,
            'slug' => 'asistente',
            'descripcion' => 'Admisiones, agenda de citas, verificación de tratamiento de datos e inventarios.',
        ]);

        // 2. Usuarios Base
        $admin = Usuario::create([
            'rol_id' => $rolAdmin->id,
            'tipo_documento' => 'CC',
            'numero_documento' => '1020304050',
            'nombre_completo' => 'Dra. Claudia Marcela Pérez',
            'registro_profesional' => null,
            'especialidad' => 'Administración en Salud',
            'correo_electronico' => 'admin@odontech.com.co',
            'contrasena' => Hash::make('Admin2026*'),
            'telefono' => '3101234567',
            'activo' => true,
        ]);

        $odontologo = Usuario::create([
            'rol_id' => $rolOdonto->id,
            'tipo_documento' => 'CC',
            'numero_documento' => '80901234',
            'nombre_completo' => 'Dr. Carlos Alberto Morales Gómez',
            'registro_profesional' => 'TP-ODO-54891',
            'especialidad' => 'Odontología General y Rehabilitación Oral',
            'correo_electronico' => 'odontologo@odontech.com.co',
            'contrasena' => Hash::make('Odonto2026*'),
            'telefono' => '3157894561',
            'activo' => true,
        ]);

        $asistente = Usuario::create([
            'rol_id' => $rolAsistente->id,
            'tipo_documento' => 'CC',
            'numero_documento' => '52987654',
            'nombre_completo' => 'Sra. Andrea Valentina Castillo',
            'registro_profesional' => null,
            'especialidad' => 'Auxiliar de Salud Oral',
            'correo_electronico' => 'asistente@odontech.com.co',
            'contrasena' => Hash::make('Asistente2026*'),
            'telefono' => '3004561234',
            'activo' => true,
        ]);

        // 3. Catálogos CUPS (Procedimientos Odontológicos Frecuentes)
        $cupsList = [
            [
                'codigo' => '890203',
                'descripcion' => 'CONSULTA DE PRIMERA VEZ POR ODONTOLOGÍA GENERAL',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '01_CONSULTA',
                'tarifa_base_particular' => 80000,
                'requiere_diente_fdi' => false,
            ],
            [
                'codigo' => '890303',
                'descripcion' => 'CONSULTA DE CONTROL O DE SEGUIMIENTO POR ODONTOLOGÍA GENERAL',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '01_CONSULTA',
                'tarifa_base_particular' => 50000,
                'requiere_diente_fdi' => false,
            ],
            [
                'codigo' => '230101',
                'descripcion' => 'EXODONCIA DE DIENTE PERMANENTE UNIRRADICULAR',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '02_PROCEDIMIENTO',
                'tarifa_base_particular' => 90000,
                'requiere_diente_fdi' => true,
            ],
            [
                'codigo' => '232101',
                'descripcion' => 'OBTURACIÓN DENTAL CON RESINA DE FOTOCURADO (UNA SUPERFICIE)',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '02_PROCEDIMIENTO',
                'tarifa_base_particular' => 110000,
                'requiere_diente_fdi' => true,
            ],
            [
                'codigo' => '234101',
                'descripcion' => 'APLICACIÓN DE SELLANTES DE FOSAS Y FISURAS (POR DIENTE)',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '02_PROCEDIMIENTO',
                'tarifa_base_particular' => 45000,
                'requiere_diente_fdi' => true,
            ],
            [
                'codigo' => '240101',
                'descripcion' => 'DETARTRAJE SUBGINGIVAL Y SUPRAGINGIVAL (PROFILAXIS DENTAL)',
                'seccion_odontologia' => true,
                'tipo_servicio_rips' => '02_PROCEDIMIENTO',
                'tarifa_base_particular' => 95000,
                'requiere_diente_fdi' => false,
            ],
        ];

        foreach ($cupsList as $item) {
            CatalogoCups::create($item);
        }

        // 4. Catálogos CIE-10 (Diagnósticos Orales Frecuentes)
        $cieList = [
            ['codigo' => 'K021', 'version' => 'CIE-10', 'nombre' => 'CARIES DE LA DENTINA', 'es_odontologico' => true],
            ['codigo' => 'K020', 'version' => 'CIE-10', 'nombre' => 'CARIES LIMITADA AL ESMALTE', 'es_odontologico' => true],
            ['codigo' => 'K040', 'version' => 'CIE-10', 'nombre' => 'PULPITIS (REVERSIBLE E IRREVERSIBLE)', 'es_odontologico' => true],
            ['codigo' => 'K051', 'version' => 'CIE-10', 'nombre' => 'GINGIVITIS CRÓNICA', 'es_odontologico' => true],
            ['codigo' => 'K053', 'version' => 'CIE-10', 'nombre' => 'PERIODONTITIS CRÓNICA', 'es_odontologico' => true],
            ['codigo' => 'K081', 'version' => 'CIE-10', 'nombre' => 'PÉRDIDA DE DIENTES POR CAUSAS DENTALES O TRAUMA', 'es_odontologico' => true],
            ['codigo' => 'Z012', 'version' => 'CIE-10', 'nombre' => 'EXAMEN ODONTOLÓGICO DE RUTINA', 'es_odontologico' => true],
        ];

        foreach ($cieList as $cie) {
            CatalogoCie::create($cie);
        }

        // 5. Paciente de prueba con cumplimiento de Habeas Data (Ley 1581 de 2012)
        $paciente = Paciente::create([
            'tipo_documento' => 'CC',
            'numero_documento' => '1018234567',
            'primer_nombre' => 'María',
            'segundo_nombre' => 'Fernanda',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Restrepo',
            'fecha_nacimiento' => '1995-06-15',
            'sexo_biologico' => 'F',
            'grupo_sanguineo_rh' => 'O+',
            'pais_residencia_codigo' => '170',
            'departamento_codigo' => '11',
            'municipio_codigo' => '11001',
            'zona_residencia' => '01_URBANO',
            'direccion_residencia' => 'Carrera 7 # 120-45 Apto 502',
            'telefono_principal' => '3209876543',
            'correo_electronico' => 'maria.gomez@gmail.com',
            'ocupacion' => 'Ingeniera de Sistemas',
            'estado_civil' => 'Soltera',
            'tipo_usuario_rips' => '01',
            'aseguradora_eps' => 'Sura EPS',
            'codigo_eps' => 'EPS010',
            'incapacidad' => 'NO',
            'acepta_tratamiento_datos' => true,
            'fecha_aceptacion_datos' => now()->subDays(5),
            'medio_aceptacion_datos' => 'FIRMA_DIGITAL',
            'ip_aceptacion_datos' => '190.158.42.10',
            'autorizacion_envio_mensajes' => true,
        ]);

        // 6. Maestro de Insumos y Lote con trazabilidad INVIMA
        $insumoResina = Insumo::create([
            'codigo_interno' => 'BIO-RES-3M-A2',
            'nombre_comercial' => 'Filtek Z350 XT Jeringa Color A2',
            'principio_activo_material' => 'Nanocompósito restaurador universal a base de Bis-GMA',
            'presentacion' => 'Jeringa x 4g',
            'unidad_medida' => 'JERINGA',
            'categoria' => 'BIOMATERIAL_RESTAURADOR',
            'stock_minimo' => 3,
            'stock_actual' => 10,
            'requiere_cadena_frio' => false,
            'activo' => true,
        ]);

        LoteInventario::create([
            'insumo_id' => $insumoResina->id,
            'lote_invima' => 'LOT-3M-8921B',
            'registro_sanitario_invima' => 'INVIMA 2018DM-0002145-R1',
            'fecha_fabricacion' => '2025-01-15',
            'fecha_vencimiento' => now()->addMonths(18)->format('Y-m-d'),
            'cantidad_recibida' => 10,
            'cantidad_disponible' => 10,
            'costo_unitario' => 125000,
            'estado' => 'VIGENTE',
        ]);
    }
}
