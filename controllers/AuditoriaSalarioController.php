<?php
// controllers/AuditoriaSalarioController.php
require_once __DIR__ . '/../models/AuditoriaSalarioModel.php';

class AuditoriaSalarioController {
    private AuditoriaSalarioModel $modelo;
    public function __construct(PDO $pdo) { $this->modelo = new AuditoriaSalarioModel($pdo); }

    public function manejar(): void {
        if (!in_array($_SESSION['rol'], ['Administrador', 'RRHH'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        $registros = $this->modelo->obtenerTodos();
        require_once __DIR__ . '/../views/auditoria_salario/listado.php';
    }
}