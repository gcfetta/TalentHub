-- ============================================================
--  TalentHub – Sistema de Recursos Humanos
--  Script completo: tablas + programabilidad + datos de prueba
-- ============================================================

CREATE DATABASE IF NOT EXISTS talenthub_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE talenthub_db;

-- ============================================================
--  BLOQUE 1 – Tablas maestras
-- ============================================================

CREATE TABLE Localidad (
    localidad_cod   INT          PRIMARY KEY AUTO_INCREMENT,
    nombre          VARCHAR(100) NOT NULL,
    provincia       VARCHAR(100) NOT NULL
);

CREATE TABLE Nivel_Jerarquico (
    nivel_cod       INT          PRIMARY KEY AUTO_INCREMENT,
    nombre          VARCHAR(50)  NOT NULL,
    descripcion     VARCHAR(255)
);

CREATE TABLE Tipo_Licencia (
    tipo_lic_cod         INT          PRIMARY KEY AUTO_INCREMENT,
    nombre               VARCHAR(100) NOT NULL,
    dias_max             INT          NOT NULL,
    requiere_certificado TINYINT(1)   NOT NULL DEFAULT 0,
    remunerada           TINYINT(1)   NOT NULL DEFAULT 1
);

-- ============================================================
--  BLOQUE 2 – Tablas con una sola FK
-- ============================================================

CREATE TABLE Cargo (
    cargo_cod          INT           PRIMARY KEY AUTO_INCREMENT,
    nombre             VARCHAR(100)  NOT NULL,
    banda_salarial_min DECIMAL(12,2) NOT NULL,
    banda_salarial_max DECIMAL(12,2) NOT NULL,
    nivel_cod          INT           NOT NULL,
    CONSTRAINT fk_cargo_nivel
        FOREIGN KEY (nivel_cod) REFERENCES Nivel_Jerarquico(nivel_cod)
);

CREATE TABLE Departamento (
    depto_cod     INT          PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(100) NOT NULL,
    localidad_cod INT          NOT NULL,
    CONSTRAINT fk_depto_localidad
        FOREIGN KEY (localidad_cod) REFERENCES Localidad(localidad_cod)
);

-- ============================================================
--  BLOQUE 3 – Empleado
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
    CONSTRAINT fk_empleado_localidad
        FOREIGN KEY (localidad_cod)     REFERENCES Localidad(localidad_cod),
    CONSTRAINT fk_empleado_depto
        FOREIGN KEY (depto_cod)         REFERENCES Departamento(depto_cod),
    CONSTRAINT fk_empleado_supervisor
        FOREIGN KEY (supervisor_legajo) REFERENCES Empleado(legajo)
);

-- ============================================================
--  BLOQUE 4 – Historial, Evaluaciones y Auditoría Salarial
--  Auditoria_Salario va aquí para que el trigger pueda referenciarla
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
    CONSTRAINT fk_usuario_empleado
        FOREIGN KEY (legajo) REFERENCES Empleado(legajo)
);

-- ============================================================
--  BLOQUE 7 – Funciones almacenadas
--  Contexto PDF: "Caso TalentHub – Cálculo de Antigüedad"
--  Las funciones van ANTES de los procedimientos y vistas
--  que las invocan.
-- ============================================================

DELIMITER //

-- Devuelve años completos de antigüedad. DETERMINISTIC porque
-- dado el mismo input siempre retorna el mismo valor.
-- Usable directamente en SELECT como columna calculada.
CREATE FUNCTION calcular_antiguedad(p_fecha_ingreso DATE)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_fecha_ingreso, CURDATE());
END //

-- Devuelve promedio de puntajes de evaluación de un empleado.
-- Retorna 0.00 si no tiene evaluaciones (COALESCE evita NULL).
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

-- Registra una nueva solicitud de licencia con validación de
-- días disponibles. El OUT devuelve el nro generado al PHP.
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
-- El PHP llama CALL en lugar de dos INSERTs separados.
CREATE PROCEDURE cambiar_estado_solicitud(
    IN p_nro_solicitud     INT,
    IN p_estado_anterior   VARCHAR(50),
    IN p_estado_nuevo      VARCHAR(50),
    IN p_observacion       VARCHAR(500),
    IN p_supervisor_legajo INT
)
BEGIN
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

-- AFTER UPDATE: registra en Auditoria_Salario cada vez que
-- un empleado cambia de cargo en su historial.
-- Equivalente exacto al "Trigger de Historial Salarial" del PDF.
CREATE TRIGGER tr_auditoria_salario
AFTER UPDATE ON Historial_Cargo
FOR EACH ROW
BEGIN
    IF OLD.cargo_cod <> NEW.cargo_cod THEN
        INSERT INTO Auditoria_Salario
            (legajo, historial_id, cargo_anterior_cod, cargo_nuevo_cod, fecha_cambio)
        VALUES
            (NEW.legajo, NEW.historial_id, OLD.cargo_cod, NEW.cargo_cod, NOW());
    END IF;
END //

-- BEFORE INSERT: cierra el cargo activo anterior antes de abrir
-- uno nuevo. Garantiza que nunca haya dos registros abiertos
-- (fecha_hasta IS NULL) para el mismo empleado.
-- NOTA: este trigger se desactiva durante la carga de datos
-- históricos (ver SET @skip_trigger más abajo).
CREATE TRIGGER tr_cerrar_cargo_anterior
BEFORE INSERT ON Historial_Cargo
FOR EACH ROW
BEGIN
    IF @skip_trigger IS NULL OR @skip_trigger = 0 THEN
        UPDATE Historial_Cargo
        SET fecha_hasta = NEW.fecha_desde - INTERVAL 1 DAY
        WHERE legajo      = NEW.legajo
          AND fecha_hasta IS NULL;
    END IF;
END //

DELIMITER ;

-- ============================================================
--  BLOQUE 10 – Vistas
--  Contexto PDF: capa de abstracción virtual, desacopla esquema
--  físico de la capa de aplicación.
-- ============================================================

-- Resumen completo de empleados con antigüedad y promedio
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
JOIN Departamento d           ON d.depto_cod    = e.depto_cod
JOIN Localidad l              ON l.localidad_cod = e.localidad_cod
LEFT JOIN Empleado s          ON s.legajo       = e.supervisor_legajo
LEFT JOIN Historial_Cargo hc  ON hc.legajo      = e.legajo AND hc.fecha_hasta IS NULL
LEFT JOIN Cargo c             ON c.cargo_cod    = hc.cargo_cod
LEFT JOIN Nivel_Jerarquico nj ON nj.nivel_cod   = c.nivel_cod;

-- Estado actual de cada solicitud (solo el último registro de auditoría).
-- No es actualizable por usar subconsulta de agregación — solo lectura.
CREATE OR REPLACE VIEW v_solicitudes_estado_actual AS
SELECT
    sl.nro_solicitud,
    sl.fecha_solicitud,
    sl.fecha_inicio,
    sl.fecha_fin,
    sl.dias_solicitados,
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

-- Ranking de evaluaciones agrupado por empleado.
-- No actualizable (GROUP BY + funciones de agregación).
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
GROUP BY e.legajo, e.nombre, e.apellido, d.nombre;

-- ============================================================
--  BLOQUE 11 – DCL: usuarios con mínimo privilegio
--  Contexto PDF: principio de mínimo privilegio,
--  separación DDL/DML, restricción de host.
-- ============================================================

CREATE USER IF NOT EXISTS 'talenthub_app'@'localhost'
    IDENTIFIED BY 'AppPass2026!';
GRANT SELECT, INSERT, UPDATE
    ON talenthub_db.* TO 'talenthub_app'@'localhost';

CREATE USER IF NOT EXISTS 'talenthub_reporter'@'localhost'
    IDENTIFIED BY 'ReportPass2026!';
GRANT SELECT ON talenthub_db.v_empleados_resumen
    TO 'talenthub_reporter'@'localhost';
GRANT SELECT ON talenthub_db.v_solicitudes_estado_actual
    TO 'talenthub_reporter'@'localhost';
GRANT SELECT ON talenthub_db.v_ranking_evaluaciones
    TO 'talenthub_reporter'@'localhost';

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
-- LEFT JOIN Cargo c ON c.cargo_cod = hc.cargo_cod;

-- ============================================================
--  DATOS DE PRUEBA
--  IMPORTANTE: el trigger tr_cerrar_cargo_anterior se desactiva
--  durante la inserción histórica para respetar las fechas reales.
-- ============================================================

INSERT INTO Localidad (localidad_cod, nombre, provincia) VALUES
(1, 'Neuquén Capital', 'Neuquén'),
(2, 'Cipolletti',      'Río Negro'),
(3, 'Buenos Aires',    'Buenos Aires'),
(4, 'Mendoza',         'Mendoza');

INSERT INTO Nivel_Jerarquico (nivel_cod, nombre, descripcion) VALUES
(1, 'Estratégico', 'Alta dirección y gerencia general'),
(2, 'Táctico',     'Jefaturas y coordinaciones'),
(3, 'Operativo',   'Personal de base y analistas');

INSERT INTO Tipo_Licencia (tipo_lic_cod, nombre, dias_max, requiere_certificado, remunerada) VALUES
(1, 'Vacaciones Anuales',    30, 0, 1),
(2, 'Licencia Médica',       15, 1, 1),
(3, 'Licencia por Duelo',     5, 1, 1),
(4, 'Licencia sin Goce',     10, 0, 0),
(5, 'Maternidad/Paternidad', 90, 1, 1);

INSERT INTO Cargo (cargo_cod, nombre, banda_salarial_min, banda_salarial_max, nivel_cod) VALUES
(1, 'Gerente General',        400000.00, 700000.00, 1),
(2, 'Gerente de RRHH',        300000.00, 500000.00, 1),
(3, 'Gerente de Sistemas',    300000.00, 500000.00, 1),
(4, 'Jefe de Administración', 180000.00, 280000.00, 2),
(5, 'Coordinador de RRHH',    150000.00, 240000.00, 2),
(6, 'Analista de RRHH',        90000.00, 150000.00, 3),
(7, 'Desarrollador PHP',      100000.00, 170000.00, 3),
(8, 'Administrativo',          70000.00, 110000.00, 3),
(9, 'Recepcionista',           55000.00,  85000.00, 3);

INSERT INTO Departamento (depto_cod, nombre, localidad_cod) VALUES
(1, 'Dirección General', 1),
(2, 'Recursos Humanos',  1),
(3, 'Sistemas',          1),
(4, 'Administración',    2),
(5, 'Operaciones',       3);

-- Sin supervisor primero (gerentes)
INSERT INTO Empleado (legajo, nombre, apellido, mail, fecha_ingreso, telefono, localidad_cod, depto_cod, supervisor_legajo) VALUES
(1001, 'Martín',  'Ferreyra', 'mferrey@talenthub.com',  '2018-03-01', '2994100001', 1, 1, NULL),
(1002, 'Claudia', 'Ríos',     'crios@talenthub.com',    '2019-05-15', '2994100002', 1, 2, NULL),
(1003, 'Roberto', 'Sánchez',  'rsanchez@talenthub.com', '2019-08-20', '2994100003', 1, 3, NULL);

-- Con supervisor
INSERT INTO Empleado (legajo, nombre, apellido, mail, fecha_ingreso, telefono, localidad_cod, depto_cod, supervisor_legajo) VALUES
(1004, 'Verónica', 'Luna',    'vluna@talenthub.com',    '2020-01-10', '2994100004', 1, 2, 1002),
(1005, 'Diego',    'Morales', 'dmorales@talenthub.com', '2020-06-01', '2994100005', 1, 3, 1003),
(1006, 'Sofía',    'Paredes', 'sparedes@talenthub.com', '2021-02-15', '2994100006', 2, 4, 1001),
(1007, 'Hernán',   'Castro',  'hcastro@talenthub.com',  '2021-07-01', '2994100007', 1, 2, 1002),
(1008, 'Natalia',  'Gómez',   'ngomez@talenthub.com',   '2022-03-10', '2994100008', 1, 3, 1003),
(1009, 'Ezequiel', 'Vidal',   'evidal@talenthub.com',   '2022-09-01', '2994100009', 2, 4, 1006),
(1010, 'Laura',    'Ibáñez',  'libanez@talenthub.com',  '2023-01-16', '2994100010', 3, 5, 1001);

-- password_hash se genera con setup_usuarios.php
INSERT INTO Usuario (usuario_id, legajo, username, password_hash, rol, activo) VALUES
(1, 1001, 'admin',    'PENDIENTE_BCRYPT', 'Administrador', 1),
(2, 1002, 'crios',    'PENDIENTE_BCRYPT', 'RRHH',          1),
(3, 1003, 'rsanchez', 'PENDIENTE_BCRYPT', 'Supervisor',    1),
(4, 1004, 'vluna',    'PENDIENTE_BCRYPT', 'RRHH',          1),
(5, 1005, 'dmorales', 'PENDIENTE_BCRYPT', 'Empleado',      1),
(6, 1006, 'sparedes', 'PENDIENTE_BCRYPT', 'Empleado',      1);

-- Desactivar tr_cerrar_cargo_anterior para respetar historia real.
-- Los datos tienen fechas explícitas; el trigger solo aplica en
-- operaciones normales de producción, no en carga de semilla.
SET @skip_trigger = 1;

INSERT INTO Historial_Cargo (legajo, cargo_cod, fecha_desde, fecha_hasta) VALUES
(1001, 4, '2018-03-01', '2020-12-31'),
(1001, 1, '2021-01-01', NULL),
(1002, 2, '2019-05-15', NULL),
(1003, 3, '2019-08-20', NULL),
(1004, 6, '2020-01-10', '2022-06-30'),
(1004, 5, '2022-07-01', NULL),
(1005, 7, '2020-06-01', NULL),
(1006, 8, '2021-02-15', '2023-03-31'),
(1006, 4, '2023-04-01', NULL),
(1007, 6, '2021-07-01', NULL),
(1008, 7, '2022-03-10', NULL),
(1009, 8, '2022-09-01', NULL),
(1010, 9, '2023-01-16', NULL);

SET @skip_trigger = 0;

INSERT INTO Evaluacion_Desempeno (legajo, periodo, puntaje, observaciones, evaluador_legajo, fecha_evaluacion) VALUES
(1004, '2023-S1', 8.50, 'Excelente desempeño en gestión de legajos.',          1002, '2023-07-10'),
(1004, '2023-S2', 9.00, 'Lideró el proyecto de digitalización de contratos.',  1002, '2024-01-15'),
(1005, '2023-S1', 7.80, 'Buen rendimiento. Mejorar documentación de código.',  1003, '2023-07-12'),
(1005, '2023-S2', 8.20, 'Incorporó buenas prácticas de testing.',              1003, '2024-01-18'),
(1006, '2023-S1', 9.50, 'Reorganizó el área administrativa con gran impacto.', 1001, '2023-07-08'),
(1007, '2023-S1', 7.00, 'Desempeño dentro de lo esperado.',                   1002, '2023-07-11'),
(1007, '2023-S2', 7.50, 'Mejora notable en atención al empleado.',             1002, '2024-01-20'),
(1008, '2023-S2', 8.80, 'Desarrolló el módulo de reportes con autonomía.',     1003, '2024-01-22'),
(1009, '2023-S2', 6.50, 'Requiere mayor prolijidad en el archivo de docs.',    1006, '2024-01-25'),
(1010, '2023-S2', 7.20, 'Buena presencia y trato con el personal.',            1001, '2024-01-30');

INSERT INTO Solicitud_Licencia (nro_solicitud, fecha_solicitud, fecha_inicio, fecha_fin, dias_solicitados, legajo, tipo_lic_cod) VALUES
(1,  '2024-01-05', '2024-01-15', '2024-01-19',  5, 1004, 2),
(2,  '2024-02-01', '2024-02-10', '2024-03-10', 30, 1005, 1),
(3,  '2024-03-12', '2024-03-14', '2024-03-16',  3, 1007, 3),
(4,  '2024-04-01', '2024-04-08', '2024-04-12',  5, 1008, 4),
(5,  '2024-05-20', '2024-06-01', '2024-08-29', 90, 1010, 5),
(6,  '2024-06-10', '2024-06-17', '2024-06-21',  5, 1006, 2),
(7,  '2024-07-01', '2024-07-15', '2024-08-13', 30, 1009, 1),
(8,  '2024-08-05', '2024-08-12', '2024-08-14',  3, 1004, 3),
(9,  '2024-09-01', '2024-09-10', '2024-09-12',  3, 1007, 2),
(10, '2024-10-15', '2024-11-01', '2024-11-05',  5, 1005, 4);

INSERT INTO Auditoria_Estado_Solicitud (nro_solicitud, estado_anterior, estado_nuevo, observacion, supervisor_legajo, fecha_cambio) VALUES
(1,  NULL,        'Pendiente', 'Solicitud registrada.',                    1002, '2024-01-05 09:00:00'),
(1,  'Pendiente', 'Aprobada',  'Certificado médico verificado.',           1002, '2024-01-08 10:30:00'),
(2,  NULL,        'Pendiente', 'Solicitud registrada.',                    1003, '2024-02-01 08:00:00'),
(2,  'Pendiente', 'Aprobada',  'Vacaciones correspondientes al período.',  1003, '2024-02-05 11:00:00'),
(3,  NULL,        'Pendiente', 'Solicitud registrada.',                    1002, '2024-03-12 09:15:00'),
(3,  'Pendiente', 'Aprobada',  'Acreditado certificado de defunción.',     1002, '2024-03-13 08:00:00'),
(4,  NULL,        'Pendiente', 'Solicitud registrada.',                    1003, '2024-04-01 10:00:00'),
(4,  'Pendiente', 'Rechazada', 'No se justificó la necesidad.',            1003, '2024-04-03 09:00:00'),
(5,  NULL,        'Pendiente', 'Solicitud registrada.',                    1001, '2024-05-20 08:30:00'),
(5,  'Pendiente', 'Aprobada',  'Maternidad aprobada según convenio.',      1001, '2024-05-22 09:00:00'),
(6,  NULL,        'Pendiente', 'Solicitud registrada, en revisión.',       1002, '2024-06-10 11:00:00'),
(7,  NULL,        'Pendiente', 'Solicitud registrada.',                    1006, '2024-07-01 08:00:00'),
(7,  'Pendiente', 'Aprobada',  'Vacaciones aprobadas.',                    1006, '2024-07-03 09:30:00'),
(8,  NULL,        'Pendiente', 'Solicitud registrada.',                    1002, '2024-08-05 10:00:00'),
(8,  'Pendiente', 'Aprobada',  'Certificado presentado correctamente.',    1002, '2024-08-06 08:15:00'),
(9,  NULL,        'Pendiente', 'Solicitud registrada.',                    1002, '2024-09-01 09:00:00'),
(9,  'Pendiente', 'Rechazada', 'Ya consumió el máximo de días médicos.',   1002, '2024-09-03 10:00:00'),
(10, NULL,        'Pendiente', 'Solicitud registrada, pendiente de firma.', 1003, '2024-10-15 08:45:00');

INSERT INTO Documento_Licencia (nro_solicitud, tipo_documento, archivo_path, fecha_carga) VALUES
(1, 'Certificado médico',       'docs/licencias/cert_med_1004_ene24.pdf', '2024-01-06 10:00:00'),
(3, 'Certificado de defunción', 'docs/licencias/cert_defuncion_1007.pdf', '2024-03-12 14:00:00'),
(5, 'Partida de nacimiento',    'docs/licencias/partida_1010.pdf',        '2024-05-20 09:00:00'),
(6, 'Certificado médico',       'docs/licencias/cert_med_1006_jun24.pdf', '2024-06-10 11:30:00'),
(8, 'Certificado médico',       'docs/licencias/cert_med_1004_ago24.pdf', '2024-08-05 10:15:00');

-- Datos de muestra en Auditoria_Salario (cambios de cargo históricos)
-- para que la tabla no aparezca vacía en el proyecto.
-- Corresponden a los cambios reales del Historial_Cargo.
INSERT INTO Auditoria_Salario (legajo, historial_id, cargo_anterior_cod, cargo_nuevo_cod, fecha_cambio) VALUES
(1001, 2,  4, 1, '2021-01-01 08:00:00'),
(1004, 6,  6, 5, '2022-07-01 08:00:00'),
(1006, 9,  8, 4, '2023-04-01 08:00:00');