/**
 * OdonTech Colombia - Módulo de Manipulación Visual de Odontograma Interactivo (FDI)
 * Cumplimiento con la Nomenclatura de la Federación Dental Internacional (FDI).
 */
class OdontogramaInteractivo {
    constructor(contenedorId, opciones = {}) {
        this.contenedor = document.getElementById(contenedorId);
        this.historiaClinicaId = opciones.historiaClinicaId || null;
        this.tipoOdontograma = opciones.tipoOdontograma || 'INICIAL';
        this.soloLectura = opciones.soloLectura || false;
        this.csrfToken = opciones.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        this.herramientaActiva = 'caries'; // 'caries', 'resina', 'amalgama', 'sellante', 'fractura', 'sano', 'ausente'
        this.mostrarDeciduos = false;

        // Estado interno en memoria de cada uno de los 52 dientes posibles
        this.estadoDientes = {};

        this.inicializarEstructura();
        this.inicializarEventos();
        if (opciones.datosIniciales) {
            this.cargarDatos(opciones.datosIniciales);
        }
    }

    // Cuadrantes anatómicos permanentes y temporales (FDI)
    static CUADRANTES = {
        PERMANENTES_SUPERIORES: {
            C1: [18, 17, 16, 15, 14, 13, 12, 11],
            C2: [21, 22, 23, 24, 25, 26, 27, 28]
        },
        TEMPORALES_SUPERIORES: {
            C5: [55, 54, 53, 52, 51],
            C6: [61, 62, 63, 64, 65]
        },
        TEMPORALES_INFERIORES: {
            C8: [85, 84, 83, 82, 81],
            C7: [71, 72, 73, 74, 75]
        },
        PERMANENTES_INFERIORES: {
            C4: [48, 47, 46, 45, 44, 43, 42, 41],
            C3: [31, 32, 33, 34, 35, 36, 37, 38]
        }
    };

    inicializarEstructura() {
        this.contenedor.innerHTML = `
            <div class="barra-herramientas">
                <div class="grupo-herramienta">
                    <span style="font-weight: 600; font-size: 0.85rem; margin-right: 6px;">Hallazgos Clínicos:</span>
                    <button type="button" class="btn-herramienta activo" data-herramienta="caries">
                        <span class="muestra-color" style="background: var(--color-caries);"></span> Caries (Rojo)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="resina">
                        <span class="muestra-color" style="background: var(--color-resina);"></span> Obt. Resina (Azul)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="amalgama">
                        <span class="muestra-color" style="background: var(--color-amalgama);"></span> Obt. Amalgama (Gris)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="sellante">
                        <span class="muestra-color" style="background: var(--color-sellante);"></span> Sellante (Amarillo)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="fractura">
                        <span class="muestra-color" style="background: var(--color-fractura);"></span> Fractura (Verde)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="sano">
                        <span class="muestra-color" style="background: var(--color-sano);"></span> Limpiar (Sano)
                    </button>
                    <button type="button" class="btn-herramienta" data-herramienta="ausente">
                        <span style="color: var(--color-ausente); font-weight: bold;">✕</span> Diente Ausente
                    </button>
                </div>
            </div>

            <!-- Arco Superior Permanente -->
            <div class="arco-dental" id="arco-permanente-superior">
                <div class="cuadrante-fdi" id="cuadrante-1"></div>
                <div class="separador-linea-media"></div>
                <div class="cuadrante-fdi" id="cuadrante-2"></div>
            </div>

            <!-- Arco Inferior Permanente -->
            <div class="arco-dental" id="arco-permanente-inferior" style="margin-top: 28px;">
                <div class="cuadrante-fdi" id="cuadrante-4"></div>
                <div class="separador-linea-media"></div>
                <div class="cuadrante-fdi" id="cuadrante-3"></div>
            </div>

            <!-- Panel de Estadísticas y Resumen Clínico -->
            <div class="panel-resumen">
                <div class="tarjeta-estadistica">
                    <div class="etiqueta">Superficies con Caries</div>
                    <div class="valor" id="resumen-caries" style="color: var(--color-caries);">0</div>
                </div>
                <div class="tarjeta-estadistica">
                    <div class="etiqueta">Restauraciones en Resina</div>
                    <div class="valor" id="resumen-resina" style="color: var(--color-resina);">0</div>
                </div>
                <div class="tarjeta-estadistica">
                    <div class="etiqueta">Restauraciones en Amalgama</div>
                    <div class="valor" id="resumen-amalgama" style="color: var(--color-amalgama);">0</div>
                </div>
                <div class="tarjeta-estadistica">
                    <div class="etiqueta">Sellantes Realizados</div>
                    <div class="valor" id="resumen-sellante" style="color: var(--color-sellante);">0</div>
                </div>
                <div class="tarjeta-estadistica">
                    <div class="etiqueta">Dientes Ausentes</div>
                    <div class="valor" id="resumen-ausentes" style="color: var(--color-ausente);">0</div>
                </div>
            </div>
        `;

        this.renderizarDientes();
    }

    renderizarDientes() {
        this.renderizarCuadrante('cuadrante-1', OdontogramaInteractivo.CUADRANTES.PERMANENTES_SUPERIORES.C1);
        this.renderizarCuadrante('cuadrante-2', OdontogramaInteractivo.CUADRANTES.PERMANENTES_SUPERIORES.C2);
        this.renderizarCuadrante('cuadrante-4', OdontogramaInteractivo.CUADRANTES.PERMANENTES_INFERIORES.C4);
        this.renderizarCuadrante('cuadrante-3', OdontogramaInteractivo.CUADRANTES.PERMANENTES_INFERIORES.C3);
    }

    renderizarCuadrante(elementoId, listaDientes) {
        const contenedorCuadrante = document.getElementById(elementoId);
        contenedorCuadrante.innerHTML = '';

        listaDientes.forEach(numeroFdi => {
            if (!this.estadoDientes[numeroFdi]) {
                this.estadoDientes[numeroFdi] = {
                    numero_diente_fdi: numeroFdi,
                    estado_general_diente: 'SANO',
                    superficie_vestibular: 'SANO',
                    superficie_lingual_palatina: 'SANO',
                    superficie_mesial: 'SANO',
                    superficie_distal: 'SANO',
                    superficie_oclusal_incisal: 'SANO',
                    observaciones: ''
                };
            }

            const cuaddroDiente = document.createElement('div');
            cuaddroDiente.className = 'cuadro-diente';
            cuaddroDiente.id = `diente-box-${numeroFdi}`;

            // Determinar orientación espacial (Mesial está siempre hacia la línea media)
            const esCuadranteDerecho = (numeroFdi >= 11 && numeroFdi <= 18) || (numeroFdi >= 41 && numeroFdi <= 48);
            const mesialLado = esCuadranteDerecho ? 'right' : 'left';
            const distalLado = esCuadranteDerecho ? 'left' : 'right';

            // Geometría SVG con 5 polígonos: Vestibular, Lingual/Palatina, Mesial, Distal, Oclusal
            cuaddroDiente.innerHTML = `
                <span class="numero-fdi">${numeroFdi}</span>
                <svg viewBox="0 0 100 100" class="svg-diente" data-diente="${numeroFdi}">
                    <!-- Vestibular (Superior) -->
                    <polygon class="superficie sup-vestibular" data-sup="superficie_vestibular" points="0,0 100,0 75,25 25,25" />
                    <!-- Lingual / Palatina (Inferior) -->
                    <polygon class="superficie sup-lingual" data-sup="superficie_lingual_palatina" points="25,75 75,75 100,100 0,100" />
                    <!-- Lado Izquierdo -->
                    <polygon class="superficie sup-izq" data-sup="${mesialLado === 'left' ? 'superficie_mesial' : 'superficie_distal'}" points="0,0 25,25 25,75 0,100" />
                    <!-- Lado Derecho -->
                    <polygon class="superficie sup-der" data-sup="${mesialLado === 'right' ? 'superficie_mesial' : 'superficie_distal'}" points="75,25 100,0 100,100 75,75" />
                    <!-- Oclusal / Incisal (Centro) -->
                    <polygon class="superficie sup-oclusal" data-sup="superficie_oclusal_incisal" points="25,25 75,25 75,75 25,75" />
                </svg>
            `;

            contenedorCuadrante.appendChild(cuaddroDiente);
        });
    }

    inicializarEventos() {
        // Evento de cambio de herramienta
        this.contenedor.querySelectorAll('.btn-herramienta').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.contenedor.querySelectorAll('.btn-herramienta').forEach(b => b.classList.remove('activo'));
                btn.classList.add('activo');
                this.herramientaActiva = btn.getAttribute('data-herramienta');
            });
        });

        // Evento de clic en una superficie o diente
        this.contenedor.addEventListener('click', (e) => {
            if (this.soloLectura) return;

            const supElement = e.target.closest('.superficie');
            if (supElement) {
                const svgDiente = supElement.closest('.svg-diente');
                const numeroFdi = parseInt(svgDiente.getAttribute('data-diente'));
                const nombreSup = supElement.getAttribute('data-sup');

                this.aplicarHerramientaASuperficie(numeroFdi, nombreSup, supElement);
                return;
            }

            const svgElement = e.target.closest('.svg-diente');
            if (svgElement && this.herramientaActiva === 'ausente') {
                const numeroFdi = parseInt(svgElement.getAttribute('data-diente'));
                this.alternarDienteAusente(numeroFdi);
            }
        });
    }

    aplicarHerramientaASuperficie(numeroFdi, nombreSup, elementoPoligono) {
        if (!this.estadoDientes[numeroFdi]) return;

        // Limpiar clases visuales
        elementoPoligono.classList.remove('caries', 'resina', 'amalgama', 'sellante', 'fractura');

        let valorEstado = 'SANO';
        if (this.herramientaActiva === 'caries') {
            elementoPoligono.classList.add('caries');
            valorEstado = 'CARIES';
        } else if (this.herramientaActiva === 'resina') {
            elementoPoligono.classList.add('resina');
            valorEstado = 'OBTURACION_RESINA';
        } else if (this.herramientaActiva === 'amalgama') {
            elementoPoligono.classList.add('amalgama');
            valorEstado = 'OBTURACION_AMALGAMA';
        } else if (this.herramientaActiva === 'sellante') {
            elementoPoligono.classList.add('sellante');
            valorEstado = 'SELLANTE';
        } else if (this.herramientaActiva === 'fractura') {
            elementoPoligono.classList.add('fractura');
            valorEstado = 'FRACTURA';
        }

        this.estadoDientes[numeroFdi][nombreSup] = valorEstado;
        this.actualizarMetricas();
    }

    alternarDienteAusente(numeroFdi) {
        const boxDiente = document.getElementById(`diente-box-${numeroFdi}`);
        const estadoActual = this.estadoDientes[numeroFdi].estado_general_diente;

        if (estadoActual === 'AUSENTE') {
            this.estadoDientes[numeroFdi].estado_general_diente = 'SANO';
            boxDiente.classList.remove('diente-ausente');
        } else {
            this.estadoDientes[numeroFdi].estado_general_diente = 'AUSENTE';
            boxDiente.classList.add('diente-ausente');
        }

        this.actualizarMetricas();
    }

    actualizarMetricas() {
        let conteoCaries = 0;
        let conteoResina = 0;
        let conteoAmalgama = 0;
        let conteoSellante = 0;
        let conteoAusentes = 0;

        const superficies = [
            'superficie_vestibular',
            'superficie_lingual_palatina',
            'superficie_mesial',
            'superficie_distal',
            'superficie_oclusal_incisal'
        ];

        Object.values(this.estadoDientes).forEach(diente => {
            if (diente.estado_general_diente === 'AUSENTE') {
                conteoAusentes++;
            }

            superficies.forEach(sup => {
                if (diente[sup] === 'CARIES') conteoCaries++;
                if (diente[sup] === 'OBTURACION_RESINA') conteoResina++;
                if (diente[sup] === 'OBTURACION_AMALGAMA') conteoAmalgama++;
                if (diente[sup] === 'SELLANTE') conteoSellante++;
            });
        });

        document.getElementById('resumen-caries').textContent = conteoCaries;
        document.getElementById('resumen-resina').textContent = conteoResina;
        document.getElementById('resumen-amalgama').textContent = conteoAmalgama;
        document.getElementById('resumen-sellante').textContent = conteoSellante;
        document.getElementById('resumen-ausentes').textContent = conteoAusentes;
    }

    obtenerPayloadJSON() {
        return {
            tipo_odontograma: this.tipoOdontograma,
            observaciones: 'Odontograma digitalizado bajo nomenclatura FDI',
            dientes: Object.values(this.estadoDientes)
        };
    }

    async guardarEnServidor(urlGuardado) {
        const payload = this.obtenerPayloadJSON();

        try {
            const respuesta = await fetch(urlGuardado, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await respuesta.json();
            return data;
        } catch (error) {
            console.error('Error al guardar el odontograma:', error);
            throw error;
        }
    }

    cargarDatos(datosOdontograma) {
        if (!datosOdontograma || !datosOdontograma.dientes) return;

        datosOdontograma.dientes.forEach(diente => {
            const num = diente.numero_diente_fdi;
            this.estadoDientes[num] = Object.assign({}, this.estadoDientes[num], diente);

            const svgDiente = document.querySelector(`.svg-diente[data-diente="${num}"]`);
            if (!svgDiente) return;

            const boxDiente = document.getElementById(`diente-box-${num}`);
            if (diente.estado_general_diente === 'AUSENTE') {
                boxDiente.classList.add('diente-ausente');
            }

            // Aplicar colores a cada polígono según superficie
            const mapaClases = {
                'CARIES': 'caries',
                'OBTURACION_RESINA': 'resina',
                'OBTURACION_AMALGAMA': 'amalgama',
                'SELLANTE': 'sellante',
                'FRACTURA': 'fractura'
            };

            const mapaNombres = {
                'superficie_vestibular': '.sup-vestibular',
                'superficie_lingual_palatina': '.sup-lingual',
                'superficie_oclusal_incisal': '.sup-oclusal'
            };

            for (const [prop, selector] of Object.entries(mapaNombres)) {
                if (diente[prop] && mapaClases[diente[prop]]) {
                    const el = svgDiente.querySelector(selector);
                    if (el) el.classList.add(mapaClases[diente[prop]]);
                }
            }
        });

        this.actualizarMetricas();
    }
}

window.OdontogramaInteractivo = OdontogramaInteractivo;
