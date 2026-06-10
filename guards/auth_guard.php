<?php
// guards/auth_guard.php — Incluir al inicio de cada página protegida

$TIMEOUT = 30 * 60; // 30 minutos de inactividad

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit;
}

if ((time() - $_SESSION['ultimo_acceso']) > $TIMEOUT) {
    session_destroy();
    header('Location: index.php?page=login&motivo=timeout');
    exit;
}

$_SESSION['ultimo_acceso'] = time();