<?php
// index.php — Router principal del proyecto

session_start();
require_once 'config/db.php';

$page = $_GET['page'] ?? 'login';

// Páginas que NO requieren login
$paginas_publicas = ['login', 'logout', 'activar_cuenta'];

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

    case 'activar_cuenta':
        require_once 'controllers/AuthController.php';
        $ctrl = new AuthController($pdo);
        $ctrl->activarCuenta();
    break;

    case 'cambiar_password':
        require_once 'controllers/AuthController.php';
        $ctrl = new AuthController($pdo);
        $ctrl->cambiarPassword();
    break;

    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        $ctrl = new DashboardController($pdo);
        $ctrl->index();
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

    case 'cargos':
        require_once 'controllers/CargoController.php';
        $ctrl = new CargoController($pdo);
        $ctrl->manejar();
    break;

    case 'departamentos':
        require_once 'controllers/DepartamentoController.php';
        $ctrl = new DepartamentoController($pdo);
        $ctrl->manejar();
    break;

    case 'auditoria_salario':
        require_once 'controllers/AuditoriaSalarioController.php';
        $ctrl = new AuditoriaSalarioController($pdo);
        $ctrl->manejar();
    break;

    default:
        http_response_code(404);
        echo "<h1>Página no encontrada</h1>";
    break;
}