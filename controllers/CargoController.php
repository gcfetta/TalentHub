<?php
require_once __DIR__ . '/../models/CargoModel.php';

class CargoController {
    private CargoModel $modelo;
    public function __construct(PDO $pdo) { $this->modelo = new CargoModel($pdo); }

    public function manejar(): void {
        match($_GET['accion'] ?? 'listado') {
            'nuevo'    => $this->formulario(),
            'editar'   => $this->formulario(),
            'guardar'  => $this->guardar(),
            'eliminar' => $this->eliminar(),
            default    => $this->listado(),
        };
    }

    private function listado(): void {
        $cargos = $this->modelo->obtenerTodos();
        require_once __DIR__ . '/../views/cargos/listado.php';
    }

    private function formulario(): void {
        $id      = (int)($_GET['id'] ?? 0);
        $cargo   = $id ? $this->modelo->obtenerPorId($id) : null;
        $niveles = $this->modelo->obtenerNiveles();
        $error   = '';
        require_once __DIR__ . '/../views/cargos/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=cargos'); exit; }

        $id       = (int)($_POST['cargo_cod'] ?? 0);
        $es_nuevo = (bool)($_POST['es_nuevo'] ?? 0);
        $datos = [
            ':nombre'    => trim($_POST['nombre']    ?? ''),
            ':banda_min' => (float)($_POST['banda_min'] ?? 0),
            ':banda_max' => (float)($_POST['banda_max'] ?? 0),
            ':nivel_cod' => (int)($_POST['nivel_cod']   ?? 0),
        ];

        if (empty($datos[':nombre']) || !$datos[':nivel_cod'] || $datos[':banda_min'] <= 0 || $datos[':banda_max'] <= 0) {
            $error = 'Completá todos los campos obligatorios con valores válidos.';
            $cargo = $es_nuevo ? null : $this->modelo->obtenerPorId($id);
            $niveles = $this->modelo->obtenerNiveles();
            require_once __DIR__ . '/../views/cargos/formulario.php'; return;
        }
        if ($datos[':banda_min'] > $datos[':banda_max']) {
            $error = 'La banda mínima no puede superar a la máxima.';
            $cargo = $es_nuevo ? null : $this->modelo->obtenerPorId($id);
            $niveles = $this->modelo->obtenerNiveles();
            require_once __DIR__ . '/../views/cargos/formulario.php'; return;
        }

        try {
            if ($es_nuevo) { $this->modelo->crear($datos); }
            else { $datos[':cargo_cod'] = $id; $this->modelo->actualizar($datos); }
            header('Location: index.php?page=cargos&ok=1'); exit;
        } catch (PDOException $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
            $cargo = $es_nuevo ? null : $this->modelo->obtenerPorId($id);
            $niveles = $this->modelo->obtenerNiveles();
            require_once __DIR__ . '/../views/cargos/formulario.php';
        }
    }

    private function eliminar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            if ($this->modelo->tieneEmpleados($id)) { header('Location: index.php?page=cargos&error=tiene_empleados'); exit; }
            try { $this->modelo->eliminar($id); } catch (PDOException $e) { header('Location: index.php?page=cargos&error=fk'); exit; }
        }
        header('Location: index.php?page=cargos&ok=2'); exit;
    }
}