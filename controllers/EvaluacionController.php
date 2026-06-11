<?php
// controllers/EvaluacionController.php

require_once __DIR__ . '/../models/EvaluacionModel.php';

class EvaluacionController {
    private $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new EvaluacionModel($pdo);
    }

    public function manejar(): void {
        $accion = $_GET['accion'] ?? 'listado';

        match($accion) {
            'listado' => $this->listado(),
            'ver'     => $this->ver(),
            'nueva'   => $this->formulario(),
            'guardar' => $this->guardar(),
            default   => $this->listado(),
        };
    }

    private function listado(): void {
        $rol    = $_SESSION['rol'];
        $legajo = (int)$_SESSION['legajo'];

        // Empleado solo ve las suyas; roles con permisos ven todas
        $filtro = in_array($rol, ['Administrador', 'RRHH', 'Supervisor']) ? null : $legajo;
        $evaluaciones = $this->modelo->obtenerTodas($filtro);

        require_once __DIR__ . '/../views/evaluaciones/listado.php';
    }

    private function ver(): void {
        $id = (int)($_GET['id'] ?? 0);
        $evaluacion = $this->modelo->obtenerPorId($id);

        if (!$evaluacion) {
            header('Location: index.php?page=evaluaciones');
            exit;
        }

        // Empleado solo puede ver las suyas
        if ($_SESSION['rol'] === 'Empleado' && $evaluacion['legajo'] != $_SESSION['legajo']) {
            header('Location: index.php?page=evaluaciones');
            exit;
        }

        $historial = $this->modelo->obtenerHistorialEmpleado((int)$evaluacion['legajo']);
        $promedio  = $this->modelo->promedioEmpleado((int)$evaluacion['legajo']);
        require_once __DIR__ . '/../views/evaluaciones/detalle.php';
    }

    private function formulario(): void {
        // Solo RRHH, Admin y Supervisor pueden crear evaluaciones
        if (!in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])) {
            header('Location: index.php?page=evaluaciones');
            exit;
        }
        $empleados = $this->modelo->obtenerEmpleados();
        $error     = '';
        require_once __DIR__ . '/../views/evaluaciones/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=evaluaciones');
            exit;
        }

        if (!in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])) {
            header('Location: index.php?page=evaluaciones');
            exit;
        }

        $legajo   = (int)($_POST['legajo']   ?? 0);
        $periodo  = trim($_POST['periodo']   ?? '');
        $puntaje  = $_POST['puntaje']        ?? '';
        $obs      = trim($_POST['observaciones'] ?? '');
        $fecha    = $_POST['fecha_evaluacion'] ?? '';
        $error    = '';

        // Validaciones
        if (!$legajo || !$periodo || $puntaje === '' || !$fecha) {
            $error = 'Completá todos los campos obligatorios.';
        } elseif ((float)$puntaje < 0 || (float)$puntaje > 10) {
            $error = 'El puntaje debe estar entre 0 y 10.';
        } elseif ($legajo == (int)$_SESSION['legajo']) {
            $error = 'No podés evaluarte a vos mismo.';
        } elseif ($this->modelo->existePeriodo($legajo, $periodo)) {
            $error = "Ya existe una evaluación para ese empleado en el período «{$periodo}».";
        }

        if ($error) {
            $empleados = $this->modelo->obtenerEmpleados();
            require_once __DIR__ . '/../views/evaluaciones/formulario.php';
            return;
        }

        $datos = [
            ':legajo'            => $legajo,
            ':periodo'           => $periodo,
            ':puntaje'           => number_format((float)$puntaje, 2, '.', ''),
            ':observaciones'     => $obs ?: null,
            ':evaluador_legajo'  => (int)$_SESSION['legajo'],
            ':fecha_evaluacion'  => $fecha,
        ];

        try {
            $id = $this->modelo->crear($datos);
            header("Location: index.php?page=evaluaciones&accion=ver&id={$id}&ok=1");
            exit;
        } catch (Exception $e) {
            $error     = 'Error al guardar: ' . $e->getMessage();
            $empleados = $this->modelo->obtenerEmpleados();
            require_once __DIR__ . '/../views/evaluaciones/formulario.php';
        }
    }
}