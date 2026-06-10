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
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = 'Completá todos los campos.';
            } else {
                $user = $this->modelo->login($username, $password);

                if ($user) {
                    session_regenerate_id(true); // previene Session Fixation

                    $_SESSION['usuario_id']  = $user['usuario_id'];
                    $_SESSION['username']    = $user['username'];
                    $_SESSION['rol']         = $user['rol'];
                    $_SESSION['legajo']      = $user['legajo'];
                    $_SESSION['nombre']      = $user['nombre'] . ' ' . $user['apellido'];
                    $_SESSION['ultimo_acceso'] = time();

                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        // Mostrar la vista de login
        require_once __DIR__ . '/../views/auth/login.php';
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