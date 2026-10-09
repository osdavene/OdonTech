# 🦷 OdonTech Colombia
### Sistema Integral de Gestión de Consultorios y Clínicas Odontológicas
#### *Health-Tech adaptado al marco normativo y sanitario de la República de Colombia*

[![PHP Version](https://img.shields.io/badge/PHP-8.4-777bb4?logo=php&logoColor=white)](https://php.net)
[![Framework](https://img.shields.io/badge/Laravel-12.x-ff2d20?logo=laravel&logoColor=white)](https://laravel.com)
[![Database](https://img.shields.io/badge/Database-PostgreSQL-336791?logo=postgresql&logoColor=white)](https://postgresql.org)
[![Normativa](https://img.shields.io/badge/Normativa-MinSalud%20%7C%20DIAN%20%7C%20INVIMA-0284c7)](#-cumplimiento-del-marco-legal-colombiano)
[![Licencia](https://img.shields.io/badge/Licencia-Privada%20%2F%20Comercial-0f172a)](#)

---

## 📋 Descripción General

**OdonTech** es una solución informática integral diseñada para automatizar los procesos administrativos, asistenciales, clínicos y de facturación electrónica de consultorios y clínicas odontológicas en Colombia. 

Desarrollado con una arquitectura relacional sólida bajo **Laravel** y **PostgreSQL**, el sistema garantiza el cumplimiento riguroso de las directrices emitidas por el **Ministerio de Salud y Protección Social (MinSalud)**, la **Dirección de Impuestos y Aduanas Nacionales (DIAN)**, el **Instituto Nacional de Vigilancia de Medicamentos y Alimentos (INVIMA)** y la **Superintendencia de Industria y Comercio (SIC - Habeas Data)**.

---

## ⚖️ Cumplimiento del Marco Legal Colombiano

| Entidad / Ley | Norma | Implementación en OdonTech |
| :--- | :--- | :--- |
| **SIC / MinTIC** | **Ley 1581 de 2012**<br>*(Habeas Data)* | • Aceptación obligatoria y explícita del tratamiento de datos personales para admisiones clínicas.<br>• Captura de firma digitalizada en pantalla táctil/canvas.<br>• Registro de auditoría forense inalterable (*Append-Only*) con sellado criptográfico **SHA-256** para toda lectura, modificación o consulta de pacientes. |
| **MinSalud** | **Resolución 1995 de 1999**<br>*(Historia Clínica)* | • Prohibición de enmendaduras, tachones o modificaciones posteriores.<br>• Modelo de **Evoluciones y Notas Aclaratorias cronológicas inmutables** con formato SOAP y sellado con tarjeta profesional ReTHUS.<br>• Odontograma y Periodontograma estandarizados. |
| **MinSalud** | **Resolución 839 de 2017**<br>*(Custodia y Retención)* | • Obligatoriedad de retención y custodia mínima de **15 años** (5 en archivo de gestión y 10 en archivo central).<br>• Prohibición estricta de borrado físico (`SoftDeletes` mandatorio en todas las tablas asistenciales). |
| **MinSalud / SISPRO** | **Resolución 2275 de 2023**<br>**Resolución 510 de 2024** | • Generación del archivo **RIPS en formato JSON estructurado oficial** para el sistema SISPRO.<br>• **Enlace Mandatorio con el CUFE** de la Factura Electrónica.<br>• Mapeo automatizado de catálogos maestros **CUPS** y **CIE-10 / CIE-11**.<br>• Procesamiento y recepción del **CUV** (Código Único de Validación). |
| **DIAN** | **Anexo Técnico 1.9**<br>*(Sector Salud)* | • Integración con Proveedor Tecnológico Autorizado (PTA - Factus API v2 u homologado).<br>• Emisión de Facturas Electrónicas de Venta en Salud con código de prestador **REPS** de 12 dígitos.<br>• Servicios odontológicos con exclusión de IVA (Art. 476 del Estatuto Tributario).<br>• Obtención de **CUFE**, código QR oficial, descarga de XML y Representación Gráfica imprimible. |
| **INVIMA** | **Normativa de Dispositivos y Reactivos** | • Trazabilidad por **Lote INVIMA**, **Registro Sanitario** y **Fecha de Vencimiento**.<br>• Inspección sanitaria y **bloqueo preventivo automático** de lotes caducados para uso en pacientes.<br>• Trazabilidad clínica de Tecnovigilancia vinculando el lote implantado o aplicado al ID del expediente. |

---

## 🚀 Módulos del Sistema

### 1. Seguridad, Roles y Auditoría Forense
- **Roles estrictos del sistema:**
  - `Administrador`: Acceso total gerencial, trazabilidad de logs, reportes y configuración.
  - `Odontólogo`: Diligenciamiento de historia clínica, odontograma FDI, periodontograma, consentimientos y registro de tarjeta profesional ReTHUS.
  - `Asistente`: Admisión de pacientes, agendamiento de citas, control de sala de espera e inventarios.
- **Trazabilidad Inalterable:** Registro append-only en `auditorias_accesos` con IP, User-Agent, fecha/hora y firma encadenada SHA-256.

### 2. Módulo Clínico y Odontología Asistencial
- **Historia Clínica Digital:** Anamnesis completa, antecedentes sistémicos, examen estomatológico por regiones orales y diagnósticos CIE-10/CIE-11.
- **Odontograma Interactivo FDI:**
  - Nomenclatura internacional de dos dígitos de la **Federación Dental Internacional (FDI)** para dentición permanente (11-48) y decidua/infantil (51-85).
  - Manipulación interactiva de las **5 superficies dentales** (Vestibular, Lingual/Palatina, Mesial, Distal y Oclusal/Incisal).
  - Convención cromática universal: Caries (Rojo), Obturación en Resina (Azul), Obturación en Amalgama (Gris plata), Sellante (Amarillo), Fractura (Verde) y Diente ausente (Cruz).
- **Periodontograma Milimétrico:**
  - Sondaje de **6 puntos clínicos universales** por diente.
  - Cálculo automático en tiempo real del **Nivel de Inserción Clínica ($CAL = \text{Sondaje} + \text{Margen}$)**.
  - Semáforo de severidad: $\le 3\text{ mm}$ (Salud/Leve), $4-5\text{ mm}$ (Moderada), $\ge 6\text{ mm}$ (Severa).
  - Cálculo de índice de sangrado al sondaje ($BOP$), movilidad dental (Grados 0-III) y lesiones de furca.
- **Consentimientos Informados:** Redacción vinculada al código CUPS, captura de firma táctil y control de revocatorias voluntarias.

### 3. Facturación Electrónica en Salud y RIPS
- Catálogos maestros completos de **CUPS** (procedimientos) y **CIE-10 / CIE-11** (diagnósticos).
- Generación de RIPS JSON según Resoluciones 2275 de 2023 y 510 de 2024.
- Flujo integrado de emisión DIAN:
  1. `[Enviar DIAN]` $\rightarrow$ Envío al PTA y recepción del CUFE.
  2. `[Armar RIPS]` $\rightarrow$ Inclusión automática del CUFE en el payload JSON.
  3. `[Validar SISPRO]` $\rightarrow$ Transmisión y recepción del código CUV.
- Representación Gráfica oficial de la factura lista para impresión o guardado en PDF con código QR y detalles del prestador REPS.

### 4. Inventario Odontológico y Control INVIMA
- Control de insumos odontológicos, biomateriales y anestésicos.
- Campos obligatorios: `lote_invima`, `registro_sanitario_invima`, `fecha_vencimiento` y `stock_minimo`.
- Algoritmo de inspección sanitaria con técnica **FEFO** (*First Expired, First Out*).
- Disparo de alertas críticas y bloqueo automático en el despachador de materiales si un producto se encuentra caducado.

### 5. Agenda y Citas Odontológicas
- Agendamiento por fecha, hora, duración y asignación de unidad/sillón dental.
- Detección de traslapes en sillón o profesional.
- Flujo de sala de espera y transición en **1 clic a la atención médica** vinculando el expediente activo.

---

## 🛠️ Tecnologías Utilizadas

- **Lenguaje:** PHP 8.4+
- **Framework:** Laravel 12.x / Boost
- **Base de Datos:** PostgreSQL 15+ (Diseño puramente relacional con claves foráneas, JSONB e integridad referencial estricta)
- **Frontend:** HTML5, CSS3, JavaScript modular nativo (sin dependencias pesadas), SVG vectorial interactivo y Blade Templates.
- **Herramientas de Calidad:** PHPUnit, Laravel Boost, Git.

---

## 📦 Instalación y Configuración Local

### 1. Clonar el repositorio
```bash
git clone https://github.com/osdavene/OdonTech.git
cd OdonTech
```

### 2. Instalar dependencias de PHP
```bash
composer install
```

### 3. Configurar variables de entorno
Copia el archivo de ejemplo `.env.example` a `.env`:
```bash
cp .env.example .env
```

Configura los datos de tu conexión PostgreSQL y los datos del prestador de salud en el archivo `.env`:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=odontech
DB_USERNAME=postgres
DB_PASSWORD=7420

# Configuración DIAN & Habilitación MinSalud
DIAN_PTA_PROVEEDOR=factus
DIAN_AMBIENTE=sandbox
ODONTECH_PRESTADOR_NIT=901234567
ODONTECH_PRESTADOR_DV=1
ODONTECH_PRESTADOR_RAZON_SOCIAL="CENTRO ODONTOLOGICO ODONTECH SAS"
ODONTECH_PRESTADOR_CODIGO_REPS=110010000001
ODONTECH_PRESTADOR_MUNICIPIO=11001
```

Genera la clave de cifrado de la aplicación:
```bash
php artisan key:generate
```

### 4. Ejecutar migraciones y poblar la base de datos (Seeders)
```bash
php artisan migrate --seed
```

### 5. Iniciar el servidor local
```bash
php artisan serve
```
El sistema estará disponible en: **`http://localhost:8000`**

---

## 👥 Credenciales de Acceso por Defecto (Seeders)

La base de datos incluye tres usuarios maestros para pruebas, correspondientes a los 3 roles normativos:

| Rol | Correo Electrónico | Contraseña | Detalles / Registro |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@odontech.com.co` | `Admin2026*` | Gerencia y Administración en Salud |
| **Odontólogo** | `odontologo@odontech.com.co` | `Odonto2026*` | Tarjeta Profesional: `TP-ODO-54891` (ReTHUS) |
| **Asistente** | `asistente@odontech.com.co` | `Asistente2026*` | Auxiliar de Salud Oral |

---

## 🧪 Pruebas Automatizadas

El proyecto incluye una suite de pruebas de integración que valida el cumplimiento estricto de las leyes colombianas:
```bash
php artisan test
```

### Cobertura de Pruebas:
- `✓` **Habeas Data obligatorio:** Rechazo de apertura de historia si el paciente no tiene autorización expresa (Ley 1581 de 2012).
- `✓` **Custodia legal y SoftDeletes:** Comprobación de retención documental y adición de evoluciones inmutables (Res. 1995/1999 y Res. 839/2017).
- `✓` **Facturación DIAN y RIPS:** Validación de obtención de CUFE y enlace mandatorio en el JSON de RIPS (Res. 2275/2023).
- `✓` **Bloqueo INVIMA:** Detección de lotes vencidos e impedimento de su uso en pacientes.
- `✓` **Agenda clínica:** Agendamiento sin traslapes y transición a atención en 1 clic.
- `✓` **Periodontograma:** Sondaje de 6 puntos y cálculo automatizado del nivel de inserción clínica ($CAL$).

---

## 📂 Estructura Principal del Proyecto

```
OdonTech/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Agenda/CitaController.php
│   │   │   ├── Clinico/HistoriaClinicaController.php
│   │   │   ├── Clinico/OdontogramaController.php
│   │   │   ├── Clinico/PeriodontogramaController.php
│   │   │   ├── Clinico/ConsentimientoController.php
│   │   │   ├── Facturacion/FacturacionRipsController.php
│   │   │   ├── Inventario/InventarioInvimaController.php
│   │   │   └── Seguridad/AutenticacionController.php
│   │   └── Middleware/
│   │       ├── AuditoriaHabeasDataMiddleware.php
│   │       └── VerificarRol.php
│   ├── Models/
│   │   ├── Usuario.php, Rol.php, AuditoriaAcceso.php
│   │   ├── Paciente.php, HistoriaClinica.php, EvolucionClinica.php
│   │   ├── Odontograma.php, OdontogramaDiente.php
│   │   ├── Periodontograma.php, PeriodontogramaDiente.php
│   │   ├── ConsentimientoInformado.php, Cita.php
│   │   ├── CatalogoCups.php, CatalogoCie.php
│   │   ├── Factura.php, FacturaDetalle.php, RipsTransaccion.php
│   │   └── Insumo.php, LoteInventario.php, MovimientoInventario.php, AlertaInventario.php
│   └── Services/
│       ├── Auditoria/AuditoriaService.php
│       ├── Dian/DianPtaFacturacionService.php
│       ├── Inventario/ControlVencimientoInvimaService.php
│       └── Rips/RipsJsonBuilderService.php
├── database/
│   ├── migrations/ (Todas las tablas en español con integridad referencial)
│   └── seeders/DatabaseSeeder.php
├── public/
│   ├── css/ (odontograma.css, periodontograma.css)
│   └── js/ (odontograma.js, periodontograma.js)
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── dashboard.blade.php
│   ├── pacientes/crear.blade.php (Firma digital Habeas Data)
│   ├── odontograma/interactivo.blade.php
│   └── facturacion/ (index.blade.php, representacion_grafica.blade.php)
├── routes/web.php
└── tests/Feature/OdonTechNormativaTest.php
```

---

## 📄 Licencia

Este software ha sido diseñado con fines de gestión médica y odontológica privada. Todos los derechos reservados.
Desarrollado bajo los estándares oficiales de MinSalud, DIAN e INVIMA Colombia.
