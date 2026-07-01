<?php
// views/auth/activar_cuenta.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentHub — Activar cuenta</title>
    <link rel="stylesheet" href="/TalentHub/public/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f0f2f7;
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            position: relative;
            overflow: hidden;
        }
        body::before, body::after {
            content: "";
            position: fixed;
            width: 420px; height: 420px;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            z-index: 0;
        }
        body::before { top: -120px; left: -120px; background: #c4b5fd; }
        body::after  { bottom: -120px; right: -120px; background: #fbcfe8; }

        .login-box {
            position: relative;
            z-index: 1;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 20px 45px -12px rgba(30, 27, 75, 0.18);
        }
        .login-box .brand-row {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.8rem;
        }
        .login-box .brand-mark {
            width: 46px; height: 46px;
            flex-shrink: 0;
            border-radius: 16px;
            background: #7c3aed;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 8px 18px -6px rgba(124, 58, 237, 0.45);
        }
        .login-box h1 { color: #1e1b4b; margin: 0; font-size: 1.3rem; font-weight: 800; }
        .login-box p.sub { color: #6b7280; margin: 0.15rem 0 0; font-size: 0.78rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block; color: #1e1b4b; font-size: 0.85rem;
            font-weight: 700; margin-bottom: 0.4rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #f3f4f6;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            color: #1e1b4b;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .form-group input:focus {
            background: #ffffff;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.18);
        }
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: #7c3aed;
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 0.4rem;
            box-shadow: 0 8px 18px -6px rgba(124, 58, 237, 0.4);
        }
        .btn-login:hover  { background: #6d28d9; }
        .btn-login:active { transform: scale(0.98); }
        .login-box form > a, .login-box > a {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #7c3aed;
            text-decoration: underline;
        }
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="brand-row">
        <div class="brand-mark">🔑</div>
        <div>
            <h1>Activar cuenta</h1>
            <p class="sub">Ingresá tu legajo y el mail registrado en RRHH</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($ok): ?>
        <div class="alert alert-success">
            Cuenta activada correctamente. Tu contraseña inicial es tu número de legajo.
        </div>
        <a href="index.php?page=login">Ir a iniciar sesión →</a>
    <?php else: ?>
        <form method="POST" action="index.php?page=activar_cuenta">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label for="legajo">Legajo</label>
                <input type="number" id="legajo" name="legajo" placeholder="Ingresá tu legajo" required autofocus>
            </div>
            <div class="form-group">
                <label for="mail">Mail</label>
                <input type="email" id="mail" name="mail" placeholder="Mail registrado en RRHH" required>
            </div>
            <button type="submit" class="btn-login">Activar cuenta →</button>
            <a href="index.php?page=login">Volver a iniciar sesión</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>