<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica de Venta en Salud - {{ $factura->numero_factura }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            background: #ffffff;
        }
        .contenedor-factura {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            padding: 24px;
            border-radius: 6px;
        }
        .encabezado {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .datos-prestador h2 {
            margin: 0 0 4px 0;
            color: #0284c7;
            font-size: 16px;
        }
        .cuadro-resolucion {
            border: 2px solid #0284c7;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
            background: #f0f9ff;
            min-width: 220px;
        }
        .cuadro-resolucion h3 {
            margin: 0;
            font-size: 14px;
            color: #0369a1;
        }
        .seccion-adquiriente {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .tabla-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .tabla-items th {
            background: #0284c7;
            color: white;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }
        .tabla-items td {
            border-bottom: 1px solid #e2e8f0;
            padding: 6px;
            font-size: 10px;
        }
        .tabla-totales {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .tabla-totales td {
            padding: 4px 8px;
        }
        .tabla-totales .gran-total {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #0284c7;
        }
        .bloque-cufe {
            background: #f8fafc;
            border: 1px dashed #94a3b8;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 9px;
            word-break: break-all;
            margin-bottom: 12px;
        }
        .pie-pagina {
            display: flex;
            align-items: center;
            gap: 16px;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
        }
        .qr-placeholder {
            width: 85px;
            height: 85px;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #94a3b8;
            text-align: center;
        }
        @media print {
            body { padding: 0; }
            .contenedor-factura { border: none; padding: 0; }
            .no-imprimir { display: none; }
        }
    </style>
</head>
<body>
    <div class="contenedor-factura">
        <div class="no-imprimir" style="margin-bottom: 16px; text-align: right;">
            <button onclick="window.print();" style="padding: 6px 14px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                🖨️ Imprimir / Guardar en PDF
            </button>
        </div>

        <div class="encabezado">
            <div class="datos-prestador">
                <h2>CENTRO ODONTOLÓGICO ODONTECH S.A.S.</h2>
                <div>NIT: 901.234.567-1 | Régimen Responsable de IVA</div>
                <div>Código Prestador REPS MinSalud: <strong>110010000001</strong></div>
                <div>Dirección: Calle 100 # 15-20 Consultorio 401, Bogotá D.C.</div>
                <div>Teléfono: (601) 300 0000 | Correo: facturacion@odontech.com.co</div>
                <div>Actividad Económica: 8622 (Actividades de la práctica odontológica)</div>
            </div>
            <div class="cuadro-resolucion">
                <h3>FACTURA ELECTRÓNICA DE VENTA EN SALUD</h3>
                <div style="font-size: 16px; font-weight: bold; color: #0284c7; margin: 4px 0;">
                    No. {{ $factura->numero_factura }}
                </div>
                <div style="font-size: 9px; color: #64748b;">
                    Autorización DIAN No. 18764000001 de 2026<br>
                    Rango: FEV1001 a FEV5000 | Vigencia 24 Meses
                </div>
            </div>
        </div>

        <div class="seccion-adquiriente">
            <div>
                <strong>Adquiriente (Paciente):</strong> {{ $factura->paciente->nombre_completo }}<br>
                <strong>Identificación:</strong> {{ $factura->paciente->tipo_documento }} {{ $factura->paciente->numero_documento }}<br>
                <strong>Dirección:</strong> {{ $factura->paciente->direccion_residencia }}<br>
                <strong>Municipio:</strong> Bogotá D.C. (DANE 11001)
            </div>
            <div>
                <strong>Fecha Emisión:</strong> {{ $factura->fecha_emision->format('d/m/Y H:i') }}<br>
                <strong>Fecha Vencimiento:</strong> {{ $factura->fecha_vencimiento->format('d/m/Y') }}<br>
                <strong>Medio de Pago:</strong> Efectivo (Código DIAN 10)<br>
                <strong>Régimen Salud:</strong> Particular / SGSSS
            </div>
        </div>

        <table class="tabla-items">
            <thead>
                <tr>
                    <th style="width: 50px;">Código</th>
                    <th>Concepto / Procedimiento CUPS</th>
                    <th style="width: 40px;">Diente</th>
                    <th style="width: 35px; text-align: center;">Cant</th>
                    <th style="width: 75px; text-align: right;">Val. Unitario</th>
                    <th style="width: 60px; text-align: right;">Copago</th>
                    <th style="width: 75px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factura->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cups->codigo }}</td>
                    <td>
                        <strong>{{ $detalle->descripcion_concepto }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">Diag. CIE-10: {{ $detalle->diagnosticoCie->codigo }} - {{ $detalle->diagnosticoCie->nombre }}</span>
                    </td>
                    <td style="text-align: center;">{{ $detalle->diente_fdi ?? '-' }}</td>
                    <td style="text-align: center;">{{ number_format($detalle->cantidad, 0) }}</td>
                    <td style="text-align: right;">${{ number_format($detalle->valor_unitario, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($detalle->copago, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($detalle->valor_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="tabla-totales">
            <tr>
                <td>Subtotal Servicios Odontológicos:</td>
                <td style="text-align: right;">${{ number_format($factura->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Descuentos:</td>
                <td style="text-align: right;">-${{ number_format($factura->descuento, 2) }}</td>
            </tr>
            <tr>
                <td>IVA (0% Excluido Art. 476 ET):</td>
                <td style="text-align: right;">$0.00</td>
            </tr>
            <tr class="gran-total">
                <td>TOTAL A PAGAR:</td>
                <td style="text-align: right; color: #0284c7;">${{ number_format($factura->total_pagar, 2) }}</td>
            </tr>
        </table>

        <div class="bloque-cufe">
            <strong>CUFE:</strong> {{ $factura->cufe ?? 'PENDIENTE_VALIDACION_DIAN' }}<br>
            @if ($factura->rips_cuv)
            <strong>CUV SISPRO (MinSalud):</strong> {{ $factura->rips_cuv }}
            @endif
        </div>

        <div class="pie-pagina">
            <div class="qr-placeholder">
                CÓDIGO QR<br>DIAN
            </div>
            <div style="font-size: 8.5px; color: #64748b; line-height: 1.4;">
                Representación gráfica oficial de la Factura Electrónica de Venta en Salud de acuerdo con la Resolución 000042 de 2020 y Anexo Técnico 1.9 de la DIAN y Resoluciones 2275 de 2023 y 510 de 2024 del Ministerio de Salud y Protección Social. Los servicios de salud humana se encuentran expresamente excluidos del Impuesto sobre las Ventas (IVA) de conformidad con el Artículo 476 del Estatuto Tributario Colombiano.
            </div>
        </div>
    </div>
</body>
</html>
