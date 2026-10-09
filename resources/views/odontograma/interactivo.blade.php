<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OdonTech - Odontograma Clínico Interactivo FDI</title>
    <link rel="stylesheet" href="/css/odontograma.css">
    <style>
        .tarjeta-paciente {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .campo-paciente .titulo {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .campo-paciente .valor {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
        }
        .alerta-exito {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            display: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="contenedor-odontograma">
        <header class="cabecera-clinica">
            <div>
                <h1>🦷 Odontograma Clínico Interactivo (FDI)</h1>
                <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.875rem;">
                    Nomenclatura Internacional FDI | Resolución 1995 de 1999 y Resolución 839 de 2017
                </p>
            </div>
            <div>
                <span class="insignia-normativa">Normativa MinSalud & DIAN Cumplida</span>
            </div>
        </header>

        <!-- Información del Paciente y Habeas Data -->
        <div class="tarjeta-paciente">
            <div class="campo-paciente">
                <div class="titulo">Paciente</div>
                <div class="valor">{{ $paciente->nombre_completo ?? 'María Fernanda Gómez Restrepo' }}</div>
            </div>
            <div class="campo-paciente">
                <div class="titulo">Identificación</div>
                <div class="valor">{{ $paciente->tipo_documento ?? 'CC' }}: {{ $paciente->numero_documento ?? '1020304050' }}</div>
            </div>
            <div class="campo-paciente">
                <div class="titulo">Historia Clínica</div>
                <div class="valor">{{ $historia->numero_historia ?? 'HC-1020304050-2026' }}</div>
            </div>
            <div class="campo-paciente">
                <div class="titulo">Ley 1581 Habeas Data</div>
                <div class="valor" style="color: #16a34a;">✓ Autorizado (Firma Digital)</div>
            </div>
        </div>

        <div id="mensaje-exito" class="alerta-exito">
            Odontograma guardado exitosamente con sellado inalterable y trazabilidad de auditoría.
        </div>

        <!-- Contenedor donde se dibuja el Odontograma -->
        <div id="odontograma-canvas"></div>

        <footer class="botones-accion">
            <button type="button" class="btn-accion btn-secundario" onclick="window.location.reload();">
                Restablecer Vista
            </button>
            <button type="button" class="btn-accion btn-primario" id="btn-guardar-odontograma">
                Guardar Odontograma FDI
            </button>
        </footer>
    </div>

    <script src="/js/odontograma.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const historiaId = {{ $historia->id ?? 1 }};
            const odontogramaApp = new OdontogramaInteractivo('odontograma-canvas', {
                historiaClinicaId: historiaId,
                tipoOdontograma: 'INICIAL'
            });

            document.getElementById('btn-guardar-odontograma').addEventListener('click', async () => {
                const btn = document.getElementById('btn-guardar-odontograma');
                btn.disabled = true;
                btn.textContent = 'Guardando...';

                try {
                    const respuesta = await odontogramaApp.guardarEnServidor(`/clinico/historias/${historiaId}/odontograma`);
                    const alerta = document.getElementById('mensaje-exito');
                    alerta.style.display = 'block';
                    alerta.textContent = respuesta.mensaje || 'Odontograma guardado exitosamente.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) {
                    alert('Error al guardar el odontograma en el servidor.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Guardar Odontograma FDI';
                }
            });
        });
    </script>
</body>
</html>
