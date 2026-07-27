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
        $cargos        = $this->modelo->obtenerCargos();
        $departamentos = $this->modelo->obtenerDepartamentos();
        $localidades   = $this->modelo->obtenerLocalidades();
        $supervisores  = $this->modelo->obtenerParaSelect();
        $cargo_actual  = $legajo ? $this->modelo->obtenerCargoActual($legajo) : null;
        $proximo_legajo = $empleado === null ? $this->modelo->obtenerProximoLegajo() : null;
        $ocupantes_por_cargo = $this->modelo->contarOcupantesPorCargo();
        $error = '';
        require_once __DIR__ . '/../views/empleados/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=empleados');
            exit;
        }

        $legajo   = (int)($_POST['legajo']   ?? 0);
        $es_nuevo = !empty($_POST['es_nuevo']);

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
        $cargo_cod = !empty($_POST['cargo_cod']) ? (int)$_POST['cargo_cod'] : null;

        $recargar = function (string $mensaje) use ($es_nuevo, $legajo) {
            $error         = $mensaje;
            $cargos        = $this->modelo->obtenerCargos();
            $departamentos = $this->modelo->obtenerDepartamentos();
            $localidades   = $this->modelo->obtenerLocalidades();
            $supervisores  = $this->modelo->obtenerParaSelect();
            $empleado      = $es_nuevo ? null : $this->modelo->obtenerPorLegajo($legajo);
            $cargo_actual  = $legajo ? $this->modelo->obtenerCargoActual($legajo) : null;
            $proximo_legajo = $es_nuevo ? $this->modelo->obtenerProximoLegajo() : null;
            $ocupantes_por_cargo = $this->modelo->contarOcupantesPorCargo();
            require_once __DIR__ . '/../views/empleados/formulario.php';
        };

        foreach ([':nombre', ':apellido', ':mail', ':fecha_ingreso'] as $campo) {
            if (empty($datos[$campo])) {
                $recargar('Completá todos los campos obligatorios.');
                return;
            }
        }

        if (!filter_var($datos[':mail'], FILTER_VALIDATE_EMAIL)) {
            $recargar('El mail ingresado no es válido.');
            return;
        }

        if ($datos[':localidad_cod'] <= 0 || $datos[':depto_cod'] <= 0) {
            $recargar('Seleccioná una localidad y un departamento válidos.');
            return;
        }

        if ($es_nuevo && $this->modelo->legajoExiste($legajo)) {
            $recargar("El legajo {$legajo} ya existe.");
            return;
        }

        if ($cargo_cod && $this->modelo->esCargoGerencial($cargo_cod)) {
            $ocupante = $this->modelo->cargoOcupado($cargo_cod, $es_nuevo ? null : $legajo);
            if ($ocupante) {
                $recargar("Ese cargo gerencial ya está ocupado por {$ocupante['nombre_completo']} (legajo {$ocupante['legajo']}). Los cargos gerenciales admiten un solo ocupante activo.");
                return;
            }
        } try {
            if ($es_nuevo) {
                $this->modelo->crear($datos);

                if ($cargo_cod) {
                    $stmt = $this->modelo->getPdo()->prepare(
                        "INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde)
                        VALUES (?, ?, ?)"
                    );
                    $stmt->execute([$legajo, $cargo_cod, $datos[':fecha_ingreso']]);
                }
            } else {
                $this->modelo->actualizar($datos);

                $cargo_previo = $this->modelo->obtenerCargoActual($legajo);
                if ($cargo_cod !== $cargo_previo) {
                    $pdo = $this->modelo->getPdo();
                    $hoy = date('Y-m-d');

                    if ($cargo_cod !== null) {
                        // El cierre de la fila anterior en Historial_Cargo (fecha_hasta)
                        // lo hace tr_cerrar_cargo_anterior (BEFORE INSERT).
                        $pdo->prepare(
                            "INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde)
                            VALUES (?, ?, ?)"
                        )->execute([$legajo, $cargo_cod, $hoy]);
                    } elseif ($cargo_previo !== null) {
                        // Se quita el cargo sin asignar uno nuevo: no hay INSERT
                        // que dispare el trigger, así que acá sí hace falta cerrar
                        // la fila manualmente.
                        $pdo->prepare(
                            "UPDATE Historial_Cargo SET fecha_hasta = ?
                            WHERE legajo = ? AND cargo_cod = ? AND fecha_hasta IS NULL"
                        )->execute([$hoy, $legajo, $cargo_previo]);
                    }
                }

                if ($legajo === (int)($_SESSION['legajo'] ?? 0)) {
                    $_SESSION['nombre'] = $datos[':nombre'] . ' ' . $datos[':apellido'];
                }
            }

            header('Location: index.php?page=empleados&ok=1');
            exit;

        } catch (PDOException $e) {
            $mensaje = ($e->getCode() == 23000)
                ? 'Ya existe un empleado con ese mail.'
                : 'Error al guardar: ' . $e->getMessage();
            $recargar($mensaje);
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
        $filas = $this->modelo->obtenerReporteSueldos();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_sueldos_talenthub.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['legajo','empleado','cargo','banda_salarial_min','banda_salarial_max','anios_antiguedad']);
        foreach ($filas as $fila) {
            fputcsv($out, $fila);
        }
        fclose($out);
        exit;
    }
}