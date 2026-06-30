<?php
// models/DashboardModel.php

class DashboardModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function totalEmpleados(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM Empleado")->fetchColumn();
    }

    public function solicitudesPendientes(): int {
        return (int)$this->pdo->query("
            SELECT COUNT(DISTINCT sl.nro_solicitud)
            FROM Solicitud_Licencia sl
            JOIN Auditoria_Estado_Solicitud a
              ON a.nro_solicitud = sl.nro_solicitud
            WHERE a.estado_nuevo = 'Pendiente'
              AND a.auditoria_id = (
                  SELECT MAX(auditoria_id) FROM Auditoria_Estado_Solicitud
                  WHERE nro_solicitud = sl.nro_solicitud
              )
        ")->fetchColumn();
    }

    public function totalEvaluaciones(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM Evaluacion_Desempeno")->fetchColumn();
    }

    public function promedioPuntaje(): ?float {
        $r = $this->pdo->query("SELECT ROUND(AVG(puntaje),2) FROM Evaluacion_Desempeno")->fetchColumn();
        return $r !== null ? (float)$r : null;
    }

    // Últimas 5 solicitudes (reutiliza la vista v_solicitudes_estado_actual)
    public function ultimasSolicitudes(int $limite = 5): array {
        $sql = "
            SELECT nro_solicitud, empleado, tipo_licencia,
                   fecha_inicio, dias_solicitados, estado_actual AS estado
            FROM v_solicitudes_estado_actual
            ORDER BY nro_solicitud DESC
            LIMIT :lim
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Últimas 5 evaluaciones
    public function ultimasEvaluaciones(int $limite = 5): array {
        $sql = "
            SELECT e.nombre, e.apellido, ev.periodo, ev.puntaje,
                   ev.fecha_evaluacion,
                   CONCAT(ev2.nombre,' ',ev2.apellido) AS evaluador
            FROM Evaluacion_Desempeno ev
            JOIN Empleado e   ON e.legajo  = ev.legajo
            JOIN Empleado ev2 ON ev2.legajo = ev.evaluador_legajo
            ORDER BY ev.fecha_evaluacion DESC
            LIMIT :lim
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}