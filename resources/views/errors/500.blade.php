<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 – Error del servidor · RapidBistro</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            background-color: #030712;
            color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .container {
            text-align: center;
            max-width: 480px;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background-color: #ef4444;
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #ef4444, #f87171);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f9fafb;
            margin-bottom: 0.75rem;
        }
        .description {
            color: #6b7280;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .divider {
            width: 40px;
            height: 2px;
            background: #ef4444;
            margin: 0 auto 2rem;
            border-radius: 9999px;
        }
        .alert {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .alert-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .alert-body {
            font-size: 0.8125rem;
            color: #9ca3af;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: #f59e0b;
            color: #111827;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-primary:hover { background: #fbbf24; }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: #1f2937;
            color: #d1d5db;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #374151;
            transition: background 0.15s;
            cursor: pointer;
        }
        .btn-secondary:hover { background: #374151; color: #f9fafb; }
        .footer {
            margin-top: 3rem;
            color: #374151;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">!</div>
        <div class="code">500</div>
        <div class="divider"></div>
        <h1 class="title">Error interno del servidor</h1>
        <p class="description">
            Algo salió mal en nuestro servidor.<br>
            Estamos trabajando para resolverlo.
        </p>
        <div class="alert">
            <p class="alert-title">¿Qué puedes hacer?</p>
            <p class="alert-body">
                Intenta recargar la página. Si el problema persiste, contacta al administrador del sistema.
            </p>
        </div>
        <div class="actions">
            <button onclick="window.location.reload()" class="btn-primary">
                ↺ Recargar página
            </button>
            <a href="/dashboard" class="btn-secondary">
                ← Ir al inicio
            </a>
        </div>
        <p class="footer">RapidBistro &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
