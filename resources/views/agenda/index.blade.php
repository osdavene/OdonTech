@extends('layouts.app')

@section('titulo', 'Agenda & Sillones Dentales')

@push('estilos')
<style>
    /* Estilos del Modal de Agendamiento */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .modal-overlay.activo {
        display: flex;
    }
    .modal-contenedor {
        background: #ffffff;
        border-radius: 14px;
        width: 100%;
        max-width: 600px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: aparecerModal 0.25s ease-out;
    }
    @keyframes aparecerModal {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-cabecera {
        background: #0284c7;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-cuerpo {
        padding: 22px;
        max-height: 80vh;
        overflow-y: auto;
    }
    .modal-pie {
        padding: 14px 22px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-cerrar-modal {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 1.5rem;
        cursor: pointer;
        line-height: 1;
        opacity: 0.85;
    }
    .btn-cerrar-modal:hover {
        opacity: 1;
    }
    .campo-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }
    .campo-grid.completo {
        grid-template-columns: 1fr;
    }
    .etiqueta-form {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
    }
    .control-form {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.85rem;
        color: #0f172a;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .control-form:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
</style>
@endpush

@section('contenido')
<!-- Mensajes de Estado -->
@if (session('exito'))
    <div style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
        ✓ {{ session('exito') }}
    </div>
@endif

@if ($errors->any())
    <div style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
        <strong>⚠️ Hubo un inconveniente al agendar:</strong>
        <ul style="margin: 6px 0 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Barra Superior de Agenda -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="margin: 0; color: #0f172a; font-size: 1.5rem;">Agenda de Citas & Sillones Odontológicos</h2>
        <span style="font-size: 0.85rem; color: #64748b;">Gestión de turnos clínicos, asignación de unidades dentales y pase directo a consulta.</span>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <label for="filtro-fecha" style="font-size: 0.85rem; font-weight: 600; color: #475569;">Fecha:</label>
        <input type="date" id="filtro-fecha" value="{{ $fecha ?? date('Y-m-d') }}" onchange="window.location.href = '/agenda/citas?fecha=' + this.value" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600; color: #0f172a; outline: none;">
        
        <button type="button" class="btn-accion btn-primario" onclick="abrirModalCita()" style="background: #0284c7; padding: 9px 16px;">
            + Agendar Cita
        </button>
    </div>
</div>

<!-- Cuadrícula de Sillones Odontológicos -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
    <!-- SILLÓN DENTAL 1 -->
    <div class="tarjeta-blanca" style="padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
            <div>
                <strong style="color: #0369a1; font-size: 1.15rem;">💺 Sillón Odontológico 1</strong>
                <div style="font-size: 0.75rem; color: #64748b;">Unidad Principal - Odontología General & Rehabilitación</div>
            </div>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                Operativo
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @php $citasSillon1 = $citas->where('unidad_sillon_numero', 1); @endphp
            @forelse ($citasSillon1 as $cita)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 5px solid #0284c7; border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                        ⏰ {{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('h:i A') }}
                    </span>
                    <span style="font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; background: #e0f2fe; color: #0369a1;">
                        {{ $cita->estado }}
                    </span>
                </div>
                <div style="font-size: 0.95rem; font-weight: 600; color: #1e293b;">
                    {{ $cita->paciente->nombre_completo ?? 'Paciente Registrado' }}
                </div>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px;">
                    <strong>{{ $cita->paciente->tipo_documento ?? 'CC' }}:</strong> {{ $cita->paciente->numero_documento ?? '000000' }} |
                    <strong>Odontólogo:</strong> {{ $cita->odontologo->nombre_completo ?? 'Dr. Carlos Morales' }}
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 8px; background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                    🎯 <strong>Motivo:</strong> {{ $cita->motivo_consulta }}
                    @if ($cita->cupsPropuesto)
                        <div style="font-size: 0.75rem; color: #0284c7; margin-top: 4px;">
                            Procedimiento CUPS: {{ $cita->cupsPropuesto->codigo }} - {{ $cita->cupsPropuesto->descripcion }}
                        </div>
                    @endif
                </div>
                <div style="margin-top: 12px; display: flex; justify-content: flex-end; gap: 8px;">
                    <a href="/clinico/estacion" class="btn-accion btn-primario" style="text-decoration: none; padding: 7px 14px; font-size: 0.8rem; background: #16a34a;">
                        🩺 Iniciar Atención Clínica
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #94a3b8; padding: 32px 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                No hay citas programadas en este sillón para esta fecha.
                <div style="margin-top: 10px;">
                    <button type="button" class="btn-accion btn-secundario" onclick="abrirModalCita(1)" style="font-size: 0.8rem; padding: 6px 12px;">
                        + Programar en Sillón 1
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- SILLÓN DENTAL 2 -->
    <div class="tarjeta-blanca" style="padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
            <div>
                <strong style="color: #0369a1; font-size: 1.15rem;">💺 Sillón Odontológico 2</strong>
                <div style="font-size: 0.75rem; color: #64748b;">Unidad Secundaria - Endodoncia & Cirugía Oral</div>
            </div>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                Operativo
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @php $citasSillon2 = $citas->where('unidad_sillon_numero', 2); @endphp
            @forelse ($citasSillon2 as $cita)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 5px solid #059669; border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                        ⏰ {{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($cita->fecha_hora_fin)->format('h:i A') }}
                    </span>
                    <span style="font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; background: #ecfdf5; color: #065f46;">
                        {{ $cita->estado }}
                    </span>
                </div>
                <div style="font-size: 0.95rem; font-weight: 600; color: #1e293b;">
                    {{ $cita->paciente->nombre_completo ?? 'Paciente Registrado' }}
                </div>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px;">
                    <strong>{{ $cita->paciente->tipo_documento ?? 'CC' }}:</strong> {{ $cita->paciente->numero_documento ?? '000000' }} |
                    <strong>Odontólogo:</strong> {{ $cita->odontologo->nombre_completo ?? 'Dr. Carlos Morales' }}
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 8px; background: #ffffff; padding: 8px 10px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                    🎯 <strong>Motivo:</strong> {{ $cita->motivo_consulta }}
                </div>
                <div style="margin-top: 12px; display: flex; justify-content: flex-end; gap: 8px;">
                    <a href="/clinico/estacion" class="btn-accion btn-primario" style="text-decoration: none; padding: 7px 14px; font-size: 0.8rem; background: #16a34a;">
                        🩺 Iniciar Atención Clínica
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #94a3b8; padding: 32px 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                No hay citas programadas en este sillón para esta fecha.
                <div style="margin-top: 10px;">
                    <button type="button" class="btn-accion btn-secundario" onclick="abrirModalCita(2)" style="font-size: 0.8rem; padding: 6px 12px;">
                        + Programar en Sillón 2
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL PARA AGENDAR NUEVA CITA ODONTOLÓGICA -->
<div class="modal-overlay" id="modal-agendar-cita" onclick="if(event.target === this) cerrarModalCita()">
    <div class="modal-contenedor">
        <div class="modal-cabecera">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.25rem;">📅</span>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Agendar Cita Odontológica</h3>
            </div>
            <button type="button" class="btn-cerrar-modal" onclick="cerrarModalCita()">✕</button>
        </div>

        <form action="{{ route('agenda.citas.agendar') }}" method="POST" id="form-agendar-cita">
            @csrf
            <div class="modal-cuerpo">
                <!-- Paciente y Odontólogo -->
                <div class="campo-grid">
                    <div>
                        <label class="etiqueta-form" for="paciente_id">Paciente Autorizado *</label>
                        <select name="paciente_id" id="paciente_id" class="control-form" required>
                            @forelse ($pacientes ?? [] as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre_completo }} ({{ $p->tipo_documento }} {{ $p->numero_documento }})</option>
                            @empty
                                <option value="" disabled selected>No hay pacientes registrados</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label class="etiqueta-form" for="odontologo_id">Odontólogo Tratante *</label>
                        <select name="odontologo_id" id="odontologo_id" class="control-form" required>
                            @forelse ($odontologos ?? [] as $odo)
                                <option value="{{ $odo->id }}">{{ $odo->nombre_completo }} (ReTHUS: {{ $odo->registro_profesional ?? 'General' }})</option>
                            @empty
                                <option value="" disabled selected>No hay odontólogos disponibles</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <!-- Sillón Dental y Duración -->
                <div class="campo-grid">
                    <div>
                        <label class="etiqueta-form" for="unidad_sillon_numero">Sillón Dental Asignado *</label>
                        <select name="unidad_sillon_numero" id="unidad_sillon_numero" class="control-form" required>
                            <option value="1">💺 Sillón 1 - Principal (Consultorio 401)</option>
                            <option value="2">💺 Sillón 2 - Procedimientos & Cirugía</option>
                            <option value="3">💺 Sillón 3 - Profilaxis & Valoración</option>
                        </select>
                    </div>

                    <div>
                        <label class="etiqueta-form" for="duracion_minutos">Duración Estimada *</label>
                        <select name="duracion_minutos" id="duracion_minutos" class="control-form" required>
                            <option value="30">30 minutos (Control / Profilaxis)</option>
                            <option value="45" selected>45 minutos (Restauración / Consulta)</option>
                            <option value="60">60 minutos (Endodoncia / Cirugía)</option>
                            <option value="90">90 minutos (Rehabilitación Compleja)</option>
                        </select>
                    </div>
                </div>

                <!-- Fecha y Hora de Inicio -->
                <div class="campo-grid completo">
                    <div>
                        <label class="etiqueta-form" for="fecha_hora_inicio">Fecha y Hora de la Cita *</label>
                        @php
                            $siguienteHora = \Carbon\Carbon::now()->addHour()->setMinute(0)->format('Y-m-d\TH:i');
                        @endphp
                        <input type="datetime-local" name="fecha_hora_inicio" id="fecha_hora_inicio" class="control-form" value="{{ $siguienteHora }}" required>
                    </div>
                </div>

                <!-- Procedimiento CUPS Sugerido -->
                <div class="campo-grid completo">
                    <div>
                        <label class="etiqueta-form" for="cups_propuesto_id">Procedimiento CUPS Previsto (Opcional)</label>
                        <select name="cups_propuesto_id" id="cups_propuesto_id" class="control-form">
                            <option value="">-- Seleccione procedimiento CUPS si aplica --</option>
                            @foreach ($cupsList ?? [] as $cups)
                                <option value="{{ $cups->id }}">{{ $cups->codigo }} - {{ $cups->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Motivo de Consulta -->
                <div class="campo-grid completo">
                    <div>
                        <label class="etiqueta-form" for="motivo_consulta">Motivo de Consulta Clínico *</label>
                        <input type="text" name="motivo_consulta" id="motivo_consulta" class="control-form" required placeholder="Ej: Dolor en molar superior, limpieza dental, valoración estética...">
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="campo-grid completo">
                    <div>
                        <label class="etiqueta-form" for="observaciones">Observaciones o Instrucciones Previas</label>
                        <textarea name="observaciones" id="observaciones" rows="2" class="control-form" placeholder="Ej: Paciente refiere alergia a penicilina, requiere confirmación telefónica..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-pie">
                <button type="button" class="btn-accion btn-secundario" onclick="cerrarModalCita()" style="padding: 8px 16px;">
                    Cancelar
                </button>
                <button type="submit" class="btn-accion btn-primario" style="background: #0284c7; padding: 8px 18px;">
                    ✓ Confirmar y Agendar Cita
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function abrirModalCita(sillonNumero = 1) {
        const modal = document.getElementById('modal-agendar-cita');
        if (sillonNumero) {
            document.getElementById('unidad_sillon_numero').value = sillonNumero;
        }
        modal.classList.add('activo');
        document.getElementById('motivo_consulta').focus();
    }

    function cerrarModalCita() {
        const modal = document.getElementById('modal-agendar-cita');
        modal.classList.remove('activo');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarModalCita();
        }
    });
</script>
@endpush
