<?php
// views/auth/login.php
$motivo = $_GET['motivo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentHub — Ingresar</title>
    <link rel="stylesheet" href="/TalentHub/public/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #0f172a;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            background: #1e293b;
            padding: 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .login-box h1 {
            color: #6366f1;
            margin: 0 0 0.25rem;
            font-size: 1.8rem;
        }
        .login-box p.sub {
            color: #94a3b8;
            margin: 0 0 1.8rem;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn-login:hover { background: #4f46e5; }
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
        .credenciales {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #0f172a;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #64748b;
        }
        .credenciales strong { color: #94a3b8; }
    </style>
</head>
<body>
<div class="login-box">
    <h1>TalentHub</h1>
    <p class="sub">Sistema de Recursos Humanos — U. Champagnat</p>

    <?php if ($motivo === 'timeout'): ?>
        <div class="alert alert-warning">Tu sesión expiró por inactividad.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=login">
        <div class="form-group">
            <label for="username">Usuario</label>
            <input type="number" id="legajo" name="legajo" 
                    placeholder="Ingresá tu legajo" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" 
                   placeholder="Ingresá tu contraseña" required>
        </div>
        <button type="submit" class="btn-login">Ingresar →</button>
        <a href="index.php?page=activar_cuenta">¿Todavía no activaste tu cuenta?</a>
    </form>

    <div class="credenciales">
        <strong>Usuarios de prueba:</strong><br>
        admin / admin123 &nbsp;·&nbsp; rrhh / rrhh123 &nbsp;·&nbsp; empleado / emp123
    </div>
</div>
</body>
</html>