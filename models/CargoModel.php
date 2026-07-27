<?php
class CargoModel {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function obtenerTodos(): array {
        return $this->pdo->query("
            SELECT c.cargo_cod, c.nombre, c.banda_salarial_min, c.banda_salarial_max,
                nj.nombre AS nivel, nj.nivel_cod,
                COUNT(hc.historial_id) AS empleados_activos
            FROM Cargo c
            JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
            LEFT JOIN Historial_Cargo hc ON hc.cargo_cod = c.cargo_cod AND hc.fecha_hasta IS NULL
            WHERE c.activo = 1
            GROUP BY c.cargo_cod, c.nombre, c.banda_salarial_min, c.banda_salarial_max, nj.nombre, nj.nivel_cod
            ORDER BY nj.nivel_cod, c.nombre
        ")->fetchAll();
    }

    public function obtenerPorId(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT c.cargo_cod, c.nombre, c.banda_salarial_min, c.banda_salarial_max, c.nivel_cod,
                   nj.nombre AS nivel
            FROM Cargo c
            JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
            WHERE c.cargo_cod = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerNiveles(): array {
        return $this->pdo->query("SELECT nivel_cod, nombre FROM Nivel_Jerarquico ORDER BY nivel_cod")->fetchAll();
    }

    public function crear(array $datos): void {
        $this->pdo->prepare("
            INSERT INTO Cargo (nombre, banda_salarial_min, banda_salarial_max, nivel_cod)
            VALUES (:nombre, :banda_min, :banda_max, :nivel_cod)
        ")->execute($datos);
    }

    public function actualizar(array $datos): void {
        $this->pdo->prepare("
            UPDATE Cargo SET nombre=:nombre, banda_salarial_min=:banda_min,
                banda_salarial_max=:banda_max, nivel_cod=:nivel_cod
            WHERE cargo_cod=:cargo_cod
        ")->execute($datos);
    }

    public function tieneEmpleados(int $id): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM Historial_Cargo hc
            JOIN Empleado e ON e.legajo = hc.legajo
            WHERE hc.cargo_cod = ? AND hc.fecha_hasta IS NULL AND e.activo = 1
        ");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function eliminar(int $id): void {
        $this->pdo->prepare("UPDATE Cargo SET activo = 0 WHERE cargo_cod = ?")->execute([$id]);
    }
}