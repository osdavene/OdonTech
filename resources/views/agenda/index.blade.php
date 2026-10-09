@extends('layouts.app')

@section('titulo', 'Agenda & Sillones Dentales')

@section('contenido')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="margin: 0; color: #0f172a; font-size: 1.5rem;">Agenda de Citas & Sillones Odontológicos</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Gestión de turnos clínicos, asignación de unidades dentales y pase directo a consulta.</span>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <input type="date" value="{{ $fecha ?? date('Ymd') }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 500;">
        <button type="button" class="btn-accion btn-primario" onclick="alert('Formulario de nueva cita disponible para asignación.')" style="background: #0284c7; padding: 9px 16px;">
            + Agendar Cita
        </button>
    </div>
</div>

<!-- Cuadrícula de Sillones Odontológicos -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <!-- SILLÓN DENTAL 1 -->
    <div class="tarjeta-blanca" style="padding: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 14px;">
            <div>
                <strong style="color: #0369a1; font-size: 1.1rem;">💺 Sillón Odontológico 1</strong>
                <div style="font-size: 0.75rem; color: #64748b;">Unidad Eléctrica Principal - Consultorio 401</div>
            </div>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                Operativo
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse ($citas->where('unidad_sillon_numero', 1) as $cita)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0284c7; border-radius: 8px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                        ⏰ {{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('h:i A') }}
                    </span>
                    <span style="font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #e0f2fe; color: #0369a1;">
                        {{ $cita->estado }}
                    </span>
                </div>
                <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b;">
                    {{ $cita->paciente->nombre_completo ?? 'Paciente' }}
                </div>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                    Doc: {{ $cita->paciente->tipo_documento ?? 'CC' }} {{ $cita->paciente->numero_documento ?? '' }}
                </div>
                <div style="font-size: 0.8rem; color: #475569; margin-top: 6px; background: #ffffff; padding: 6px; border-radius: 4px; border: 1px dashed #cbd5e1;">
                    🎯 <strong>Motivo:</strong> {{ $cita->motivo_consulta }}
                </div>
                <div style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 8px;">
                    <a href="/clinico/estacion" class="btn-accion btn-primario" style="text-decoration: none; padding: 6px 12px; font-size: 0.75rem; background: #16a34a;">
                        🩺 Iniciar Atención Clínica
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #94a3b8; padding: 24px;">
                No hay más citas asignadas a este sillón para hoy.
            </div>
            @endforelse
        </div>
    </div>

    <!-- SILLÓN DENTAL 2 -->
    <div class="tarjeta-blanca" style="padding: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 14px;">
            <div>
                <strong style="color: #0369a1; font-size: 1.1rem;">💺 Sillón Odontológico 2</strong>
                <div style="font-size: 0.75rem; color: #64748b;">Unidad Hidráulica - Profilaxis & Cirugía</div>
            </div>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                Operativo
            </span>
        </div>

        <div style="text-align: center; color: #94a3b8; padding: 32px 16px;">
            <p>Disponible para asignación de turnos odontológicos.</p>
            <a href="/pacientes/nuevo" class="btn-accion btn-secundario" style="text-decoration: none; font-size: 0.8rem; padding: 6px 12px;">
                + Admitir Nuevo Paciente
            </a>
        </div>
    </div>
</div>
@endsection
