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
            SELECT v.nro_solicitud, v.fecha_solicitud, v.fecha_inicio,
                v.fecha_fin, v.dias_solicitados,
                v.empleado, v.tipo_licencia, v.remunerada,
                v.estado_actual AS estado, v.ultima_actualizacion, v.ultimo_supervisor,
                sl.legajo
            FROM v_solicitudes_estado_actual v
            JOIN Solicitud_Licencia sl ON sl.nro_solicitud = v.nro_solicitud
            $where
            ORDER BY v.nro_solicitud DESC
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

    // Crear solicitud — invoca el procedimiento almacenado (valida días disponibles
    // y registra el primer estado 'Pendiente' de forma atómica en la BD)
    public function crear(array $datos, int $supervisor_legajo): int {
        $stmt = $this->pdo->prepare("
            CALL registrar_solicitud_licencia(:legajo, :tipo_lic_cod, :fecha_inicio, :fecha_fin, :dias, :supervisor, @nro_solicitud)
        ");
        $stmt->execute([
            ':legajo'        => $datos['legajo'],
            ':tipo_lic_cod'  => $datos['tipo_lic_cod'],
            ':fecha_inicio'  => $datos['fecha_inicio'],
            ':fecha_fin'     => $datos['fecha_fin'],
            ':dias'          => $datos['dias_solicitados'],
            ':supervisor'    => $supervisor_legajo,
        ]);
        $stmt->closeCursor(); // libera el resultado del CALL antes de leer el OUT

        $nro = (int)$this->pdo->query("SELECT @nro_solicitud")->fetchColumn();
        return $nro;
    }

    // Cambiar estado (Aprobar / Rechazar) — invoca el procedimiento almacenado
    public function cambiarEstado(int $nro, string $estado_anterior, string $estado_nuevo, string $observacion, int $supervisor_legajo): void {
        $stmt = $this->pdo->prepare("
            CALL cambiar_estado_solicitud(:nro, :anterior, :nuevo, :obs, :supervisor)
        ");
        $stmt->execute([
            ':nro'        => $nro,
            ':anterior'   => $estado_anterior,
            ':nuevo'      => $estado_nuevo,
            ':obs'        => $observacion,
            ':supervisor' => $supervisor_legajo,
        ]);
        $stmt->closeCursor();
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

    // Empleados a cargo de un supervisor (incluye al propio supervisor)
    public function obtenerEmpleadosVisibles(int $legajo_sesion, string $rol): array {
        if (in_array($rol, ['Administrador', 'RRHH'])) {
            return $this->obtenerEmpleados();
        }
        if ($rol === 'Supervisor') {
            $stmt = $this->pdo->prepare("
                SELECT legajo, CONCAT(apellido, ', ', nombre) AS nombre_completo
                FROM Empleado
                WHERE supervisor_legajo = :legajo OR legajo = :legajo
                ORDER BY apellido
            ");
            $stmt->execute([':legajo' => $legajo_sesion]);
            return $stmt->fetchAll();
        }
        // Empleado raso: solo él mismo
        $stmt = $this->pdo->prepare("
            SELECT legajo, CONCAT(apellido, ', ', nombre) AS nombre_completo
            FROM Empleado WHERE legajo = :legajo
        ");
        $stmt->execute([':legajo' => $legajo_sesion]);
        return $stmt->fetchAll();
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