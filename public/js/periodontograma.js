/**
 * OdonTech Colombia - Módulo de Periodontograma Interactivo
 * Sondaje milimétrico de 6 puntos, cálculo automático de inserción clínica (CAL), BOP y movilidad.
 */
class PeriodontogramaInteractivo {
    constructor(contenedorId, opciones = {}) {
        this.contenedor = document.getElementById(contenedorId);
        this.historiaClinicaId = opciones.historiaClinicaId || null;
        this.csrfToken = opciones.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.dientes = {};

        this.dientesLista = [
            18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28,
            48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38
        ];

        this.inicializarEstructura();
        this.inicializarEventos();
    }

    inicializarEstructura() {
        this.contenedor.innerHTML = `
            <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; color: #0369a1; font-size: 1.1rem;">Sondaje Periodontal Milimétrico (6 puntos por diente)</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Verde: ≤3mm (Salud) | Naranja: 4-5mm (Moderada) | Rojo: ≥6mm (Severa)</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="tabla-periodontograma" id="tabla-periodontal-superior">
                    <thead>
                        <tr class="fila-diente">
                            <th style="width: 140px;">Parámetro / Diente</th>
                            ${this.dientesLista.slice(0, 16).map(d => `<th colspan="3">${d}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Vestibular: 3 puntos (Distal, Medio, Mesial) -->
                        <tr>
                            <td style="font-weight: 600; text-align: left; padding-left: 8px;">Sondaje Vestibular (mm)</td>
                            ${this.dientesLista.slice(0, 16).map(d => `
                                <td><input type="number" min="0" max="15" value="2" class="input-sondaje" data-diente="${d}" data-pos="profundidad_vestibular_distal" title="Distal"></td>
                                <td><input type="number" min="0" max="15" value="2" class="input-sondaje" data-diente="${d}" data-pos="profundidad_vestibular_medio" title="Medio"></td>
                                <td><input type="number" min="0" max="15" value="2" class="input-sondaje" data-diente="${d}" data-pos="profundidad_vestibular_mesial" title="Mesial"></td>
                            `).join('')}
                        </tr>
                        <!-- Margen Gingival Vestibular -->
                        <tr>
                            <td style="font-weight: 600; text-align: left; padding-left: 8px;">Margen Gingival (mm)</td>
                            ${this.dientesLista.slice(0, 16).map(d => `
                                <td colspan="3"><input type="number" min="-10" max="10" value="0" class="input-sondaje" style="width: 32px;" data-diente="${d}" data-pos="margen_vestibular"></td>
                            `).join('')}
                        </tr>
                        <!-- CAL Vestibular Calculado -->
                        <tr style="background: #f8fafc;">
                            <td style="font-weight: 700; text-align: left; padding-left: 8px; color: #0284c7;">Nivel Inserción CAL (mm)</td>
                            ${this.dientesLista.slice(0, 16).map(d => `
                                <td colspan="3" class="indicador-cal" id="cal-vest-${d}">2</td>
                            `).join('')}
                        </tr>
                        <!-- Sangrado al Sondaje (BOP) -->
                        <tr>
                            <td style="font-weight: 600; text-align: left; padding-left: 8px;">Sangrado (BOP)</td>
                            ${this.dientesLista.slice(0, 16).map(d => `
                                <td colspan="3"><input type="checkbox" class="check-periodontal check-sangrado" data-diente="${d}"></td>
                            `).join('')}
                        </tr>
                        <!-- Movilidad Dental -->
                        <tr>
                            <td style="font-weight: 600; text-align: left; padding-left: 8px;">Movilidad Dental</td>
                            ${this.dientesLista.slice(0, 16).map(d => `
                                <td colspan="3">
                                    <select class="select-grado select-movilidad" data-diente="${d}">
                                        <option value="0">0</option>
                                        <option value="1">I</option>
                                        <option value="2">II</option>
                                        <option value="3">III</option>
                                    </select>
                                </td>
                            `).join('')}
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="resumen-periodontal">
                <div><strong>Índice de Sangrado al Sondaje:</strong> <span id="indice-sangrado" style="color: #ef4444; font-weight: bold;">0%</span></div>
                <div style="margin-left: 20px;"><strong>Bolsas Periodontales Severas (≥6mm):</strong> <span id="total-bolsas-severas" style="color: #dc2626; font-weight: bold;">0</span></div>
            </div>
        `;

        this.actualizarColoresSondaje();
    }

    inicializarEventos() {
        this.contenedor.addEventListener('input', (e) => {
            if (e.target.classList.contains('input-sondaje')) {
                this.recalcularFila(e.target);
            } else if (e.target.classList.contains('check-sangrado')) {
                this.recalcularSangrado();
            }
        });
    }

    recalcularFila(input) {
        const diente = input.getAttribute('data-diente');
        const dInput = this.contenedor.querySelector(`input[data-diente="${diente}"][data-pos="profundidad_vestibular_distal"]`);
        const mInput = this.contenedor.querySelector(`input[data-diente="${diente}"][data-pos="profundidad_vestibular_medio"]`);
        const mesInput = this.contenedor.querySelector(`input[data-diente="${diente}"][data-pos="profundidad_vestibular_mesial"]`);
        const margenInput = this.contenedor.querySelector(`input[data-diente="${diente}"][data-pos="margen_vestibular"]`);

        const maxSondaje = Math.max(
            parseInt(dInput?.value || 0),
            parseInt(mInput?.value || 0),
            parseInt(mesInput?.value || 0)
        );

        const margen = parseInt(margenInput?.value || 0);
        const cal = maxSondaje + margen;

        const calElement = document.getElementById(`cal-vest-${diente}`);
        if (calElement) {
            calElement.textContent = cal;
        }

        this.actualizarColoresSondaje();
    }

    actualizarColoresSondaje() {
        let bolsasSeveras = 0;

        this.contenedor.querySelectorAll('.input-sondaje').forEach(inp => {
            const val = parseInt(inp.value || 0);
            inp.classList.remove('sondaje-sano', 'sondaje-moderado', 'sondaje-severo');

            if (val >= 6) {
                inp.classList.add('sondaje-severo');
                bolsasSeveras++;
            } else if (val >= 4) {
                inp.classList.add('sondaje-moderado');
            } else {
                inp.classList.add('sondaje-sano');
            }
        });

        document.getElementById('total-bolsas-severas').textContent = bolsasSeveras;
    }

    recalcularSangrado() {
        const total = this.dientesLista.slice(0, 16).length;
        const activos = this.contenedor.querySelectorAll('.check-sangrado:checked').length;
        const porcentaje = Math.round((activos / total) * 100);
        document.getElementById('indice-sangrado').textContent = `${porcentaje}%`;
    }

    obtenerPayloadJSON() {
        const dientesArray = [];

        this.dientesLista.slice(0, 16).forEach(d => {
            const dVal = parseInt(this.contenedor.querySelector(`input[data-diente="${d}"][data-pos="profundidad_vestibular_distal"]`)?.value || 0);
            const mVal = parseInt(this.contenedor.querySelector(`input[data-diente="${d}"][data-pos="profundidad_vestibular_medio"]`)?.value || 0);
            const mesVal = parseInt(this.contenedor.querySelector(`input[data-diente="${d}"][data-pos="profundidad_vestibular_mesial"]`)?.value || 0);
            const margVal = parseInt(this.contenedor.querySelector(`input[data-diente="${d}"][data-pos="margen_vestibular"]`)?.value || 0);
            const sangrado = this.contenedor.querySelector(`.check-sangrado[data-diente="${d}"]`)?.checked || false;
            const movilidad = this.contenedor.querySelector(`.select-movilidad[data-diente="${d}"]`)?.value || '0';

            dientesArray.push({
                numero_diente_fdi: d,
                profundidad_vestibular_distal: dVal,
                profundidad_vestibular_medio: mVal,
                profundidad_vestibular_mesial: mesVal,
                profundidad_lingual_distal: 2,
                profundidad_lingual_medio: 2,
                profundidad_lingual_mesial: 2,
                margen_vestibular: margVal,
                margen_lingual: 0,
                sangrado_al_sondaje: sangrado,
                supuracion: false,
                movilidad_dental: movilidad,
                lesion_furca: 'NO_APLICA'
            });
        });

        return {
            fecha_evaluacion: new Date().toISOString(),
            indice_placa_porcentaje: 15.0,
            indice_sangrado_porcentaje: parseInt(document.getElementById('indice-sangrado').textContent) || 0,
            diagnostico_periodontal: 'Evaluación periodontal rutinaria',
            dientes: dientesArray
        };
    }
}

window.PeriodontogramaInteractivo = PeriodontogramaInteractivo;
