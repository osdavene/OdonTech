<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fórmula Médica Odontológica - {{ $formula->paciente->nombre_completo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 0;
            padding: 20px;
            background: #ffffff;
        }
        .contenedor-receta {
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            padding: 24px;
            border-radius: 6px;
        }
        .encabezado {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .datos-clinica h2 {
            margin: 0 0 2px 0;
            color: #0284c7;
            font-size: 15px;
        }
        .cuadro-doctor {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 4px;
            text-align: right;
            font-size: 10px;
        }
        .seccion-paciente {
            background: #f1f5f9;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            font-size: 10px;
        }
        .tabla-medicamentos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .tabla-medicamentos th {
            background: #0284c7;
            color: white;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }
        .tabla-medicamentos td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 6px;
            font-size: 10px;
        }
        .pie-receta {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #cbd5e1;
            padding-top: 20px;
            margin-top: 24px;
        }
        .firma-area {
            text-align: center;
            width: 250px;
            border-top: 1px solid #334155;
            padding-top: 6px;
            font-size: 10px;
        }
        @media print {
            body { padding: 0; }
            .contenedor-receta { border: none; padding: 0; }
            .no-imprimir { display: none; }
        }
    </style>
</head>
<body>
    <div class="contenedor-receta">
        <div class="no-imprimir" style="margin-bottom: 12px; text-align: right;">
            <button onclick="window.print();" style="padding: 6px 14px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                🖨️ Imprimir Prescripción Médica
            </button>
        </div>

        <div class="encabezado">
            <div class="datos-clinica">
                <h2>CENTRO ODONTOLÓGICO ODONTECH SAS</h2>
                <div>NIT: 901.234.567-1 | Código REPS MinSalud: 110010000001</div>
                <div>Calle 100 # 15-20 Consultorio 401, Bogotá D.C. | Tel: (601) 300 0000</div>
            </div>
            <div class="cuadro-doctor">
                <strong>{{ $formula->odontologo->nombre_completo }}</strong><br>
                <span>{{ $formula->odontologo->especialidad ?? 'Odontología General' }}</span><br>
                <span style="color: #0369a1; font-weight: bold;">Registro ReTHUS: {{ $formula->odontologo->registro_profesional }}</span>
            </div>
        </div>

        <div class="seccion-paciente">
            <div>
                <strong>Paciente:</strong> {{ $formula->paciente->nombre_completo }}<br>
                <strong>Identificación:</strong> {{ $formula->paciente->tipo_documento }} {{ $formula->paciente->numero_documento }}<br>
                <strong>Edad:</strong> {{ \Carbon\Carbon::parse($formula->paciente->fecha_nacimiento)->age }} años
            </div>
            <div>
                <strong>Fecha Emisión:</strong> {{ $formula->fecha_emision->format('d/m/Y H:i') }}<br>
                <strong>Vigencia:</strong> {{ $formula->vigencia_dias }} días (Decreto 2200 de 2005)<br>
                <strong>Diagnóstico CIE-10:</strong> {{ $formula->diagnosticoCie?->codigo }} - {{ $formula->diagnosticoCie?->nombre }}
            </div>
        </div>

        <table class="tabla-medicamentos">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Medicamento / Concentración</th>
                    <th style="width: 70px;">Presentación</th>
                    <th>Posología / Instrucciones</th>
                    <th style="width: 60px; text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($formula->medicamentos as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->medicamento_nombre }}</strong> ({{ $item->concentracion }})<br>
                        <span style="color: #64748b; font-size: 9px;">Vía: {{ $item->via_administracion }}</span>
                    </td>
                    <td>{{ $item->forma_farmaceutica }}</td>
                    <td>
                        <strong>{{ $item->dosis }}</strong><br>
                        <span>{{ $item->duracion_tratamiento }}</span>
                        @if ($item->indicaciones_uso)
                            <div style="color: #0369a1; font-size: 9px; margin-top: 2px;">Nota: {{ $item->indicaciones_uso }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $item->cantidad_total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($formula->recomendaciones_generales)
        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px;">
            <strong>Recomendaciones Generales de Cuidado Oral:</strong><br>
            {{ $formula->recomendaciones_generales }}
        </div>
        @endif

        <div class="pie-receta">
            <div style="font-size: 8px; color: #64748b; max-width: 350px;">
                Prescripción emitida bajo los parámetros del Decreto 2200 de 2005 y la Resolución 1995 de 1999 de la República de Colombia.<br>
                <strong>Firma Digital Hash:</strong> {{ substr($formula->firma_digital_hash, 0, 24) }}...
            </div>
            <div class="firma-area">
                <strong>{{ $formula->odontologo->nombre_completo }}</strong><br>
                <span>Odontólogo Tratante</span><br>
                <span>ReTHUS: {{ $formula->odontologo->registro_profesional }}</span>
            </div>
        </div>
    </div>
</body>
</html>
