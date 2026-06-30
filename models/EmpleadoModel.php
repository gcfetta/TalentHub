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
                   CONCAT(s.nombre, ' ', s.apellido) AS supervisor
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
             FROM Empleado ORDER BY apellido"
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

    public function legajoExiste(int $legajo): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Empleado WHERE legajo = ?");
        $stmt->execute([$legajo]);
        return (bool) $stmt->fetchColumn();
    }
}