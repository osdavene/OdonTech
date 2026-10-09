@extends('layouts.app')

@section('titulo', 'Centro de Facturación Electrónica DIAN y RIPS MinSalud')

@section('contenido')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; margin: 0 0 4px 0; color: #0f172a;">Centro de Facturación DIAN & RIPS (SISPRO)</h1>
    <p style="margin: 0; color: #64748b; font-size: 0.875rem;">
        Emisión de Facturas Electrónicas de Venta en Salud (Anexo 1.9 DIAN) y reporte de RIPS (Resolución 2275 de 2023).
    </p>
</div>

<div class="tarjeta-blanca">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 1.1rem; margin: 0; color: #0f172a;">Facturas Electrónicas de Salud</h2>
        <div style="font-size: 0.8rem; color: #64748b;">
            Proveedor Tecnológico Autorizado (PTA): <strong style="color: #0284c7;">Factus API v2</strong>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; text-align: left;">
                <th style="padding: 10px;">No. Factura</th>
                <th style="padding: 10px;">Paciente</th>
                <th style="padding: 10px;">Fecha</th>
                <th style="padding: 10px; text-align: right;">Total Pagar</th>
                <th style="padding: 10px; text-align: center;">Estado DIAN (CUFE)</th>
                <th style="padding: 10px; text-align: center;">RIPS MinSalud (CUV)</th>
                <th style="padding: 10px; text-align: right;">Acciones Normativas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturas ?? [] as $factura)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px; font-weight: 700; color: #0284c7;">
                    <a href="/facturacion/facturas/{{ $factura->id }}/imprimir" target="_blank" style="color: inherit; text-decoration: underline;">
                        {{ $factura->numero_factura }}
                    </a>
                </td>
                <td style="padding: 10px;">
                    <strong>{{ $factura->paciente->nombre_completo }}</strong><br>
                    <span style="font-size: 0.75rem; color: #64748b;">{{ $factura->paciente->tipo_documento }} {{ $factura->paciente->numero_documento }}</span>
                </td>
                <td style="padding: 10px;">{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                <td style="padding: 10px; text-align: right; font-weight: 700;">
                    ${{ number_format($factura->total_pagar, 2) }}
                </td>
                <td style="padding: 10px; text-align: center;">
                    @if ($factura->estaValidadaDian())
                        <span style="background: #dcfce7; color: #166534; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            ✓ VALIDADA DIAN
                        </span>
                        <div style="font-size: 8px; color: #64748b; font-family: monospace; margin-top: 2px;">
                            {{ substr($factura->cufe, 0, 16) }}...
                        </div>
                    @else
                        <span style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            BORRADOR
                        </span>
                    @endif
                </td>
                <td style="padding: 10px; text-align: center;">
                    @if ($factura->tieneRipsCuv())
                        <span style="background: #dbeafe; color: #1e40af; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            ✓ VALIDADO SISPRO
                        </span>
                        <div style="font-size: 8px; color: #64748b; font-family: monospace; margin-top: 2px;">
                            {{ substr($factura->rips_cuv, 0, 14) }}...
                        </div>
                    @elseif ($factura->rips_estado === 'GENERADO')
                        <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            JSON GENERADO
                        </span>
                    @else
                        <span style="background: #f1f5f9; color: #94a3b8; font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">
                            PENDIENTE
                        </span>
                    @endif
                </td>
                <td style="padding: 10px; text-align: right; white-space: nowrap;">
                    @if (!$factura->estaValidadaDian())
                        <button onclick="transmitirDian({{ $factura->id }})" style="padding: 4px 8px; background: #0284c7; color: white; border: none; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                            📤 Enviar DIAN
                        </button>
                    @elseif ($factura->rips_estado === 'PENDIENTE')
                        <button onclick="generarRips({{ $factura->id }})" style="padding: 4px 8px; background: #d97706; color: white; border: none; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                            📄 Armar RIPS
                        </button>
                    @elseif ($factura->rips_estado === 'GENERADO')
                        <a href="/facturacion/facturas/{{ $factura->id }}/descargar-rips" style="display: inline-block; padding: 4px 8px; background: #475569; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                            💾 Descargar JSON
                        </a>
                        <button onclick="validarSispro({{ $factura->id }})" style="padding: 4px 8px; background: #16a34a; color: white; border: none; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                            ✓ Validar CUV
                        </button>
                    @else
                        <a href="/facturacion/facturas/{{ $factura->id }}/descargar-rips" style="display: inline-block; padding: 4px 8px; background: #475569; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                            💾 RIPS JSON
                        </a>
                    @endif
                    <a href="/facturacion/facturas/{{ $factura->id }}/imprimir" target="_blank" style="display: inline-block; padding: 4px 8px; background: #0369a1; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none; margin-left: 4px;">
                        🖨️ PDF
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 24px; color: #64748b;">
                    No hay facturas registradas actualmente.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function transmitirDian(facturaId) {
        if (!confirm('¿Desea transmitir esta factura al PTA de la DIAN para obtener el CUFE oficial?')) return;

        try {
            const res = await fetch(`/facturacion/facturas/${facturaId}/enviar-dian`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            alert(data.mensaje);
            window.location.reload();
        } catch (e) {
            alert('Error en la comunicación con la DIAN.');
        }
    }

    async function generarRips(facturaId) {
        try {
            const res = await fetch(`/facturacion/facturas/${facturaId}/generar-rips`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            alert(data.mensaje);
            window.location.reload();
        } catch (e) {
            alert('Error al generar RIPS JSON.');
        }
    }

    async function validarSispro(facturaId) {
        try {
            const res = await fetch(`/facturacion/facturas/${facturaId}/validar-sispro`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            alert(`Validación Exitosa. Código CUV recibido: ${data.cuv}`);
            window.location.reload();
        } catch (e) {
            alert('Error al validar con SISPRO.');
        }
    }
</script>
@endpush
