<?php
// models/AuditoriaSalarioModel.php

class AuditoriaSalarioModel {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function obtenerTodos(): array {
        return $this->pdo->query("
            SELECT a.auditoria_sal_id, a.fecha_cambio,
                   e.legajo, CONCAT(e.apellido, ', ', e.nombre) AS empleado,
                   ca.nombre AS cargo_anterior, ca.banda_salarial_min AS ant_min, ca.banda_salarial_max AS ant_max,
                   cn.nombre AS cargo_nuevo,    cn.banda_salarial_min AS nvo_min, cn.banda_salarial_max AS nvo_max
            FROM Auditoria_Salario a
            JOIN Empleado e ON e.legajo = a.legajo
            JOIN Cargo ca   ON ca.cargo_cod = a.cargo_anterior_cod
            JOIN Cargo cn   ON cn.cargo_cod = a.cargo_nuevo_cod
            ORDER BY a.fecha_cambio DESC
        ")->fetchAll();
    }
}