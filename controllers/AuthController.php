<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new UsuarioModel($pdo);
    }

    public function login(): void {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $legajo   = trim($_POST['legajo'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($legajo) || empty($password)) {
                $error = 'Completá todos los campos.';
            } elseif (!ctype_digit($legajo)) {
                $error = 'Legajo o contraseña incorrectos.';
            } else {
                $user = $this->modelo->login((int)$legajo, $password);

                if ($user) {
                    session_regenerate_id(true); // previene Session Fixation

                    $_SESSION['usuario_id']  = $user['usuario_id'];
                    $_SESSION['rol']         = $user['rol'];
                    $_SESSION['legajo']      = $user['legajo'];
                    $_SESSION['nombre']      = $user['nombre'] . ' ' . $user['apellido'];
                    $_SESSION['ultimo_acceso'] = time();

                    if ($user['debe_cambiar_password']) {
                        header('Location: index.php?page=cambiar_password');
                    } else {
                        header('Location: index.php?page=dashboard');
                    }
                    exit;
                } else {
                    $error = 'Legajo o contraseña incorrectos.';
                }
            }
        }

        // Mostrar la vista de login
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function activarCuenta(): void {
    $error = '';
    $ok    = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_activacion'] ?? '', $_POST['csrf_token'])) {
            $error = 'Token de seguridad inválido, recargá la página e intentá de nuevo.';
        } else {
            $legajo = trim($_POST['legajo'] ?? '');
            $mail   = trim($_POST['mail'] ?? '');

            if (empty($legajo) || empty($mail)) {
                $error = 'Completá todos los campos.';
            } elseif (!ctype_digit($legajo)) {
                $error = 'Los datos ingresados no coinciden con ningún empleado activo.';
            } else {
                try {
                    $this->modelo->activarCuenta((int)$legajo, $mail);
                    $ok = true;
                    unset($_SESSION['csrf_activacion']); // token de un solo uso
                } catch (Exception $e) {
                    $error = 'Los datos ingresados no coinciden con ningún empleado activo, o ese legajo ya tiene una cuenta.';
                }
            }
        }
    }

    // Token nuevo para el próximo intento (evita reenvío del mismo form)
    if (empty($_SESSION['csrf_activacion'])) {
            $_SESSION['csrf_activacion'] = bin2hex(random_bytes(32));
        }
        $csrf_token = $_SESSION['csrf_activacion'];

        require_once __DIR__ . '/../views/auth/activar_cuenta.php';
    }

    public function cambiarPassword(): void {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actual = trim($_POST['actual'] ?? '');
            $nueva  = trim($_POST['nueva'] ?? '');
            $repetir = trim($_POST['repetir'] ?? '');

            if (empty($actual) || empty($nueva) || empty($repetir)) {
                $error = 'Completá todos los campos.';
            } elseif ($nueva !== $repetir) {
                $error = 'Las contraseñas nuevas no coinciden.';
            } elseif (strlen($nueva) < 6) {
                $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
            } else {
                $ok = $this->modelo->cambiarPassword($_SESSION['usuario_id'], $actual, $nueva);
                if ($ok) {
                    header('Location: index.php?page=dashboard');
                    exit;
                }
                $error = 'La contraseña actual es incorrecta.';
            }
        }

        require_once __DIR__ . '/../views/auth/cambiar_password.php';
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Location: index.php?page=login');
        exit;
    }
}