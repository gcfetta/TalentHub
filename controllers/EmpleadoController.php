<?php
// controllers/EmpleadoController.php

require_once __DIR__ . '/../models/EmpleadoModel.php';

class EmpleadoController {
    private $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new EmpleadoModel($pdo);
    }

    // GET  ?page=empleados              → listado
    // GET  ?page=empleados&accion=ver&legajo=X  → detalle
    // GET  ?page=empleados&accion=nuevo         → formulario vacío
    // GET  ?page=empleados&accion=editar&legajo=X → formulario con datos
    // POST ?page=empleados&accion=guardar       → crear/actualizar
    // GET  ?page=empleados&accion=eliminar&legajo=X → eliminar
    public function manejar(): void {
        $accion = $_GET['accion'] ?? 'listado';

        match($accion) {
            'listado'  => $this->listado(),
            'ver'      => $this->ver(),
            'nuevo'    => $this->formulario(),
            'editar'   => $this->formulario(),
            'guardar'  => $this->guardar(),
            'eliminar' => $this->eliminar(),
            'exportar' => $this->exportarCSV(),
            default    => $this->listado(),
        };
    }

    private function listado(): void {
        $empleados = $this->modelo->obtenerTodos();
        require_once __DIR__ . '/../views/empleados/listado.php';
    }

    private function ver(): void {
        $legajo = (int)($_GET['legajo'] ?? 0);
        $empleado = $this->modelo->obtenerPorLegajo($legajo);

        if (!$empleado) {
            header('Location: index.php?page=empleados');
            exit;
        }

        $historial    = $this->modelo->obtenerHistorialCargos($legajo);
        $evaluaciones = $this->modelo->obtenerEvaluaciones($legajo);
        require_once __DIR__ . '/../views/empleados/detalle.php';
    }

    private function formulario(): void {
        $legajo   = (int)($_GET['legajo'] ?? 0);
        $empleado = $legajo ? $this->modelo->obtenerPorLegajo($legajo) : null;
        $cargos       = $this->modelo->obtenerCargos();
        $departamentos = $this->modelo->obtenerDepartamentos();
        $localidades  = $this->modelo->obtenerLocalidades();
        $supervisores = $this->modelo->obtenerParaSelect();
        $error = '';
        require_once __DIR__ . '/../views/empleados/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=empleados');
            exit;
        }

        $legajo   = (int)filter_input(INPUT_POST, 'legajo',   FILTER_SANITIZE_NUMBER_INT);
        $es_nuevo = (bool)filter_input(INPUT_POST, 'es_nuevo', FILTER_SANITIZE_NUMBER_INT);

        $datos = [
            ':legajo'            => $legajo,
            ':nombre'            => trim($_POST['nombre']   ?? ''),
            ':apellido'          => trim($_POST['apellido'] ?? ''),
            ':mail'              => trim($_POST['mail']     ?? ''),
            ':fecha_ingreso'     => $_POST['fecha_ingreso'] ?? '',
            ':telefono'          => trim($_POST['telefono'] ?? '') ?: null,
            ':localidad_cod'     => (int)($_POST['localidad_cod'] ?? 0),
            ':depto_cod'         => (int)($_POST['depto_cod']     ?? 0),
            ':supervisor_legajo' => ($_POST['supervisor_legajo'] !== '') ? (int)$_POST['supervisor_legajo'] : null,
        ];

        // Validación básica
        foreach ([':nombre', ':apellido', ':mail', ':fecha_ingreso'] as $campo) {
            if (empty($datos[$campo])) {
                $error = 'Completá todos los campos obligatorios.';
                $cargos        = $this->modelo->obtenerCargos();
                $departamentos = $this->modelo->obtenerDepartamentos();
                $localidades   = $this->modelo->obtenerLocalidades();
                $supervisores  = $this->modelo->obtenerParaSelect();
                $empleado = $es_nuevo ? null : $this->modelo->obtenerPorLegajo($legajo);
                require_once __DIR__ . '/../views/empleados/formulario.php';
                return;
            }
        }

        try {
            if ($es_nuevo) {
                if ($this->modelo->legajoExiste($legajo)) {
                    $error = "El legajo {$legajo} ya existe.";
                    $cargos        = $this->modelo->obtenerCargos();
                    $departamentos = $this->modelo->obtenerDepartamentos();
                    $localidades   = $this->modelo->obtenerLocalidades();
                    $supervisores  = $this->modelo->obtenerParaSelect();
                    $empleado = null;
                    require_once __DIR__ . '/../views/empleados/formulario.php';
                    return;
                }
                $this->modelo->crear($datos);

                // Si se eligió un cargo inicial, registrarlo en historial
                if (!empty($_POST['cargo_cod'])) {
                    $stmt = $this->modelo->getPdo()->prepare(
                        "INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde)
                         VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$legajo, (int)$_POST['cargo_cod'], $datos[':fecha_ingreso']]);
                }
            } else {
                $this->modelo->actualizar($datos);
            }

            header('Location: index.php?page=empleados&ok=1');
            exit;

        } catch (PDOException $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
            $cargos        = $this->modelo->obtenerCargos();
            $departamentos = $this->modelo->obtenerDepartamentos();
            $localidades   = $this->modelo->obtenerLocalidades();
            $supervisores  = $this->modelo->obtenerParaSelect();
            $empleado = $es_nuevo ? null : $this->modelo->obtenerPorLegajo($legajo);
            require_once __DIR__ . '/../views/empleados/formulario.php';
        }
    }

    private function eliminar(): void {
        $legajo = (int)($_GET['legajo'] ?? 0);
        if ($legajo) {
            try {
                $this->modelo->eliminar($legajo);
            } catch (PDOException $e) {
                // tiene FK activas — no se puede eliminar
            }
        }
        header('Location: index.php?page=empleados');
        exit;
    }

    private function exportarCSV(): void {
        $pdo = $this->modelo->getPdo();
        $stmt = $pdo->query(
            "SELECT e.legajo,
                    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                    c.nombre AS cargo,
                    c.banda_salarial_min,
                    c.banda_salarial_max,
                    calcular_antiguedad(e.fecha_ingreso) AS anios_antiguedad
             FROM Empleado e
             LEFT JOIN Historial_Cargo hc ON hc.legajo = e.legajo AND hc.fecha_hasta IS NULL
             LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod
             WHERE e.activo = 1"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_sueldos_talenthub.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['legajo','empleado','cargo','banda_salarial_min','banda_salarial_max','anios_antiguedad']);
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($out, $fila);
        }
        fclose($out);
        exit;
    }
}