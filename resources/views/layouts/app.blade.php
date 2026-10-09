<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'OdonTech Colombia') - Software Odontológico Integral</title>
    <link rel="stylesheet" href="/css/odontograma.css">
    <link rel="stylesheet" href="/css/periodontograma.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Barra Lateral de Navegación */
        .barra-lateral {
            width: 260px;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .logo-odontech {
            padding: 24px 20px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #38bdf8;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .menu-navegacion {
            list-style: none;
            padding: 16px 0;
            margin: 0;
            flex-grow: 1;
        }
        .menu-navegacion li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .menu-navegacion li a:hover,
        .menu-navegacion li.activo a {
            background: #1e293b;
            color: #ffffff;
            border-left: 4px solid #0284c7;
        }
        .pie-usuario {
            padding: 16px 20px;
            border-top: 1px solid #1e293b;
            font-size: 0.8rem;
            color: #94a3b8;
            background: #090d16;
        }
        /* Contenido Principal */
        .area-principal {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .barra-superior {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .contenido-pagina {
            padding: 28px;
            flex-grow: 1;
        }
        .tarjeta-blanca {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
    </style>
    @stack('estilos')
</head>
<body>
    <!-- Barra Lateral -->
    <aside class="barra-lateral">
        <div class="logo-odontech">
            <span>🦷</span> OdonTech Colombia
        </div>
        <ul class="menu-navegacion">
            <li class="{{ request()->is('dashboard') ? 'activo' : '' }}">
                <a href="/dashboard"><span>📊</span> Panel Principal</a>
            </li>
            <li class="{{ request()->is('agenda*') ? 'activo' : '' }}">
                <a href="/agenda/citas"><span>📅</span> Agenda & Citas</a>
            </li>
            <li class="{{ request()->is('pacientes*') ? 'activo' : '' }}">
                <a href="/pacientes/nuevo"><span>👤</span> Admisión & Habeas Data</a>
            </li>
            <li class="{{ request()->is('clinico*') ? 'activo' : '' }}">
                <a href="/clinico/estacion"><span>🩺</span> Estación Clínica Unificada</a>
            </li>
            <li class="{{ request()->is('facturacion*') ? 'activo' : '' }}">
                <a href="/facturacion/panel"><span>🧾</span> Facturación DIAN & RIPS</a>
            </li>
            <li class="{{ request()->is('inventario*') ? 'activo' : '' }}">
                <a href="/inventario/insumos"><span>📦</span> Inventario INVIMA</a>
            </li>
        </ul>
        <div class="pie-usuario">
            <div style="margin-bottom: 6px;">
                <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: bold;">Usuario en Turno:</span>
                <div style="color: #f1f5f9; font-weight: 600; font-size: 0.85rem; margin-top: 2px;">
                    {{ Auth::user()->nombre_completo ?? 'Dr. Carlos Morales' }}
                </div>
                <div style="color: #38bdf8; font-size: 0.75rem; margin-top: 1px;">
                    Rol: {{ Auth::user()->rol->nombre ?? 'Odontólogo' }}
                </div>
                @if (Auth::user()?->registro_profesional)
                    <div style="color: #94a3b8; font-size: 0.7rem; margin-top: 1px;">
                        ReTHUS: {{ Auth::user()->registro_profesional }}
                    </div>
                @endif
            </div>
            <form action="{{ route('seguridad.salir') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <button type="submit" style="width: 100%; padding: 6px 10px; background: #1e293b; color: #f8fafc; border: 1px solid #334155; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                    🚪 Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- Área Principal -->
    <main class="area-principal">
        <header class="barra-superior">
            <div>
                <strong style="color: #0369a1;">Sede Principal - Consultorio 401</strong> | MinSalud REPS: 110010000001
            </div>
            <div>
                <span class="insignia-normativa" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                    ✓ Ley 1581 | Res. 1995/1999 | Res. 2275/2023 | INVIMA
                </span>
            </div>
        </header>

        <section class="contenido-pagina">
            @yield('contenido')
        </section>
    </main>

    @stack('scripts')
</body>
</html>
