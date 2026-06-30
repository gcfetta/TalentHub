<?php
// controllers/SolicitudController.php

require_once __DIR__ . '/../models/SolicitudLicenciaModel.php';

class SolicitudController {
    private $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new SolicitudLicenciaModel($pdo);
    }

    public function manejar(): void {
        $accion = $_GET['accion'] ?? 'listado';

        match($accion) {
            'listado'       => $this->listado(),
            'ver'           => $this->ver(),
            'nueva'         => $this->formulario(),
            'guardar'       => $this->guardar(),
            'cambiar_estado'=> $this->cambiarEstado(),
            default         => $this->listado(),
        };
    }

    private function listado(): void {
        $rol    = $_SESSION['rol'];
        $legajo = (int)$_SESSION['legajo'];

        // Empleado solo ve las suyas; RRHH y Admin ven todas
        $filtro = in_array($rol, ['Administrador', 'RRHH', 'Supervisor']) ? null : $legajo;
        $solicitudes = $this->modelo->obtenerTodas($filtro);

        require_once __DIR__ . '/../views/solicitudes/listado.php';
    }

    private function ver(): void {
        $nro = (int)($_GET['nro'] ?? 0);
        $solicitud  = $this->modelo->obtenerPorId($nro);

        if (!$solicitud) {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        // Empleado solo puede ver las suyas
        if ($_SESSION['rol'] === 'Empleado' && $solicitud['legajo'] != $_SESSION['legajo']) {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        $auditoria  = $this->modelo->obtenerAuditoria($nro);
        $documentos = $this->modelo->obtenerDocumentos($nro);
        require_once __DIR__ . '/../views/solicitudes/detalle.php';
    }

    private function formulario(): void {
        $tipos      = $this->modelo->obtenerTiposLicencia();
        $empleados  = $this->modelo->obtenerEmpleados();
        $error      = '';
        require_once __DIR__ . '/../views/solicitudes/formulario.php';
    }

    private function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        $legajo      = (int)($_POST['legajo']      ?? 0);
        $tipo_lic    = (int)($_POST['tipo_lic_cod'] ?? 0);
        $fecha_ini   = $_POST['fecha_inicio'] ?? '';
        $fecha_fin   = $_POST['fecha_fin']    ?? '';
        $observacion = trim($_POST['observacion'] ?? '');
        $error       = '';

        // Validación
        if (!$legajo || !$tipo_lic || !$fecha_ini || !$fecha_fin) {
            $error = 'Completá todos los campos obligatorios.';
        } elseif ($fecha_fin < $fecha_ini) {
            $error = 'La fecha de fin no puede ser anterior a la de inicio.';
        }

        if (!$error) {
            $dias = (int)((strtotime($fecha_fin) - strtotime($fecha_ini)) / 86400) + 1;

            // Verificar límite de días
            $tipos_data = $this->modelo->obtenerTiposLicencia();
            $tipo_info  = array_values(array_filter($tipos_data, fn($t) => $t['tipo_lic_cod'] == $tipo_lic))[0] ?? null;

            if ($tipo_info) {
                $anio       = (int)date('Y', strtotime($fecha_ini));
                $dias_usados = $this->modelo->diasUsadosEnAnio($legajo, $tipo_lic, $anio);
                if (($dias_usados + $dias) > $tipo_info['dias_max']) {
                    $error = "Supera el límite anual de {$tipo_info['dias_max']} días para este tipo de licencia. Ya usó {$dias_usados} días.";
                }
            }
        }

        if ($error) {
            $tipos     = $this->modelo->obtenerTiposLicencia();
            $empleados = $this->modelo->obtenerEmpleados();
            require_once __DIR__ . '/../views/solicitudes/formulario.php';
            return;
        }

        $datos = [
            'fecha_inicio'     => $fecha_ini,
            'fecha_fin'        => $fecha_fin,
            'dias_solicitados' => $dias,
            'legajo'           => $legajo,
            'tipo_lic_cod'     => $tipo_lic,
        ];

        try {
            $nro = $this->modelo->crear($datos, (int)$_SESSION['legajo']);
            header("Location: index.php?page=solicitudes&accion=ver&nro={$nro}&ok=1");
            exit;
        } catch (Exception $e) {
            $error     = 'Error al guardar: ' . $e->getMessage();
            $tipos     = $this->modelo->obtenerTiposLicencia();
            $empleados = $this->modelo->obtenerEmpleados();
            require_once __DIR__ . '/../views/solicitudes/formulario.php';
        }
    }

    private function cambiarEstado(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        // Solo Administrador, RRHH y Supervisor pueden cambiar estado
        if (!in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])) {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        $nro           = (int)($_POST['nro_solicitud']  ?? 0);
        $estado_nuevo  = $_POST['estado_nuevo']          ?? '';
        $observacion   = trim($_POST['observacion']      ?? '');
        $estado_actual = $_POST['estado_actual']         ?? '';

        $estados_validos = ['Aprobada', 'Rechazada', 'Pendiente'];
        if (!$nro || !in_array($estado_nuevo, $estados_validos)) {
            header('Location: index.php?page=solicitudes');
            exit;
        }

        $this->modelo->cambiarEstado(
            $nro,
            $estado_actual,
            $estado_nuevo,
            $observacion ?: "Estado cambiado a {$estado_nuevo}.",
            (int)$_SESSION['legajo']
        );

        header("Location: index.php?page=solicitudes&accion=ver&nro={$nro}&ok=1");
        exit;
    }
}