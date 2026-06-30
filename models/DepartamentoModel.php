<?php
class DepartamentoModel {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function obtenerTodos(): array {
        return $this->pdo->query("
            SELECT d.depto_cod, d.nombre, l.nombre AS localidad, l.provincia,
                   COUNT(e.legajo) AS empleados
            FROM Departamento d
            JOIN Localidad l ON l.localidad_cod = d.localidad_cod
            LEFT JOIN Empleado e ON e.depto_cod = d.depto_cod
            GROUP BY d.depto_cod, d.nombre, l.nombre, l.provincia
            ORDER BY d.nombre
        ")->fetchAll();
    }

    public function obtenerPorId(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT d.depto_cod, d.nombre, d.localidad_cod,
                   l.nombre AS localidad, l.provincia
            FROM Departamento d
            JOIN Localidad l ON l.localidad_cod = d.localidad_cod
            WHERE d.depto_cod = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerLocalidades(): array {
        return $this->pdo->query("SELECT localidad_cod, nombre, provincia FROM Localidad ORDER BY nombre")->fetchAll();
    }

    public function crear(array $datos): void {
        $this->pdo->prepare("
            INSERT INTO Departamento (nombre, localidad_cod) VALUES (:nombre, :localidad_cod)
        ")->execute($datos);
    }

    public function actualizar(array $datos): void {
        $this->pdo->prepare("
            UPDATE Departamento SET nombre=:nombre, localidad_cod=:localidad_cod
            WHERE depto_cod=:depto_cod
        ")->execute($datos);
    }

    public function tieneEmpleados(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Empleado WHERE depto_cod=?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function eliminar(int $id): void {
        $this->pdo->prepare("DELETE FROM Departamento WHERE depto_cod=?")->execute([$id]);
    }
}