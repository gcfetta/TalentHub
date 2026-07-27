-- ============================================================
--  TalentHub – Sistema de Recursos Humanos
--  Script definitivo: tablas + programabilidad + datos de prueba
--  Versión con bajas lógicas (campo activo) en Empleado,
--  Cargo y Departamento.
-- ============================================================

CREATE DATABASE IF NOT EXISTS talenthub_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE talenthub_db;

-- ============================================================
--  BLOQUE 1 – Tablas maestras
-- ============================================================

CREATE TABLE Localidad (
    localidad_cod INT          PRIMARY KEY AUTO_INCREMENT,
    nombre        VARCHAR(100) NOT NULL,
    provincia     VARCHAR(100) NOT NULL
);

CREATE TABLE Nivel_Jerarquico (
    nivel_cod   INT          PRIMARY KEY AUTO_INCREMENT,
    nombre      VARCHAR(50)  NOT NULL,
    descripcion VARCHAR(255)
);

CREATE TABLE Tipo_Licencia (
    tipo_lic_cod         INT          PRIMARY KEY AUTO_INCREMENT,
    nombre               VARCHAR(100) NOT NULL,
    dias_max             INT          NOT NULL,
    requiere_certificado TINYINT(1)   NOT NULL DEFAULT 0,
    remunerada           TINYINT(1)   NOT NULL DEFAULT 1
);

-- ============================================================
--  BLOQUE 2 – Cargo y Departamento (con baja lógica)
-- ============================================================

CREATE TABLE Cargo (
    cargo_cod          INT           PRIMARY KEY AUTO_INCREMENT,
    nombre             VARCHAR(100)  NOT NULL,
    banda_salarial_min DECIMAL(12,2) NOT NULL,
    banda_salarial_max DECIMAL(12,2) NOT NULL,
    nivel_cod          INT           NOT NULL,
    activo             TINYINT(1)    NOT NULL DEFAULT 1,
    CONSTRAINT fk_cargo_nivel
        FOREIGN KEY (nivel_cod) REFERENCES Nivel_Jerarquico(nivel_cod)
);

CREATE TABLE Departamento (
    depto_cod     INT          PRIMARY KEY AUTO_INCREMENT,
    nombre        VARCHAR(100) NOT NULL,
    localidad_cod INT          NOT NULL,
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_depto_localidad
        FOREIGN KEY (localidad_cod) REFERENCES Localidad(localidad_cod)
);

-- ============================================================
--  BLOQUE 3 – Empleado (con baja lógica)
-- ============================================================

CREATE TABLE Empleado (
    legajo            INT          PRIMARY KEY,
    nombre            VARCHAR(100) NOT NULL,
    apellido          VARCHAR(100) NOT NULL,
    mail              VARCHAR(150) NOT NULL UNIQUE,
    fecha_ingreso     DATE         NOT NULL,
    telefono          VARCHAR(20),
    localidad_cod     INT          NOT NULL,
    depto_cod         INT          NOT NULL,
    supervisor_legajo INT,
    activo            TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_empleado_localidad
        FOREIGN KEY (localidad_cod)     REFERENCES Localidad(localidad_cod),
    CONSTRAINT fk_empleado_depto
        FOREIGN KEY (depto_cod)         REFERENCES Departamento(depto_cod),
    CONSTRAINT fk_empleado_supervisor
        FOREIGN KEY (supervisor_legajo) REFERENCES Empleado(legajo)
);

-- ============================================================
--  BLOQUE 4 – Historial, Auditoría Salarial y Evaluaciones
--  Auditoria_Salario va aquí para que el trigger la referencie
-- ============================================================

CREATE TABLE Historial_Cargo (
    historial_id INT  PRIMARY KEY AUTO_INCREMENT,
    legajo       INT  NOT NULL,
    cargo_cod    INT  NOT NULL,
    fecha_desde  DATE NOT NULL,
    fecha_hasta  DATE,
    CONSTRAINT fk_historial_empleado
        FOREIGN KEY (legajo)    REFERENCES Empleado(legajo),
    CONSTRAINT fk_historial_cargo
        FOREIGN KEY (cargo_cod) REFERENCES Cargo(cargo_cod)
);

CREATE TABLE Auditoria_Salario (
    auditoria_sal_id   INT      PRIMARY KEY AUTO_INCREMENT,
    legajo             INT      NOT NULL,
    historial_id       INT      NOT NULL,
    cargo_anterior_cod INT      NOT NULL,
    cargo_nuevo_cod    INT      NOT NULL,
    fecha_cambio       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audsal_empleado  FOREIGN KEY (legajo)             REFERENCES Empleado(legajo),
    CONSTRAINT fk_audsal_historial FOREIGN KEY (historial_id)       REFERENCES Historial_Cargo(historial_id),
    CONSTRAINT fk_audsal_cargo_ant FOREIGN KEY (cargo_anterior_cod) REFERENCES Cargo(cargo_cod),
    CONSTRAINT fk_audsal_cargo_nvo FOREIGN KEY (cargo_nuevo_cod)    REFERENCES Cargo(cargo_cod)
);

CREATE TABLE Evaluacion_Desempeno (
    evaluacion_id    INT           PRIMARY KEY AUTO_INCREMENT,
    legajo           INT           NOT NULL,
    periodo          VARCHAR(20)   NOT NULL,
    puntaje          DECIMAL(5,2),
    observaciones    TEXT,
    evaluador_legajo INT           NOT NULL,
    fecha_evaluacion DATE          NOT NULL,
    CONSTRAINT fk_evaluacion_empleado
        FOREIGN KEY (legajo)           REFERENCES Empleado(legajo),
    CONSTRAINT fk_evaluacion_evaluador
        FOREIGN KEY (evaluador_legajo) REFERENCES Empleado(legajo)
);

-- ============================================================
--  BLOQUE 5 – Licencias
-- ============================================================

CREATE TABLE Solicitud_Licencia (
    nro_solicitud    INT  PRIMARY KEY AUTO_INCREMENT,
    fecha_solicitud  DATE NOT NULL,
    fecha_inicio     DATE NOT NULL,
    fecha_fin        DATE NOT NULL,
    dias_solicitados INT  NOT NULL,
    legajo           INT  NOT NULL,
    tipo_lic_cod     INT  NOT NULL,
    CONSTRAINT fk_solicitud_empleado
        FOREIGN KEY (legajo)       REFERENCES Empleado(legajo),
    CONSTRAINT fk_solicitud_tipo
        FOREIGN KEY (tipo_lic_cod) REFERENCES Tipo_Licencia(tipo_lic_cod)
);

CREATE TABLE Auditoria_Estado_Solicitud (
    auditoria_id      INT          PRIMARY KEY AUTO_INCREMENT,
    nro_solicitud     INT          NOT NULL,
    estado_anterior   VARCHAR(50),
    estado_nuevo      VARCHAR(50)  NOT NULL,
    observacion       VARCHAR(500),
    supervisor_legajo INT          NOT NULL,
    fecha_cambio      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_solicitud
        FOREIGN KEY (nro_solicitud)     REFERENCES Solicitud_Licencia(nro_solicitud),
    CONSTRAINT fk_auditoria_supervisor
        FOREIGN KEY (supervisor_legajo) REFERENCES Empleado(legajo)
);

CREATE TABLE Documento_Licencia (
    doc_id         INT          PRIMARY KEY AUTO_INCREMENT,
    nro_solicitud  INT          NOT NULL,
    tipo_documento VARCHAR(100) NOT NULL,
    archivo_path   VARCHAR(500) NOT NULL,
    fecha_carga    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documento_solicitud
        FOREIGN KEY (nro_solicitud) REFERENCES Solicitud_Licencia(nro_solicitud)
);

-- ============================================================
--  BLOQUE 6 – Usuarios
-- ============================================================

CREATE TABLE Usuario (
    usuario_id    INT          PRIMARY KEY AUTO_INCREMENT,
    legajo        INT          NOT NULL UNIQUE,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol           ENUM('Administrador','RRHH','Supervisor','Empleado') NOT NULL DEFAULT 'Empleado',
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    -- En 1 cuando la cuenta se activó con password = legajo (autogenerada);
    -- pasa a 0 cuando el empleado la cambia por una propia.
    debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_usuario_empleado
        FOREIGN KEY (legajo) REFERENCES Empleado(legajo)
);

-- ============================================================
--  BLOQUE 6.1 – Índices explícitos
--  Contexto PDF: arquitectura basada en B-Tree explícitos para
--  acelerar los JOIN/filtros que usan las vistas y procedimientos.
--  Nota: MySQL/InnoDB ya indexa PK y columnas FK automáticamente;
--  estos son adicionales, sobre columnas de filtro que NO son FK.
-- ============================================================

-- v_empleados_resumen filtra por Historial_Cargo.fecha_hasta IS NULL
-- (cargo vigente) para cada legajo. Sin índice, es un scan completo.
CREATE INDEX idx_historial_legajo_vigente
    ON Historial_Cargo (legajo, fecha_hasta);

-- v_solicitudes_estado_actual y cambiar_estado_solicitud hacen
-- MAX(auditoria_id) filtrando por nro_solicitud repetidamente.
CREATE INDEX idx_auditoria_solicitud
    ON Auditoria_Estado_Solicitud (nro_solicitud, auditoria_id);

-- registrar_solicitud_licencia filtra por legajo + tipo_lic_cod +
-- YEAR(fecha_inicio) para calcular días usados en el año.
CREATE INDEX idx_solicitud_legajo_tipo_fecha
    ON Solicitud_Licencia (legajo, tipo_lic_cod, fecha_inicio);

-- Filtro muy frecuente en listados: empleados activos por depto.
CREATE INDEX idx_empleado_activo_depto
    ON Empleado (activo, depto_cod);

-- ============================================================
--  BLOQUE 7 – Funciones almacenadas
--  Contexto PDF: "Caso TalentHub – Cálculo de Antigüedad"
--  Van ANTES de procedimientos y vistas que las invocan.
-- ============================================================

DELIMITER //

-- Devuelve años completos de antigüedad.
-- DETERMINISTIC: mismo input → mismo output.
-- Invocable directamente en SELECT como columna calculada.
CREATE FUNCTION calcular_antiguedad(p_fecha_ingreso DATE)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_fecha_ingreso, CURDATE());
END //

-- Devuelve promedio de puntajes de evaluación de un empleado.
-- COALESCE evita retornar NULL cuando no hay evaluaciones.
CREATE FUNCTION promedio_puntaje_empleado(p_legajo INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_promedio DECIMAL(5,2);
    SELECT AVG(puntaje) INTO v_promedio
    FROM Evaluacion_Desempeno
    WHERE legajo = p_legajo;
    RETURN COALESCE(v_promedio, 0.00);
END //

-- ============================================================
--  BLOQUE 8 – Procedimientos almacenados
--  Contexto PDF: encapsulamiento de lógica de negocio,
--  transacciones todo-o-nada.
-- ============================================================

-- Registra una nueva solicitud validando días disponibles.
-- Parámetro OUT devuelve el nro generado al PHP.
CREATE PROCEDURE registrar_solicitud_licencia(
    IN  p_legajo            INT,
    IN  p_tipo_lic_cod      INT,
    IN  p_fecha_inicio      DATE,
    IN  p_fecha_fin         DATE,
    IN  p_dias              INT,
    IN  p_supervisor_legajo INT,
    OUT p_nro_solicitud     INT
)
BEGIN

    DECLARE v_dias_max       INT;
    DECLARE v_dias_usados    INT;
    DECLARE v_dias_restantes INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SELECT dias_max INTO v_dias_max
    FROM Tipo_Licencia
    WHERE tipo_lic_cod = p_tipo_lic_cod;

    SELECT COALESCE(SUM(sl.dias_solicitados), 0) INTO v_dias_usados
    FROM Solicitud_Licencia sl
    JOIN Auditoria_Estado_Solicitud a
      ON a.nro_solicitud = sl.nro_solicitud
     AND a.auditoria_id  = (
             SELECT MAX(auditoria_id)
             FROM Auditoria_Estado_Solicitud
             WHERE nro_solicitud = sl.nro_solicitud
         )
    WHERE sl.legajo            = p_legajo
      AND sl.tipo_lic_cod      = p_tipo_lic_cod
      AND YEAR(sl.fecha_inicio) = YEAR(p_fecha_inicio)
      AND a.estado_nuevo       != 'Rechazada';

    SET v_dias_restantes = v_dias_max - v_dias_usados;

    IF p_dias > v_dias_restantes THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: días solicitados superan el máximo permitido para este tipo de licencia.';
    END IF;

    START TRANSACTION;

        INSERT INTO Solicitud_Licencia
            (fecha_solicitud, fecha_inicio, fecha_fin, dias_solicitados, legajo, tipo_lic_cod)
        VALUES
            (CURDATE(), p_fecha_inicio, p_fecha_fin, p_dias, p_legajo, p_tipo_lic_cod);

        SET p_nro_solicitud = LAST_INSERT_ID();

        INSERT INTO Auditoria_Estado_Solicitud
            (nro_solicitud, estado_anterior, estado_nuevo, observacion, supervisor_legajo)
        VALUES
            (p_nro_solicitud, NULL, 'Pendiente', 'Solicitud registrada via procedimiento.', p_supervisor_legajo);

    COMMIT;
END //

-- Cambia el estado de una solicitud con auditoría atómica.
-- El PHP invoca CALL en lugar de INSERTs directos.
CREATE PROCEDURE cambiar_estado_solicitud(
    IN p_nro_solicitud     INT,
    IN p_estado_anterior   VARCHAR(50),
    IN p_estado_nuevo      VARCHAR(50),
    IN p_observacion       VARCHAR(500),
    IN p_supervisor_legajo INT
)
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;

        INSERT INTO Auditoria_Estado_Solicitud
            (nro_solicitud, estado_anterior, estado_nuevo, observacion, supervisor_legajo)
        VALUES
            (p_nro_solicitud, p_estado_anterior, p_estado_nuevo, p_observacion, p_supervisor_legajo);

    COMMIT;
END //

-- ============================================================
--  BLOQUE 9 – Triggers
--  Contexto PDF: "Los vigilantes silenciosos de la base de datos"
-- ============================================================

-- BEFORE INSERT en Historial_Cargo: cierra el cargo vigente anterior
-- del empleado antes de que se confirme el nuevo registro.
DELIMITER //
CREATE TRIGGER tr_cerrar_cargo_anterior
BEFORE INSERT ON Historial_Cargo
FOR EACH ROW
BEGIN
    IF @skip_trigger IS NULL OR @skip_trigger = 0 THEN
        UPDATE Historial_Cargo
        SET fecha_hasta = NEW.fecha_desde - INTERVAL 1 DAY
        WHERE legajo = NEW.legajo
          AND fecha_hasta IS NULL;
    END IF;
END //
DELIMITER ;

-- AFTER INSERT en Historial_Cargo: registra en Auditoria_Salario
-- cada cambio de cargo. El modelo de historial cierra la fila
-- anterior (fecha_hasta) e inserta una fila nueva, por eso el
-- trigger dispara en INSERT y busca la fila previa del empleado.
CREATE TRIGGER tr_auditoria_salario
AFTER INSERT ON Historial_Cargo
FOR EACH ROW
BEGIN
    DECLARE v_cargo_anterior INT DEFAULT NULL;
    DECLARE v_historial_anterior INT DEFAULT NULL;

    SELECT historial_id, cargo_cod
      INTO v_historial_anterior, v_cargo_anterior
    FROM Historial_Cargo
    WHERE legajo = NEW.legajo
      AND historial_id <> NEW.historial_id
    ORDER BY fecha_desde DESC, historial_id DESC
    LIMIT 1;

    IF v_cargo_anterior IS NOT NULL AND v_cargo_anterior <> NEW.cargo_cod THEN
        INSERT INTO Auditoria_Salario
            (legajo, historial_id, cargo_anterior_cod, cargo_nuevo_cod, fecha_cambio)
        VALUES
            (NEW.legajo, NEW.historial_id, v_cargo_anterior, NEW.cargo_cod, NOW());
    END IF;
END //

-- Activa la cuenta de un empleado ya cargado en RRHH: valida
-- legajo + mail contra Empleado y crea el Usuario con rol fijo
-- 'Empleado' y password = legajo (hasheado desde PHP), forzando
-- el cambio en el primer login (debe_cambiar_password = 1).
CREATE PROCEDURE activar_cuenta_empleado(
    IN  p_legajo         INT,
    IN  p_mail           VARCHAR(150),
    IN  p_password_hash  VARCHAR(255),
    OUT p_usuario_id     INT
)
BEGIN
    DECLARE v_mail_real VARCHAR(150);
    DECLARE v_activo    TINYINT;
    DECLARE v_ya_tiene  INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    SELECT mail, activo INTO v_mail_real, v_activo
    FROM Empleado WHERE legajo = p_legajo;

    IF v_mail_real IS NULL OR v_activo = 0 OR v_mail_real <> p_mail THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: los datos ingresados no coinciden con ningún empleado activo.';
    END IF;

    SELECT COUNT(*) INTO v_ya_tiene FROM Usuario WHERE legajo = p_legajo;
    IF v_ya_tiene > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: este legajo ya tiene una cuenta activada.';
    END IF;

    START TRANSACTION;
        INSERT INTO Usuario (legajo, username, password_hash, rol, activo, debe_cambiar_password)
        VALUES (p_legajo, CONCAT('legajo_', p_legajo), p_password_hash, 'Empleado', 1, 1);
        SET p_usuario_id = LAST_INSERT_ID();
    COMMIT;
END //

DELIMITER ;

-- ============================================================
--  BLOQUE 10 – Vistas
--  Contexto PDF: capa de abstracción virtual, desacopla esquema
--  físico de la capa de aplicación.
--  Las tres vistas filtran registros inactivos (activo = 1).
-- ============================================================

-- Resumen completo de empleados activos con antigüedad y promedio
-- calculados por las funciones almacenadas.
CREATE OR REPLACE VIEW v_empleados_resumen AS
SELECT
    e.legajo,
    CONCAT(e.nombre, ' ', e.apellido)     AS nombre_completo,
    e.mail,
    e.fecha_ingreso,
    d.nombre                               AS departamento,
    c.nombre                               AS cargo_actual,
    nj.nombre                              AS nivel_jerarquico,
    l.nombre                               AS localidad,
    calcular_antiguedad(e.fecha_ingreso)   AS anios_antiguedad,
    promedio_puntaje_empleado(e.legajo)    AS promedio_evaluaciones,
    CONCAT(s.nombre, ' ', s.apellido)      AS supervisor
FROM Empleado e
JOIN Departamento d           ON d.depto_cod     = e.depto_cod
JOIN Localidad l              ON l.localidad_cod = e.localidad_cod
LEFT JOIN Empleado s          ON s.legajo        = e.supervisor_legajo
LEFT JOIN Historial_Cargo hc  ON hc.legajo       = e.legajo AND hc.fecha_hasta IS NULL
LEFT JOIN Cargo c             ON c.cargo_cod     = hc.cargo_cod
LEFT JOIN Nivel_Jerarquico nj ON nj.nivel_cod    = c.nivel_cod
WHERE e.activo = 1;

-- Estado actual de cada solicitud (último registro de auditoría).
-- No actualizable: usa subconsulta de agregación → solo lectura.
CREATE OR REPLACE VIEW v_solicitudes_estado_actual AS
SELECT
    sl.nro_solicitud,
    sl.fecha_solicitud,
    sl.fecha_inicio,
    sl.fecha_fin,
    sl.dias_solicitados,
    sl.legajo,
    CONCAT(e.nombre, ' ', e.apellido)  AS empleado,
    tl.nombre                           AS tipo_licencia,
    tl.remunerada,
    a.estado_nuevo                      AS estado_actual,
    a.fecha_cambio                      AS ultima_actualizacion,
    CONCAT(s.nombre, ' ', s.apellido)   AS ultimo_supervisor
FROM Solicitud_Licencia sl
JOIN Empleado e        ON e.legajo        = sl.legajo
JOIN Tipo_Licencia tl  ON tl.tipo_lic_cod = sl.tipo_lic_cod
JOIN Auditoria_Estado_Solicitud a
  ON a.nro_solicitud = sl.nro_solicitud
 AND a.auditoria_id  = (
         SELECT MAX(auditoria_id)
         FROM Auditoria_Estado_Solicitud
         WHERE nro_solicitud = sl.nro_solicitud
     )
JOIN Empleado s ON s.legajo = a.supervisor_legajo;

-- Ranking de evaluaciones agrupado por empleado activo.
-- No actualizable: GROUP BY + funciones de agregación → solo lectura.
CREATE OR REPLACE VIEW v_ranking_evaluaciones AS
SELECT
    e.legajo,
    CONCAT(e.nombre, ' ', e.apellido)  AS nombre_completo,
    d.nombre                            AS departamento,
    COUNT(ed.evaluacion_id)             AS total_evaluaciones,
    promedio_puntaje_empleado(e.legajo) AS promedio_puntaje,
    MAX(ed.puntaje)                     AS puntaje_maximo,
    MIN(ed.puntaje)                     AS puntaje_minimo
FROM Empleado e
JOIN Departamento d ON d.depto_cod = e.depto_cod
LEFT JOIN Evaluacion_Desempeno ed ON ed.legajo = e.legajo
WHERE e.activo = 1
GROUP BY e.legajo, e.nombre, e.apellido, d.nombre;

-- Vista simple, SIN join/agregación/subconsulta → ES actualizable.
-- Mapea 1 a 1 contra Empleado, MySQL puede resolver el UPDATE/INSERT
-- directo sobre la tabla base. Ejemplo práctico para la defensa:
-- diferencia entre vista actualizable (esta) y no actualizable
-- (las tres de arriba, que usan JOIN/GROUP BY/subconsulta).
CREATE OR REPLACE VIEW v_empleados_activos AS
SELECT
    legajo,
    nombre,
    apellido,
    mail,
    fecha_ingreso,
    depto_cod,
    localidad_cod,
    supervisor_legajo
FROM Empleado
WHERE activo = 1;

-- Prueba de que admite escritura (ejecutar para la defensa):
-- UPDATE v_empleados_activos SET mail = 'nuevo@mail.com' WHERE legajo = 1001;
-- Esto modifica la fila real en Empleado. Intentar lo mismo contra
-- v_empleados_resumen falla con Error 1288 (non-updatable view)
-- porque tiene JOIN y columnas calculadas.

-- ============================================================
--  BLOQUE 11 – DCL: usuarios con mínimo privilegio
--  Contexto PDF: principio de mínimo privilegio,
--  separación DDL/DML, restricción de host.
-- ============================================================

-- Usuario de la aplicación PHP: SELECT, INSERT, UPDATE.
-- Sin DELETE ni DDL para reducir superficie de ataque.
-- Las bajas son lógicas (UPDATE activo=0), no físicas.
CREATE USER IF NOT EXISTS 'talenthub_app'@'localhost'
    IDENTIFIED BY 'AppPass2026!';
GRANT SELECT, INSERT, UPDATE
    ON talenthub_db.* TO 'talenthub_app'@'localhost';

-- Usuario de solo lectura para reportes y gerencia.
-- Solo accede a las tres vistas, no a las tablas base.
CREATE USER IF NOT EXISTS 'talenthub_reporter'@'localhost'
    IDENTIFIED BY 'ReportPass2026!';
GRANT SELECT ON talenthub_db.v_empleados_resumen
    TO 'talenthub_reporter'@'localhost';
GRANT SELECT ON talenthub_db.v_solicitudes_estado_actual
    TO 'talenthub_reporter'@'localhost';
GRANT SELECT ON talenthub_db.v_ranking_evaluaciones
    TO 'talenthub_reporter'@'localhost';

GRANT SELECT, INSERT, UPDATE
    ON talenthub_db.* TO 'talenthub_app'@'localhost';
GRANT EXECUTE
    ON talenthub_db.* TO 'talenthub_app'@'localhost';

FLUSH PRIVILEGES;

-- ============================================================
--  BLOQUE 12 – INTO OUTFILE (comentado, ejecutar manualmente)
--  Contexto PDF: exportación selectiva para reportes CSV
-- ============================================================

-- SELECT e.legajo,
--        CONCAT(e.nombre, ' ', e.apellido) AS empleado,
--        c.nombre                           AS cargo,
--        c.banda_salarial_min,
--        c.banda_salarial_max,
--        calcular_antiguedad(e.fecha_ingreso) AS anios_antiguedad
-- INTO OUTFILE '/var/lib/mysql-files/reporte_sueldos_talenthub.csv'
-- FIELDS TERMINATED BY ','
-- LINES TERMINATED BY '\n'
-- FROM Empleado e
-- LEFT JOIN Historial_Cargo hc ON hc.legajo = e.legajo AND hc.fecha_hasta IS NULL
-- LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod
-- WHERE e.activo = 1;