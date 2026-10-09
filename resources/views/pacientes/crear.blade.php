@extends('layouts.app')

@section('titulo', 'Admisión de Paciente y Habeas Data')

@section('contenido')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 1.5rem; margin: 0; color: #0f172a;">Admisión y Registro de Paciente</h1>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.875rem;">
            Captura demográfica MinSalud y autorización de tratamiento de datos personales (Ley 1581 de 2012).
        </p>
    </div>

    <form action="/pacientes" method="POST" id="form-paciente" class="tarjeta-blanca">
        @csrf
        <h3 style="color: #0284c7; margin-top: 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
            1. Datos de Identificación y Contacto
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Tipo de Documento *</label>
                <select name="tipo_documento" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="CC">Cédula de Ciudadanía (CC)</option>
                    <option value="TI">Tarjeta de Identidad (TI)</option>
                    <option value="RC">Registro Civil (RC)</option>
                    <option value="CE">Cédula de Extranjería (CE)</option>
                    <option value="PA">Pasaporte (PA)</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Número de Documento *</label>
                <input type="text" name="numero_documento" required placeholder="Ej: 1020304050" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Primer Nombre *</label>
                <input type="text" name="primer_nombre" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Segundo Nombre</label>
                <input type="text" name="segundo_nombre" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Primer Apellido *</label>
                <input type="text" name="primer_apellido" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Segundo Apellido</label>
                <input type="text" name="segundo_apellido" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Fecha de Nacimiento *</label>
                <input type="date" name="fecha_nacimiento" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Sexo Biológico *</label>
                <select name="sexo_biologico" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="F">Femenino (F)</option>
                    <option value="M">Masculino (M)</option>
                    <option value="INDETERMINADO">Indeterminado</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Teléfono Principal *</label>
                <input type="text" name="telefono_principal" required placeholder="Ej: 3101234567" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Correo Electrónico</label>
                <input type="email" name="correo_electronico" placeholder="paciente@ejemplo.com" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Dirección de Residencia *</label>
                <input type="text" name="direccion_residencia" required placeholder="Calle 100 # 15-20" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Tipo de Usuario (RIPS) *</label>
                <select name="tipo_usuario_rips" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <option value="05">05 - Particular</option>
                    <option value="01">01 - Contributivo Cotizante</option>
                    <option value="02">02 - Contributivo Beneficiario</option>
                    <option value="04">04 - Subsidiado</option>
                </select>
            </div>
        </div>

        <!-- MÓDULO OBLIGATORIO: LEY 1581 DE 2012 (HABEAS DATA) -->
        <h3 style="color: #16a34a; border-top: 2px solid #e2e8f0; padding-top: 16px; margin-top: 20px;">
            2. Autorización de Tratamiento de Datos Personales (Ley 1581 de 2012)
        </h3>
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 14px; font-size: 0.8rem; color: #475569; max-height: 120px; overflow-y: auto; margin-bottom: 14px;">
            En cumplimiento de la Ley Estatutaria 1581 de 2012 y el Decreto 1377 de 2013, autorizo de manera previa, expresa e informada a <strong>CENTRO ODONTOLÓGICO ODONTECH SAS</strong> para recolectar, almacenar y tratar mis datos personales y sensibles relacionados con mi salud oral, para fines de diagnóstico, historia clínica, facturación electrónica y reporte obligatorio a los sistemas del Ministerio de Salud (SISPRO / RIPS).
        </div>

        <div style="margin-bottom: 14px;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.875rem; color: #0f172a; cursor: pointer;">
                <input type="checkbox" name="acepta_tratamiento_datos" id="check-habeas" value="1" required style="width: 18px; height: 18px; accent-color: #16a34a;">
                He leído y acepto expresamente el Tratamiento de Datos Personales (Obligatorio para atención)
            </label>
        </div>

        <!-- Captura de Firma Digital en Pantalla Táctil -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">
                Firma Digital / Táctil del Paciente o Representante Legal:
            </label>
            <div style="border: 2px dashed #94a3b8; border-radius: 8px; background: white; width: 100%; max-width: 450px;">
                <canvas id="canvas-firma" width="450" height="140" style="display: block; cursor: crosshair;"></canvas>
            </div>
            <div style="margin-top: 6px;">
                <button type="button" id="btn-limpiar-firma" style="padding: 4px 10px; font-size: 0.75rem; background: #e2e8f0; border: none; border-radius: 4px; cursor: pointer;">
                    Limpiar Firma
                </button>
            </div>
            <input type="hidden" name="firma_digital_base64" id="input-firma-base64">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <a href="/dashboard" class="btn-accion btn-secundario" style="text-decoration: none; padding: 10px 18px;">Cancelar</a>
            <button type="submit" class="btn-accion btn-primario" style="background: #16a34a;">
                ✓ Registrar Paciente y Guardar Consentimiento
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Manejo de canvas táctil para captura de firma legal
    const canvas = document.getElementById('canvas-firma');
    const ctx = canvas.getContext('2d');
    let dibujando = false;

    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';

    function obtenerPosicion(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function iniciarDibujo(e) {
        dibujando = true;
        const pos = obtenerPosicion(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function dibujar(e) {
        if (!dibujando) return;
        const pos = obtenerPosicion(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function pararDibujo() {
        dibujando = false;
    }

    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mousemove', dibujar);
    canvas.addEventListener('mouseup', pararDibujo);

    canvas.addEventListener('touchstart', iniciarDibujo);
    canvas.addEventListener('touchmove', dibujar);
    canvas.addEventListener('touchend', pararDibujo);

    document.getElementById('btn-limpiar-firma').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    document.getElementById('form-paciente').addEventListener('submit', (e) => {
        document.getElementById('input-firma-base64').value = canvas.toDataURL();
    });
</script>
@endpush
