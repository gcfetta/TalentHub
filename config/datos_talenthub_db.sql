-- ============================================================
--  TalentHub - Carga de datos de prueba
--  Ejecutar DESPUES de 01_schema_talenthub.sql
-- ============================================================

USE talenthub_db;

-- ============================================================
--  DATOS DE PRUEBA
--  El trigger tr_cerrar_cargo_anterior se desactiva durante la
--  inserción del historial para respetar las fechas reales.
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

INSERT INTO Cargo (cargo_cod, nombre, banda_salarial_min, banda_salarial_max, nivel_cod, activo) VALUES
(1, 'Gerente General',        400000.00, 700000.00, 1, 1),
(2, 'Gerente de RRHH',        300000.00, 500000.00, 1, 1),
(3, 'Gerente de Sistemas',    300000.00, 500000.00, 1, 1),
(4, 'Jefe de Administración', 180000.00, 280000.00, 2, 1),
(5, 'Coordinador de RRHH',    150000.00, 240000.00, 2, 1),
(6, 'Analista de RRHH',        90000.00, 150000.00, 3, 1),
(7, 'Desarrollador PHP',      100000.00, 170000.00, 3, 1),
(8, 'Administrativo',          70000.00, 110000.00, 3, 1),
(9, 'Recepcionista',           55000.00,  85000.00, 3, 1);

INSERT INTO Departamento (depto_cod, nombre, localidad_cod, activo) VALUES
(1, 'Dirección General', 1, 1),
(2, 'Recursos Humanos',  1, 1),
(3, 'Sistemas',          1, 1),
(4, 'Administración',    2, 1),
(5, 'Operaciones',       3, 1);

-- Gerentes sin supervisor
INSERT INTO Empleado (legajo, nombre, apellido, mail, fecha_ingreso, telefono, localidad_cod, depto_cod, supervisor_legajo, activo) VALUES
(1001, 'Martín',  'Ferreyra', 'mferrey@talenthub.com',  '2018-03-01', '2994100001', 1, 1, NULL, 1),
(1002, 'Claudia', 'Ríos',     'crios@talenthub.com',    '2019-05-15', '2994100002', 1, 2, NULL, 1),
(1003, 'Roberto', 'Sánchez',  'rsanchez@talenthub.com', '2019-08-20', '2994100003', 1, 3, NULL, 1);

-- Empleados con supervisor
INSERT INTO Empleado (legajo, nombre, apellido, mail, fecha_ingreso, telefono, localidad_cod, depto_cod, supervisor_legajo, activo) VALUES
(1004, 'Verónica', 'Luna',    'vluna@talenthub.com',    '2020-01-10', '2994100004', 1, 2, 1002, 1),
(1005, 'Diego',    'Morales', 'dmorales@talenthub.com', '2020-06-01', '2994100005', 1, 3, 1003, 1),
(1006, 'Sofía',    'Paredes', 'sparedes@talenthub.com', '2021-02-15', '2994100006', 2, 4, 1001, 1),
(1007, 'Hernán',   'Castro',  'hcastro@talenthub.com',  '2021-07-01', '2994100007', 1, 2, 1002, 1),
(1008, 'Natalia',  'Gómez',   'ngomez@talenthub.com',   '2022-03-10', '2994100008', 1, 3, 1003, 1),
(1009, 'Ezequiel', 'Vidal',   'evidal@talenthub.com',   '2022-09-01', '2994100009', 2, 4, 1006, 1),
(1010, 'Laura',    'Ibáñez',  'libanez@talenthub.com',  '2023-01-16', '2994100010', 3, 5, 1001, 1);

-- password_hash se genera con setup_usuarios.php
INSERT INTO Usuario (usuario_id, legajo, username, password_hash, rol, activo) VALUES
(1, 1001, 'admin',    'PENDIENTE_BCRYPT', 'Administrador', 1),
(2, 1002, 'crios',    'PENDIENTE_BCRYPT', 'RRHH',          1),
(3, 1003, 'rsanchez', 'PENDIENTE_BCRYPT', 'Supervisor',    1),
(4, 1004, 'vluna',    'PENDIENTE_BCRYPT', 'RRHH',          1),
(5, 1005, 'dmorales', 'PENDIENTE_BCRYPT', 'Empleado',      1),
(6, 1006, 'sparedes', 'PENDIENTE_BCRYPT', 'Empleado',      1);

-- Desactivar tr_cerrar_cargo_anterior durante la carga semilla.
-- Los datos históricos ya tienen fechas explícitas correctas.
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

-- Datos de muestra en Auditoria_Salario: corresponden a los
-- cambios de cargo reales del historial. La tabla no aparece
-- vacía y el trigger tiene contexto visible en la presentación.
INSERT INTO Auditoria_Salario (legajo, historial_id, cargo_anterior_cod, cargo_nuevo_cod, fecha_cambio) VALUES
(1001, 2, 4, 1, '2021-01-01 08:00:00'),
(1004, 6, 6, 5, '2022-07-01 08:00:00'),
(1006, 9, 8, 4, '2023-04-01 08:00:00');