<?php
// index.php — Router principal del proyecto

session_start();
require_once 'config/db.php';

$page = $_GET['page'] ?? 'login';

// Páginas que NO requieren login
$paginas_publicas = ['login', 'logout'];

// Si la página es protegida, aplicar guard
if (!in_array($page, $paginas_publicas)) {
    require_once 'guards/auth_guard.php';
}

// Mapa de rutas → controlador/acción
switch ($page) {

    case 'login':
        require_once 'controllers/AuthController.php';
        $ctrl = new AuthController($pdo);
        $ctrl->login();
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $ctrl = new AuthController($pdo);
        $ctrl->logout();
        break;

    case 'dashboard':
        require_once 'views/dashboard/index.php';
        break;

    case 'empleados':
    require_once 'controllers/EmpleadoController.php';
    $ctrl = new EmpleadoController($pdo);
    $ctrl->manejar();
    break;

    case 'solicitudes':
    require_once 'controllers/SolicitudController.php';
    $ctrl = new SolicitudController($pdo);
    $ctrl->manejar();
    break;

    case 'evaluaciones':
        require_once 'controllers/EvaluacionController.php';
        $ctrl = new EvaluacionController($pdo);
        $ctrl->manejar();
    break;

    default:
        http_response_code(404);
        echo "<h1>Página no encontrada</h1>";
    break;
}