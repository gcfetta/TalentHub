<?php
// models/EvaluacionModel.php

class EvaluacionModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Listado completo con datos del evaluado y evaluador
    public function obtenerTodas(?int $legajo_filtro = null): array {
        $where = $legajo_filtro ? "WHERE ed.legajo = :legajo" : "";
        $sql = "
            SELECT ed.evaluacion_id, ed.periodo, ed.puntaje,
                   ed.fecha_evaluacion, ed.observaciones,
                   e.legajo, e.nombre, e.apellido,
                   ev.nombre AS eval_nombre, ev.apellido AS eval_apellido,
                   c.nombre AS cargo
            FROM Evaluacion_Desempeno ed
            JOIN Empleado e  ON e.legajo  = ed.legajo
            JOIN Empleado ev ON ev.legajo = ed.evaluador_legajo
            LEFT JOIN Historial_Cargo hc
              ON hc.legajo   = ed.legajo
             AND hc.fecha_hasta IS NULL
            LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod
            $where
            ORDER BY ed.fecha_evaluacion DESC, ed.evaluacion_id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        if ($legajo_filtro) $stmt->bindValue(':legajo', $legajo_filtro, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Una evaluación con todos sus datos
    public function obtenerPorId(int $id): array|false {
        $sql = "
            SELECT ed.evaluacion_id, ed.periodo, ed.puntaje,
                   ed.fecha_evaluacion, ed.observaciones,
                   ed.legajo, ed.evaluador_legajo,
                   e.nombre, e.apellido,
                   ev.nombre AS eval_nombre, ev.apellido AS eval_apellido,
                   c.nombre AS cargo, nj.nombre AS nivel
            FROM Evaluacion_Desempeno ed
            JOIN Empleado e  ON e.legajo  = ed.legajo
            JOIN Empleado ev ON ev.legajo = ed.evaluador_legajo
            LEFT JOIN Historial_Cargo hc
              ON hc.legajo   = ed.legajo
             AND hc.fecha_hasta IS NULL
            LEFT JOIN Cargo c  ON c.cargo_cod  = hc.cargo_cod
            LEFT JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
            WHERE ed.evaluacion_id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Historial de evaluaciones de un empleado (para detalle)
    public function obtenerHistorialEmpleado(int $legajo): array {
        $sql = "
            SELECT ed.evaluacion_id, ed.periodo, ed.puntaje,
                   ed.fecha_evaluacion, ed.observaciones,
                   ev.nombre AS eval_nombre, ev.apellido AS eval_apellido
            FROM Evaluacion_Desempeno ed
            JOIN Empleado ev ON ev.legajo = ed.evaluador_legajo
            WHERE ed.legajo = :legajo
            ORDER BY ed.fecha_evaluacion DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetchAll();
    }

    // Crear una evaluación nueva
    public function crear(array $datos): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Evaluacion_Desempeno
                (legajo, periodo, puntaje, observaciones, evaluador_legajo, fecha_evaluacion)
            VALUES
                (:legajo, :periodo, :puntaje, :observaciones, :evaluador_legajo, :fecha_evaluacion)
        ");
        $stmt->execute($datos);
        return (int)$this->pdo->lastInsertId();
    }

    // Verificar si ya existe evaluación para ese empleado y período
    public function existePeriodo(int $legajo, string $periodo, ?int $excluir_id = null): bool {
        $sql = "SELECT COUNT(*) FROM Evaluacion_Desempeno
                WHERE legajo = :legajo AND periodo = :periodo";
        if ($excluir_id) $sql .= " AND evaluacion_id != :excluir";
        $stmt = $this->pdo->prepare($sql);
        $params = [':legajo' => $legajo, ':periodo' => $periodo];
        if ($excluir_id) $params[':excluir'] = $excluir_id;
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Para poblar select de empleados
    public function obtenerEmpleados(?int $supervisor_legajo = null): array {
        $sql = "SELECT legajo, CONCAT(apellido, ', ', nombre) AS nombre_completo
                FROM Empleado
                WHERE activo = 1";
        if ($supervisor_legajo !== null) {
            $sql .= " AND supervisor_legajo = :supervisor";
        }
        $sql .= " ORDER BY apellido";

        $stmt = $this->pdo->prepare($sql);
        if ($supervisor_legajo !== null) {
            $stmt->bindValue(':supervisor', $supervisor_legajo, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Promedio de puntajes de un empleado
    public function promedioEmpleado(int $legajo): ?float {
        $stmt = $this->pdo->prepare(
            "SELECT AVG(puntaje) FROM Evaluacion_Desempeno WHERE legajo = :legajo"
        );
        $stmt->execute([':legajo' => $legajo]);
        $r = $stmt->fetchColumn();
        return $r !== false ? round((float)$r, 2) : null;
    }

    // Ranking de empleados según evaluaciones (usa la vista)
    public function obtenerRanking(): array {
        return $this->pdo->query("
            SELECT legajo, nombre_completo, departamento,
                   total_evaluaciones, promedio_puntaje,
                   puntaje_maximo, puntaje_minimo
            FROM v_ranking_evaluaciones
            ORDER BY promedio_puntaje DESC
        ")->fetchAll();
    }

    // ¿Esta persona tiene al menos un subordinado a cargo?
    public function tieneSubordinados(int $legajo): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM Empleado WHERE supervisor_legajo = :legajo"
        );
        $stmt->execute([':legajo' => $legajo]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Evaluaciones de uno mismo + de los subordinados directos
    public function obtenerPropiasYDeSubordinados(int $legajo): array {
        $sql = "
            SELECT ed.evaluacion_id, ed.periodo, ed.puntaje,
                   ed.fecha_evaluacion, ed.observaciones,
                   e.legajo, e.nombre, e.apellido,
                   ev.nombre AS eval_nombre, ev.apellido AS eval_apellido,
                   c.nombre AS cargo
            FROM Evaluacion_Desempeno ed
            JOIN Empleado e  ON e.legajo  = ed.legajo
            JOIN Empleado ev ON ev.legajo = ed.evaluador_legajo
            LEFT JOIN Historial_Cargo hc
              ON hc.legajo   = ed.legajo
             AND hc.fecha_hasta IS NULL
            LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod
            WHERE ed.legajo = :legajo OR e.supervisor_legajo = :legajo
            ORDER BY ed.fecha_evaluacion DESC, ed.evaluacion_id DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetchAll();
    }
}