<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Acceso denegado · RapidBistro</title>
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
        .container { text-align: center; max-width: 480px; }
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background-color: #f59e0b;
            color: #111827;
            font-weight: 700;
            font-size: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        .divider { width: 40px; height: 2px; background: #f59e0b; margin: 0 auto 2rem; border-radius: 9999px; }
        .title { font-size: 1.5rem; font-weight: 600; color: #f9fafb; margin-bottom: 0.75rem; }
        .description { color: #6b7280; font-size: 0.9375rem; line-height: 1.6; margin-bottom: 2rem; }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            padding: 0.625rem 1.25rem;
            background: #f59e0b;
            color: #111827;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 8px;
            text-decoration: none;
        }
        .btn-primary:hover { background: #fbbf24; }
        .footer { margin-top: 3rem; color: #374151; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">R</div>
        <div class="code">403</div>
        <div class="divider"></div>
        <h1 class="title">Acceso denegado</h1>
        <p class="description">
            No tienes permisos para acceder a esta sección.<br>
            Contacta a un administrador si crees que es un error.
        </p>
        <div class="actions">
            <a href="/dashboard" class="btn-primary">← Volver al Dashboard</a>
        </div>
        <p class="footer">RapidBistro &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
