<?php
require_once __DIR__ . '/../models/DepartamentoModel.php';

class DepartamentoController {
    private DepartamentoModel $modelo;
    public function __construct(PDO $pdo) { $this->modelo = new DepartamentoModel($pdo); }

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
        $departamentos = $this->modelo->obtenerTodos();
        require_once __DIR__ . '/../views/departamentos/listado.php';
    }

    private function formulario(): void {
        $id            = (int)($_GET['id'] ?? 0);
        $departamento  = $id ? $this->modelo->obtenerPorId($id) : null;
        $localidades   = $this->modelo->obtenerLocalidades();
        $error         = '';
        require_once __DIR__ . '/../views/departamentos/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php?page=departamentos'); exit; }

        $id       = (int)($_POST['depto_cod'] ?? 0);
        $es_nuevo = (bool)($_POST['es_nuevo'] ?? 0);
        $datos = [
            ':nombre'        => trim($_POST['nombre']        ?? ''),
            ':localidad_cod' => (int)($_POST['localidad_cod'] ?? 0),
        ];

        if (empty($datos[':nombre']) || !$datos[':localidad_cod']) {
            $error        = 'Completá todos los campos obligatorios.';
            $departamento = $es_nuevo ? null : $this->modelo->obtenerPorId($id);
            $localidades  = $this->modelo->obtenerLocalidades();
            require_once __DIR__ . '/../views/departamentos/formulario.php'; return;
        }

        try {
            if ($es_nuevo) { $this->modelo->crear($datos); }
            else { $datos[':depto_cod'] = $id; $this->modelo->actualizar($datos); }
            header('Location: index.php?page=departamentos&ok=1'); exit;
        } catch (PDOException $e) {
            $error        = 'Error al guardar: ' . $e->getMessage();
            $departamento = $es_nuevo ? null : $this->modelo->obtenerPorId($id);
            $localidades  = $this->modelo->obtenerLocalidades();
            require_once __DIR__ . '/../views/departamentos/formulario.php';
        }
    }

    private function eliminar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            if ($this->modelo->tieneEmpleados($id)) { header('Location: index.php?page=departamentos&error=tiene_empleados'); exit; }
            try { $this->modelo->eliminar($id); } catch (PDOException $e) { header('Location: index.php?page=departamentos&error=fk'); exit; }
        }
        header('Location: index.php?page=departamentos&ok=2'); exit;
    }
}