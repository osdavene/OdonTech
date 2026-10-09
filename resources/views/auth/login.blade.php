<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - OdonTech Colombia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .tarjeta-login {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
        }
        .cabecera-login {
            background: #0284c7;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            padding: 30px 28px;
            color: #ffffff;
            text-align: center;
        }
        .logo-login {
            font-size: 2.2rem;
            margin-bottom: 6px;
        }
        .cuerpo-login {
            padding: 32px 28px;
        }
        .campo-formulario {
            margin-bottom: 18px;
        }
        .campo-formulario label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .campo-formulario input[type="email"],
        .campo-formulario input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .campo-formulario input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .btn-ingresar {
            width: 100%;
            padding: 12px;
            background: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-ingresar:hover {
            background: #0369a1;
        }
        .alerta-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        .alerta-exito {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        /* Botones de Acceso Rápido Demo */
        .seccion-demo {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .titulo-demo {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: center;
        }
        .grid-roles-demo {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .btn-rol-demo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }
        .btn-rol-demo:hover {
            background: #f0f9ff;
            border-color: #7dd3fc;
            transform: translateY(-1px);
        }
        .pie-login {
            padding: 16px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="tarjeta-login">
        <div class="cabecera-login">
            <div class="logo-login">🦷</div>
            <h1 style="margin: 0; font-size: 1.4rem; font-weight: 700;">OdonTech Colombia</h1>
            <p style="margin: 4px 0 0 0; font-size: 0.85rem; opacity: 0.9;">
                Gestión Clínica Odontológica & Cumplimiento Sanitario
            </p>
        </div>

        <div class="cuerpo-login">
            @if (session('mensaje'))
                <div class="alerta-exito">
                    ✓ {{ session('mensaje') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alerta-error">
                    <strong>⚠️ Error de autenticación:</strong>
                    <ul style="margin: 6px 0 0 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('seguridad.ingresar') }}" method="POST" id="form-login">
                @csrf
                <div class="campo-formulario">
                    <label for="correo_electronico">Correo Electrónico Institucional</label>
                    <input type="email" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" required placeholder="nombre@odontech.com.co" autofocus>
                </div>

                <div class="campo-formulario">
                    <label for="contrasena">Contraseña de Acceso</label>
                    <input type="password" id="contrasena" name="contrasena" required placeholder="••••••••">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #475569; cursor: pointer;">
                        <input type="checkbox" name="recordar" value="1"> Recordar mi sesión
                    </label>
                </div>

                <button type="submit" class="btn-ingresar">
                    🔐 Iniciar Sesión Segura
                </button>
            </form>

            <!-- Acceso Rápido Demo por Roles -->
            <div class="seccion-demo">
                <div class="titulo-demo">Acceso Rápido por Perfil (Entorno Demo)</div>
                <div class="grid-roles-demo">
                    <button type="button" class="btn-rol-demo" onclick="llenarYEnviar('odontologo@odontech.com.co', 'Odonto2026*')">
                        <div>
                            <strong style="color: #0369a1; font-size: 0.85rem;">🩺 Dr. Carlos Morales</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Rol: Odontólogo (ReTHUS: TP-ODO-54891)</div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: bold; color: #0284c7; background: #e0f2fe; padding: 4px 8px; border-radius: 4px;">Entrar →</span>
                    </button>

                    <button type="button" class="btn-rol-demo" onclick="llenarYEnviar('admin@odontech.com.co', 'Admin2026*')">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.85rem;">👑 Dra. Claudia Pérez</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Rol: Administradora (Auditorías & DIAN)</div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: bold; color: #475569; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">Entrar →</span>
                    </button>

                    <button type="button" class="btn-rol-demo" onclick="llenarYEnviar('asistente@odontech.com.co', 'Asistente2026*')">
                        <div>
                            <strong style="color: #059669; font-size: 0.85rem;">📋 Andrea Castillo</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Rol: Asistente (Admisión & Agenda)</div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: bold; color: #059669; background: #ecfdf5; padding: 4px 8px; border-radius: 4px;">Entrar →</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="pie-login">
            🛡️ Sellado de Auditoría Criptográfica SHA-256 (Ley 1581 de 2012)<br>
            Toda interacción queda registrada con IP, agente y marca temporal.
        </div>
    </div>

    <script>
        function llenarYEnviar(correo, pass) {
            document.getElementById('correo_electronico').value = correo;
            document.getElementById('contrasena').value = pass;
            document.getElementById('form-login').submit();
        }
    </script>
</body>
</html>
