<?php
// models/SolicitudLicenciaModel.php

class SolicitudLicenciaModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Todas las solicitudes con su estado actual (último registro de auditoría)
    public function obtenerTodas(?int $legajo_filtro = null): array {
        $where = $legajo_filtro ? "WHERE sl.legajo = :legajo" : "";
        $sql = "
            SELECT sl.nro_solicitud, sl.fecha_solicitud, sl.fecha_inicio,
                   sl.fecha_fin, sl.dias_solicitados,
                   e.legajo, e.nombre, e.apellido,
                   tl.nombre AS tipo_licencia,
                   a.estado_nuevo AS estado
            FROM Solicitud_Licencia sl
            JOIN Empleado e       ON e.legajo        = sl.legajo
            JOIN Tipo_Licencia tl ON tl.tipo_lic_cod = sl.tipo_lic_cod
            JOIN Auditoria_Estado_Solicitud a
              ON a.nro_solicitud = sl.nro_solicitud
             AND a.auditoria_id  = (
                 SELECT MAX(auditoria_id)
                 FROM Auditoria_Estado_Solicitud
                 WHERE nro_solicitud = sl.nro_solicitud
             )
            $where
            ORDER BY sl.nro_solicitud DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        if ($legajo_filtro) $stmt->bindValue(':legajo', $legajo_filtro, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Una solicitud con todos sus datos
    public function obtenerPorId(int $nro): array|false {
        $sql = "
            SELECT sl.nro_solicitud, sl.fecha_solicitud, sl.fecha_inicio,
                   sl.fecha_fin, sl.dias_solicitados,
                   sl.legajo, sl.tipo_lic_cod,
                   e.nombre, e.apellido,
                   tl.nombre AS tipo_licencia,
                   tl.dias_max, tl.requiere_certificado, tl.remunerada,
                   a.estado_nuevo AS estado
            FROM Solicitud_Licencia sl
            JOIN Empleado e       ON e.legajo        = sl.legajo
            JOIN Tipo_Licencia tl ON tl.tipo_lic_cod = sl.tipo_lic_cod
            JOIN Auditoria_Estado_Solicitud a
              ON a.nro_solicitud = sl.nro_solicitud
             AND a.auditoria_id  = (
                 SELECT MAX(auditoria_id)
                 FROM Auditoria_Estado_Solicitud
                 WHERE nro_solicitud = sl.nro_solicitud
             )
            WHERE sl.nro_solicitud = :nro
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nro' => $nro]);
        return $stmt->fetch();
    }

    // Historial completo de estados de una solicitud
    public function obtenerAuditoria(int $nro): array {
        $sql = "
            SELECT a.auditoria_id, a.estado_anterior, a.estado_nuevo,
                   a.observacion, a.fecha_cambio,
                   CONCAT(e.nombre, ' ', e.apellido) AS supervisor
            FROM Auditoria_Estado_Solicitud a
            JOIN Empleado e ON e.legajo = a.supervisor_legajo
            WHERE a.nro_solicitud = :nro
            ORDER BY a.auditoria_id ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nro' => $nro]);
        return $stmt->fetchAll();
    }

    // Documentos adjuntos
    public function obtenerDocumentos(int $nro): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM Documento_Licencia WHERE nro_solicitud = ? ORDER BY fecha_carga"
        );
        $stmt->execute([$nro]);
        return $stmt->fetchAll();
    }

    // Crear solicitud + primer registro de auditoría (todo en transacción)
    public function crear(array $datos, int $supervisor_legajo): int {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Solicitud_Licencia
                    (fecha_solicitud, fecha_inicio, fecha_fin, dias_solicitados, legajo, tipo_lic_cod)
                VALUES
                    (:fecha_solicitud, :fecha_inicio, :fecha_fin, :dias_solicitados, :legajo, :tipo_lic_cod)
            ");
            $stmt->execute($datos);
            $nro = (int)$this->pdo->lastInsertId();

            // Primer estado: Pendiente
            $this->registrarEstado($nro, null, 'Pendiente', 'Solicitud registrada.', $supervisor_legajo);

            $this->pdo->commit();
            return $nro;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Cambiar estado (Aprobar / Rechazar) — con auditoría ATÓMICA
    public function cambiarEstado(int $nro, string $estado_anterior, string $estado_nuevo, string $observacion, int $supervisor_legajo): void {
        $this->pdo->beginTransaction();
        try {
            $this->registrarEstado($nro, $estado_anterior, $estado_nuevo, $observacion, $supervisor_legajo);
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function registrarEstado(int $nro, ?string $anterior, string $nuevo, string $obs, int $supervisor): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO Auditoria_Estado_Solicitud
                (nro_solicitud, estado_anterior, estado_nuevo, observacion, supervisor_legajo)
            VALUES
                (:nro, :anterior, :nuevo, :obs, :supervisor)
        ");
        $stmt->execute([
            ':nro'        => $nro,
            ':anterior'   => $anterior,
            ':nuevo'      => $nuevo,
            ':obs'        => $obs,
            ':supervisor' => $supervisor,
        ]);
    }

    // Para poblar selects
    public function obtenerTiposLicencia(): array {
        return $this->pdo->query(
            "SELECT tipo_lic_cod, nombre, dias_max, requiere_certificado, remunerada
             FROM Tipo_Licencia ORDER BY nombre"
        )->fetchAll();
    }

    public function obtenerEmpleados(): array {
        return $this->pdo->query(
            "SELECT legajo, CONCAT(apellido, ', ', nombre) AS nombre_completo
             FROM Empleado ORDER BY apellido"
        )->fetchAll();
    }

    // Días ya usados en el año para un tipo de licencia
    public function diasUsadosEnAnio(int $legajo, int $tipo_lic_cod, int $anio): int {
        $sql = "
            SELECT COALESCE(SUM(sl.dias_solicitados), 0)
            FROM Solicitud_Licencia sl
            JOIN Auditoria_Estado_Solicitud a
              ON a.nro_solicitud = sl.nro_solicitud
             AND a.auditoria_id  = (
                 SELECT MAX(auditoria_id)
                 FROM Auditoria_Estado_Solicitud
                 WHERE nro_solicitud = sl.nro_solicitud
             )
            WHERE sl.legajo       = :legajo
              AND sl.tipo_lic_cod = :tipo
              AND YEAR(sl.fecha_inicio) = :anio
              AND a.estado_nuevo  != 'Rechazada'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo, ':tipo' => $tipo_lic_cod, ':anio' => $anio]);
        return (int)$stmt->fetchColumn();
    }
}