@extends('layouts.app')

@section('titulo', 'Estación Clínica Odontológica Unificada')

@push('estilos')
<style>
    .cabecera-paciente-fija {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .datos-paciente-flex {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .avatar-paciente {
        width: 48px;
        height: 48px;
        background: #e0f2fe;
        color: #0369a1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: bold;
    }
    .insignia-alerta {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-size: 0.75rem;
        font-weight: bold;
        padding: 3px 8px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    /* Sistema de Pestañas */
    .barra-pestanas {
        display: flex;
        gap: 4px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 20px;
        overflow-x: auto;
    }
    .btn-pestana {
        padding: 12px 18px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        font-size: 0.9rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-pestana:hover {
        color: #0284c7;
        background: #f8fafc;
    }
    .btn-pestana.activa {
        color: #0284c7;
        border-bottom-color: #0284c7;
        background: #f0f9ff;
    }
    .contenido-pestana {
        display: none;
    }
    .contenido-pestana.activa {
        display: block;
    }
</style>
@endpush

@section('contenido')
<!-- Cabecera del Paciente con Alertas Médicas en Tiempo Real -->
<div class="cabecera-paciente-fija">
    <div class="datos-paciente-flex">
        <div class="avatar-paciente">
            {{ substr($paciente?->primer_nombre ?? 'P', 0, 1) }}{{ substr($paciente?->primer_apellido ?? 'A', 0, 1) }}
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h2 style="margin: 0; font-size: 1.25rem; color: #0f172a;">{{ $paciente?->nombre_completo ?? 'Paciente General' }}</h2>
                <span class="insignia-normativa" style="background: #dcfce7; color: #166534; border-color: #bbf7d0;">
                    ✓ Habeas Data Autorizado
                </span>
            </div>
            <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">
                <strong>{{ $paciente?->tipo_documento ?? 'CC' }}:</strong> {{ $paciente?->numero_documento ?? '00000000' }} |
                <strong>Edad:</strong> {{ $paciente ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 30 }} años |
                <strong>Grupo Sanguíneo:</strong> {{ $paciente?->grupo_sanguineo_rh ?? 'O+' }} |
                <strong>EPS:</strong> {{ $paciente?->aseguradora_eps ?? 'Particular' }} |
                <strong>Expediente:</strong> {{ $historia?->numero_historia ?? 'HC-BORRADOR' }}
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <span class="insignia-alerta" title="Alerta médica sistémica detectada en anamnesis">
            ⚠️ ALERTA: Alergia a Penicilina
        </span>
        <a href="/facturacion/panel" class="btn-accion btn-primario" style="text-decoration: none; padding: 8px 14px; font-size: 0.8rem; background: #0284c7;">
            💳 Proceder a Cobro & RIPS
        </a>
    </div>
</div>

<!-- Barra de Navegación por Pestañas -->
<div class="barra-pestanas">
    <button type="button" class="btn-pestana activa" data-tab="tab-odontograma">
        🦷 Odontograma FDI
    </button>
    <button type="button" class="btn-pestana" data-tab="tab-periodontograma">
        📏 Periodontograma
    </button>
    <button type="button" class="btn-pestana" data-tab="tab-evolucion">
        📝 Evolución SOAP & CUPS
    </button>
    <button type="button" class="btn-pestana" data-tab="tab-prescripcion">
        💊 Prescripción Farmacológica
    </button>
    <button type="button" class="btn-pestana" data-tab="tab-consentimiento">
        ✍️ Consentimiento Informado
    </button>
    <button type="button" class="btn-pestana" data-tab="tab-historial">
        📜 Historial Cronológico
    </button>
</div>

<!-- PESTAÑA 1: ODONTOGRAMA FDI INTERACTIVO -->
<div class="contenido-pestana activa" id="tab-odontograma">
    <div class="tarjeta-blanca">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div>
                <h3 style="margin: 0; color: #0284c7;">Odontograma Anatómico FDI (Adulto & Mixto)</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Haga clic en las 5 superficies anatómicas para registrar caries, restauraciones o sellantes.</span>
            </div>
            <button type="button" id="btn-guardar-odonto-estacion" class="btn-accion btn-primario" style="padding: 6px 14px; font-size: 0.8rem;">
                💾 Guardar Estado Odontograma
            </button>
        </div>
        <div id="estacion-odontograma-canvas"></div>
    </div>
</div>

<!-- PESTAÑA 2: PERIODONTOGRAMA MILIMÉTRICO -->
<div class="contenido-pestana" id="tab-periodontograma">
    <div class="tarjeta-blanca">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div>
                <h3 style="margin: 0; color: #0284c7;">Sondaje Periodontal Milimétrico</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Evaluación de 6 puntos por diente, cálculo automático de inserción clínica (CAL) y sangrado.</span>
            </div>
            <button type="button" id="btn-guardar-perio-estacion" class="btn-accion btn-primario" style="padding: 6px 14px; font-size: 0.8rem;">
                💾 Guardar Periodontograma
            </button>
        </div>
        <div id="estacion-periodontograma-canvas"></div>
    </div>
</div>

<!-- PESTAÑA 3: EVOLUCIÓN CLÍNICA FORMATO SOAP -->
<div class="contenido-pestana" id="tab-evolucion">
    <div class="tarjeta-blanca">
        <h3 style="margin-top: 0; color: #0284c7; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
            Registro de Evolución Clínica Inmutable (Resolución 1995 de 1999)
        </h3>

        <form id="form-evolucion-soap" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px; color: #0369a1;">
                    S (Subjetivo): Motivo / Síntomas referidos en la sesión
                </label>
                <textarea id="soap-s" rows="3" placeholder="Paciente refiere dolor leve en molar superior al frío..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px; color: #0369a1;">
                    O (Objetivo): Hallazgos clínicos a la exploración
                </label>
                <textarea id="soap-o" rows="3" placeholder="Caries cavitada activa en superficie oclusal diente 16..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px; color: #0369a1;">
                    A (Análisis): Juicio diagnóstico del odontólogo
                </label>
                <textarea id="soap-a" rows="3" required placeholder="Caries de la dentina (K021), pulpa vital..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px; color: #0369a1;">
                    P (Plan): Procedimiento ejecutado y conducta
                </label>
                <textarea id="soap-p" rows="3" required placeholder="Aislamiento absoluto, eliminación de tejido cariado, obturación en resina..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>

            <!-- Procedimiento CUPS y Diente -->
            <div style="grid-column: span 2; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Procedimiento CUPS Realizado *</label>
                    <select id="soap-cups" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        @foreach ($cupsList ?? [] as $cups)
                            <option value="{{ $cups->id }}">{{ $cups->codigo }} - {{ $cups->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Diente FDI Tratado</label>
                    <input type="number" id="soap-diente" min="11" max="85" placeholder="Ej: 16" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Superficie Dental</label>
                    <input type="text" id="soap-superficie" placeholder="Ej: Oclusal (O)" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Próxima Cita Sugerida</label>
                    <input type="date" id="soap-proxima-cita" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div style="grid-column: span 2; text-align: right;">
                <button type="submit" class="btn-accion btn-primario" style="background: #0284c7; padding: 10px 20px;">
                    ✓ Sellar y Guardar Evolución Inmutable (ReTHUS)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PESTAÑA 4: PRESCRIPCIÓN MÉDICA ODONTOLÓGICA -->
<div class="contenido-pestana" id="tab-prescripcion">
    <div class="tarjeta-blanca">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="margin: 0; color: #0284c7;">Prescripción Farmacológica (Decreto 2200 de 2005)</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Emisión de receta médica con sello digital ReTHUS y plantilla imprimible oficial.</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" id="btn-agregar-receta-rapida" class="btn-accion btn-secundario" style="font-size: 0.8rem; padding: 6px 12px;">
                    + Posología Rápida (Amoxicilina + Ibuprofeno)
                </button>
            </div>
        </div>

        <form id="form-prescripcion">
            <div id="lista-medicamentos-receta" style="margin-bottom: 16px;">
                <!-- Renglones de medicamentos -->
                <div class="renglon-medicamento" style="display: grid; grid-template-columns: 2fr 1fr 1fr 2fr 1fr 40px; gap: 10px; align-items: center; background: #f8fafc; padding: 10px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0;">
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600;">Medicamento</label>
                        <input type="text" class="med-nombre" value="Amoxicilina" placeholder="Nombre genérico" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600;">Concentración</label>
                        <input type="text" class="med-conc" value="500mg" placeholder="500mg" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600;">Forma</label>
                        <input type="text" class="med-forma" value="Cápsulas" placeholder="Cápsulas" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600;">Posología / Frecuencia</label>
                        <input type="text" class="med-dosis" value="Tomar 1 cápsula cada 8 horas por 7 días" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600;">Cantidad</label>
                        <input type="text" class="med-cant" value="21 cápsulas" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <button type="button" onclick="this.closest('.renglon-medicamento').remove()" style="margin-top: 14px; background: none; border: none; color: #ef4444; font-size: 1.1rem; cursor: pointer;">✕</button>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Recomendaciones Generales de Cuidado Oral</label>
                <textarea id="receta-recomendaciones" rows="2" placeholder="Tomar los medicamentos después de las comidas. Mantener higiene bucal rigurosa..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" class="btn-accion btn-primario" style="background: #16a34a;">
                    🖨️ Emitir y Generar Receta Médica Imprimible
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PESTAÑA 5: CONSENTIMIENTO INFORMADO -->
<div class="contenido-pestana" id="tab-consentimiento">
    <div class="tarjeta-blanca">
        <h3 style="margin-top: 0; color: #0284c7; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
            Consentimiento Informado para Procedimiento Odontológico
        </h3>
        <p style="font-size: 0.85rem; color: #64748b;">
            El paciente debe manifestar su aceptación previa y expresa antes de iniciar cualquier procedimiento invasivo, quirúrgico o que requiera anestesia local.
        </p>

        <form id="form-consentimiento-estacion">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Título del Procedimiento *</label>
                <input type="text" id="consent-titulo" value="Procedimiento Restaurador con Resina y Aplicación de Anestesia Local" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Riesgos, Molestias y Beneficios Informados</label>
                <textarea id="consent-cuerpo" rows="4" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">Se explica al paciente la naturaleza del procedimiento de restauración dental, los posibles riesgos inherentes a la administración de anestesia local (sensación de adormecimiento, hematoma leve) y la necesidad de mantener el aislamiento del campo operatorio. El paciente declara haber comprendido y formula preguntas que fueron aclaradas satisfactoriamente.</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-accion btn-primario" style="background: #0369a1;">
                    ✓ Generar Consentimiento para Firma
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PESTAÑA 6: HISTORIAL CRONOLÓGICO DE EVOLUCIONES -->
<div class="contenido-pestana" id="tab-historial">
    <div class="tarjeta-blanca">
        <h3 style="margin-top: 0; color: #0284c7; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
            Historial Cronológico de Atenciones Clínicas (Append-Only)
        </h3>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($historia->evoluciones ?? [] as $evo)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 8px;">
                    <div>
                        <strong style="color: #0284c7;">{{ $evo->tipo_registro }}</strong> - {{ $evo->fecha_hora_atencion->format('d/m/Y H:i') }}
                        @if ($evo->diente_fdi)
                            <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; margin-left: 6px;">
                                Diente {{ $evo->diente_fdi }}
                            </span>
                        @endif
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        Odontólogo: <strong>{{ $evo->odontologo->nombre_completo ?? 'Dr. Carlos Morales' }}</strong> (ReTHUS: {{ $evo->odontologo->registro_profesional ?? 'TP-ODO-54891' }})
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #334155; line-height: 1.5;">
                    @if ($evo->descripcion_subjetiva)
                        <div><strong>S:</strong> {{ $evo->descripcion_subjetiva }}</div>
                    @endif
                    @if ($evo->descripcion_objetiva)
                        <div><strong>O:</strong> {{ $evo->descripcion_objetiva }}</div>
                    @endif
                    <div><strong>A:</strong> {{ $evo->analisis_diagnostico }}</div>
                    <div><strong>P:</strong> {{ $evo->plan_procedimiento }}</div>
                </div>
                <div style="font-size: 8px; color: #94a3b8; font-family: monospace; margin-top: 6px;">
                    Sellado Inmutable: {{ substr($evo->firma_digital_hash, 0, 32) }}...
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #64748b; padding: 24px;">
                No hay evoluciones clínicas previas registradas para este expediente.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="/js/odontograma.js"></script>
<script src="/js/periodontograma.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const historiaId = {{ $historia?->id ?? 'null' }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // 1. Manejo de Pestañas
        document.querySelectorAll('.btn-pestana').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.btn-pestana').forEach(b => b.classList.remove('activa'));
                document.querySelectorAll('.contenido-pestana').forEach(c => c.classList.remove('activa'));

                btn.classList.add('activa');
                const targetId = btn.getAttribute('data-tab');
                document.getElementById(targetId).classList.add('activa');
            });
        });

        // 2. Inicializar Odontograma FDI en la estación
        const odontogramaApp = new OdontogramaInteractivo('estacion-odontograma-canvas', {
            historiaClinicaId: historiaId,
            tipoOdontograma: 'EVOLUCION'
        });

        document.getElementById('btn-guardar-odonto-estacion').addEventListener('click', async () => {
            try {
                const res = await odontogramaApp.guardarEnServidor(`/clinico/historias/${historiaId}/odontograma`);
                alert(res.mensaje || 'Odontograma guardado correctamente.');
            } catch (e) {
                alert('Error al guardar odontograma.');
            }
        });

        // 3. Inicializar Periodontograma en la estación
        const periodontogramaApp = new PeriodontogramaInteractivo('estacion-periodontograma-canvas', {
            historiaClinicaId: historiaId
        });

        document.getElementById('btn-guardar-perio-estacion').addEventListener('click', async () => {
            const payload = periodontogramaApp.obtenerPayloadJSON();
            try {
                const res = await fetch(`/clinico/historias/${historiaId}/periodontograma`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                alert(data.mensaje || 'Periodontograma guardado.');
            } catch (e) {
                alert('Error al guardar periodontograma.');
            }
        });

        // 4. Guardar Evolución SOAP
        document.getElementById('form-evolucion-soap').addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                tipo_registro: 'EVOLUCION_TRATAMIENTO',
                descripcion_subjetiva: document.getElementById('soap-s').value,
                descripcion_objetiva: document.getElementById('soap-o').value,
                analisis_diagnostico: document.getElementById('soap-a').value,
                plan_procedimiento: document.getElementById('soap-p').value,
                cups_id: document.getElementById('soap-cups').value,
                diente_fdi: document.getElementById('soap-diente').value || null,
                superficie_diente: document.getElementById('soap-superficie').value || null,
                proxima_cita_sugerida: document.getElementById('soap-proxima-cita').value || null
            };

            try {
                const res = await fetch(`/clinico/historias/${historiaId}/evoluciones`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                alert(data.mensaje || 'Evolución asentada.');
                window.location.reload();
            } catch (err) {
                alert('Error al asentar evolución clínica.');
            }
        });

        // 5. Prescripción Rápida
        document.getElementById('btn-agregar-receta-rapida').addEventListener('click', () => {
            const lista = document.getElementById('lista-medicamentos-receta');
            const nuevo = document.createElement('div');
            nuevo.className = 'renglon-medicamento';
            nuevo.style = 'display: grid; grid-template-columns: 2fr 1fr 1fr 2fr 1fr 40px; gap: 10px; align-items: center; background: #f8fafc; padding: 10px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0;';
            nuevo.innerHTML = `
                <div><label style="font-size: 0.75rem; font-weight: 600;">Medicamento</label><input type="text" class="med-nombre" value="Ibuprofeno" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                <div><label style="font-size: 0.75rem; font-weight: 600;">Concentración</label><input type="text" class="med-conc" value="600mg" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                <div><label style="font-size: 0.75rem; font-weight: 600;">Forma</label><input type="text" class="med-forma" value="Tabletas" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                <div><label style="font-size: 0.75rem; font-weight: 600;">Posología</label><input type="text" class="med-dosis" value="1 tableta cada 8 horas si hay dolor" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                <div><label style="font-size: 0.75rem; font-weight: 600;">Cantidad</label><input type="text" class="med-cant" value="10 tabletas" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                <div><button type="button" onclick="this.closest('.renglon-medicamento').remove()" style="margin-top: 14px; background: none; border: none; color: #ef4444; font-size: 1.1rem; cursor: pointer;">✕</button></div>
            `;
            lista.appendChild(nuevo);
        });

        // 6. Guardar Prescripción Médica
        document.getElementById('form-prescripcion').addEventListener('submit', async (e) => {
            e.preventDefault();

            const meds = [];
            document.querySelectorAll('.renglon-medicamento').forEach(row => {
                meds.push({
                    medicamento_nombre: row.querySelector('.med-nombre').value,
                    concentracion: row.querySelector('.med-conc').value,
                    forma_farmaceutica: row.querySelector('.med-forma').value,
                    via_administracion: 'Oral',
                    dosis: row.querySelector('.med-dosis').value,
                    duracion_tratamiento: 'Según indicación',
                    cantidad_total: row.querySelector('.med-cant').value,
                });
            });

            const payload = {
                medicamentos: meds,
                recomendaciones_generales: document.getElementById('receta-recomendaciones').value
            };

            try {
                const res = await fetch(`/clinico/historias/${historiaId}/formulas`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.exito) {
                    window.open(`/clinico/formulas/${data.formula.id}/imprimir`, '_blank');
                }
            } catch (err) {
                alert('Error al emitir fórmula médica.');
            }
        });
    });
</script>
@endpush
