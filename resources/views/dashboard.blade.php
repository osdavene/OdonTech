@extends('layouts.app')

@section('titulo', 'Panel de Control Gerencial y Clínico')

@section('contenido')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; margin: 0 0 4px 0; color: #0f172a;">Panel Principal de Gestión Odontológica</h1>
    <p style="margin: 0; color: #64748b; font-size: 0.875rem;">Visión integral de operaciones clínicas, normativa sanitaria y facturación electrónica.</p>
</div>

<!-- Tarjetas de Métricas Normativas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="tarjeta-blanca" style="border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Citas para Hoy</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #0284c7; margin-top: 6px;">{{ $totalCitasHoy ?? 8 }}</div>
        <div style="font-size: 0.75rem; color: #16a34a; margin-top: 4px;">2 en sala de espera</div>
    </div>
    <div class="tarjeta-blanca" style="border-left: 4px solid #16a34a;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Habeas Data (Ley 1581)</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a; margin-top: 6px;">100%</div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Pacientes autorizados</div>
    </div>
    <div class="tarjeta-blanca" style="border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Facturas con CUFE DIAN</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #d97706; margin-top: 6px;">{{ $totalFacturasCufe ?? 14 }}</div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Transmitidas a Factus PTA</div>
    </div>
    <div class="tarjeta-blanca" style="border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Alertas INVIMA Activas</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #dc2626; margin-top: 6px;">{{ $alertasInvimaCount ?? 1 }}</div>
        <div style="font-size: 0.75rem; color: #dc2626; margin-top: 4px;">1 lote bloqueado preventivo</div>
    </div>
</div>

<!-- Citas del Día y Control de Sala de Espera -->
<div class="tarjeta-blanca">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 1.1rem; margin: 0; color: #0f172a;">Turnos y Citas Odontológicas del Día</h2>
        <a href="/pacientes/nuevo" class="btn-accion btn-primario" style="text-decoration: none; padding: 6px 14px; font-size: 0.8rem;">
            + Nueva Admisión
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                <th style="padding: 10px;">Hora</th>
                <th style="padding: 10px;">Paciente</th>
                <th style="padding: 10px;">Documento</th>
                <th style="padding: 10px;">Odontólogo</th>
                <th style="padding: 10px;">Sillón</th>
                <th style="padding: 10px;">Motivo / CUPS</th>
                <th style="padding: 10px;">Estado</th>
                <th style="padding: 10px; text-align: right;">Acción</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px; font-weight: 600;">08:30 AM</td>
                <td style="padding: 10px; font-weight: 600; color: #0284c7;">María Fernanda Gómez</td>
                <td style="padding: 10px;">CC 1018234567</td>
                <td style="padding: 10px;">Dr. Carlos Morales</td>
                <td style="padding: 10px;">Sillón 1</td>
                <td style="padding: 10px;">Obturación Resina (CUPS 232101)</td>
                <td style="padding: 10px;">
                    <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                        EN SALA ESPERA
                    </span>
                </td>
                <td style="padding: 10px; text-align: right;">
                    <a href="/clinico/historias/1/odontograma/vista" style="display: inline-block; padding: 4px 10px; background: #0284c7; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                        🩺 Iniciar Atención
                    </a>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px; font-weight: 600;">09:30 AM</td>
                <td style="padding: 10px; font-weight: 600; color: #0284c7;">Jorge Eduardo Ramírez</td>
                <td style="padding: 10px;">CC 79845123</td>
                <td style="padding: 10px;">Dr. Carlos Morales</td>
                <td style="padding: 10px;">Sillón 2</td>
                <td style="padding: 10px;">Profilaxis y Detartraje (CUPS 240101)</td>
                <td style="padding: 10px;">
                    <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                        CONFIRMADA
                    </span>
                </td>
                <td style="padding: 10px; text-align: right;">
                    <a href="/clinico/historias/1/odontograma/vista" style="display: inline-block; padding: 4px 10px; background: #e2e8f0; color: #334155; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">
                        Ver Historia
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
