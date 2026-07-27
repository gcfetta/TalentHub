<?php
// models/EmpleadoModel.php

class EmpleadoModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Agregar dentro de EmpleadoModel, después del constructor
    public function getPdo(): PDO {
        return $this->pdo;
    }

    // Todos los empleados con su cargo actual y departamento
    public function obtenerTodos(): array {
        return $this->pdo->query("
            SELECT legajo, nombre_completo, mail, fecha_ingreso,
                departamento, cargo_actual, nivel_jerarquico,
                localidad, anios_antiguedad, promedio_evaluaciones, supervisor
            FROM v_empleados_resumen
            ORDER BY nombre_completo
        ")->fetchAll();
    }

    // Un empleado con todo su detalle
    public function obtenerPorLegajo(int $legajo): array|false {
        $sql = "
            SELECT e.legajo, e.nombre, e.apellido, e.mail, e.telefono,
                   e.fecha_ingreso, e.depto_cod, e.localidad_cod, e.supervisor_legajo,
                   d.nombre AS departamento,
                   l.nombre AS localidad,
                   CONCAT(s.nombre, ' ', s.apellido) AS supervisor,
                   calcular_antiguedad(e.fecha_ingreso) AS anios_antiguedad,
                   promedio_puntaje_empleado(e.legajo) AS promedio_evaluaciones
            FROM Empleado e
            JOIN Departamento d ON d.depto_cod = e.depto_cod
            JOIN Localidad l    ON l.localidad_cod = e.localidad_cod
            LEFT JOIN Empleado s ON s.legajo = e.supervisor_legajo
            WHERE e.legajo = :legajo
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetch();
    }

    // Historial completo de cargos de un empleado
    public function obtenerHistorialCargos(int $legajo): array {
        $sql = "
            SELECT hc.fecha_desde, hc.fecha_hasta,
                   c.nombre AS cargo, nj.nombre AS nivel,
                   c.banda_salarial_min, c.banda_salarial_max
            FROM Historial_Cargo hc
            JOIN Cargo c            ON c.cargo_cod = hc.cargo_cod
            JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
            WHERE hc.legajo = :legajo
            ORDER BY hc.fecha_desde DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetchAll();
    }

    // Evaluaciones de un empleado
    public function obtenerEvaluaciones(int $legajo): array {
        $sql = "
            SELECT ev.periodo, ev.puntaje, ev.observaciones, ev.fecha_evaluacion,
                   CONCAT(e.nombre, ' ', e.apellido) AS evaluador
            FROM Evaluacion_Desempeno ev
            JOIN Empleado e ON e.legajo = ev.evaluador_legajo
            WHERE ev.legajo = :legajo
            ORDER BY ev.fecha_evaluacion DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        return $stmt->fetchAll();
    }

    // Para el select de supervisores en el formulario
    public function obtenerParaSelect(): array {
        return $this->pdo->query(
            "SELECT legajo, CONCAT(apellido, ', ', nombre) AS nombre_completo
            FROM Empleado WHERE activo = 1 ORDER BY apellido"
        )->fetchAll();
    }

    public function crear(array $datos): void {
        $sql = "INSERT INTO Empleado
                    (legajo, nombre, apellido, mail, fecha_ingreso, telefono,
                     localidad_cod, depto_cod, supervisor_legajo)
                VALUES
                    (:legajo, :nombre, :apellido, :mail, :fecha_ingreso, :telefono,
                     :localidad_cod, :depto_cod, :supervisor_legajo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);
    }

    public function actualizar(array $datos): void {
        $sql = "UPDATE Empleado SET
                    nombre            = :nombre,
                    apellido          = :apellido,
                    mail              = :mail,
                    fecha_ingreso     = :fecha_ingreso,
                    telefono          = :telefono,
                    localidad_cod     = :localidad_cod,
                    depto_cod         = :depto_cod,
                    supervisor_legajo = :supervisor_legajo
                WHERE legajo = :legajo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);
    }

    public function eliminar(int $legajo): void {
        $this->pdo->prepare("UPDATE Empleado SET activo = 0 WHERE legajo = ?")->execute([$legajo]);
    }

    // Auxiliares para poblar selects del formulario
    public function obtenerDepartamentos(): array {
        return $this->pdo->query("SELECT depto_cod, nombre FROM Departamento ORDER BY nombre")->fetchAll();
    }

    public function obtenerLocalidades(): array {
        return $this->pdo->query("SELECT localidad_cod, nombre, provincia FROM Localidad ORDER BY nombre")->fetchAll();
    }

    public function obtenerCargos(): array {
        return $this->pdo->query(
            "SELECT c.cargo_cod, c.nombre, nj.nombre AS nivel
             FROM Cargo c JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
             ORDER BY nj.nivel_cod, c.nombre"
        )->fetchAll();
    }

    // Cargo actual activo de un empleado (o null si no tiene)
    public function obtenerCargoActual(int $legajo): ?int {
        $sql = "SELECT cargo_cod FROM Historial_Cargo
                WHERE legajo = :legajo AND fecha_hasta IS NULL
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        $cod = $stmt->fetchColumn();
        return $cod !== false ? (int)$cod : null;
    }

    // Devuelve el empleado activo que ya ocupa ese cargo, o false si está libre
    public function cargoOcupado(int $cargo_cod, ?int $excluir_legajo = null): array|false {
        $sql = "SELECT e.legajo, CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo
                FROM Historial_Cargo hc
                JOIN Empleado e ON e.legajo = hc.legajo
                WHERE hc.cargo_cod = :cargo_cod
                AND hc.fecha_hasta IS NULL
                AND e.activo = 1";
        if ($excluir_legajo !== null) {
            $sql .= " AND e.legajo != :excluir_legajo";
        }
        $sql .= " LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $params = [':cargo_cod' => $cargo_cod];
        if ($excluir_legajo !== null) {
            $params[':excluir_legajo'] = $excluir_legajo;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function legajoExiste(int $legajo): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Empleado WHERE legajo = ?");
        $stmt->execute([$legajo]);
        return (bool) $stmt->fetchColumn();
    }

    // Próximo legajo disponible (correlativo simple)
    public function obtenerProximoLegajo(): int {
        $max = $this->pdo->query("SELECT MAX(legajo) FROM Empleado")->fetchColumn();
        return $max ? ((int)$max + 1) : 1001;
    }

    // Cantidad de empleados activos por cargo (para mostrar en el selector)
    public function contarOcupantesPorCargo(): array {
        $sql = "SELECT hc.cargo_cod, COUNT(*) AS cantidad
                FROM Historial_Cargo hc
                JOIN Empleado e ON e.legajo = hc.legajo
                WHERE hc.fecha_hasta IS NULL AND e.activo = 1
                GROUP BY hc.cargo_cod";
        $filas = $this->pdo->query($sql)->fetchAll();
        $conteo = [];
        foreach ($filas as $f) {
            $conteo[(int)$f['cargo_cod']] = (int)$f['cantidad'];
        }
        return $conteo;
    }

    // Indica si un cargo es de nivel gerencial (único por definición)
    public function esCargoGerencial(int $cargo_cod): bool {
        $stmt = $this->pdo->prepare(
            "SELECT nj.nivel_cod FROM Cargo c
            JOIN Nivel_Jerarquico nj ON nj.nivel_cod = c.nivel_cod
            WHERE c.cargo_cod = ?"
        );
        $stmt->execute([$cargo_cod]);
        return (int)$stmt->fetchColumn() === 1;
    }

    // Alta de historial al crear un empleado con cargo inicial
    public function registrarCargoInicial(int $legajo, int $cargo_cod, string $fecha_desde): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde) VALUES (?, ?, ?)"
        );
        $stmt->execute([$legajo, $cargo_cod, $fecha_desde]);
    }

    // Cierra el cargo vigente (fecha_hasta) y abre el nuevo, en una transacción
    public function cambiarCargo(int $legajo, ?int $cargo_previo, ?int $cargo_nuevo, string $fecha): void {
        $this->pdo->beginTransaction();
        try {
            if ($cargo_previo !== null) {
                $this->pdo->prepare(
                    "UPDATE Historial_Cargo SET fecha_hasta = ?
                     WHERE legajo = ? AND cargo_cod = ? AND fecha_hasta IS NULL"
                )->execute([$fecha, $legajo, $cargo_previo]);
            }
            if ($cargo_nuevo !== null) {
                $this->pdo->prepare(
                    "INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde) VALUES (?, ?, ?)"
                )->execute([$legajo, $cargo_nuevo, $fecha]);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Reporte de sueldos para exportar (usa la función calcular_antiguedad)
    public function obtenerReporteSueldos(): array {
        $stmt = $this->pdo->query(
            "SELECT e.legajo,
                    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                    c.nombre AS cargo,
                    c.banda_salarial_min,
                    c.banda_salarial_max,
                    calcular_antiguedad(e.fecha_ingreso) AS anios_antiguedad
             FROM Empleado e
             LEFT JOIN Historial_Cargo hc ON hc.legajo = e.legajo AND hc.fecha_hasta IS NULL
             LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod
             WHERE e.activo = 1"
        );
        return $stmt->fetchAll();
    }
}