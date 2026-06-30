<?php
// controllers/DashboardController.php

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController {
    private $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new DashboardModel($pdo);
    }

    public function index(): void {
        $total_empleados    = $this->modelo->totalEmpleados();
        $pendientes         = $this->modelo->solicitudesPendientes();
        $total_evaluaciones = $this->modelo->totalEvaluaciones();
        $prom_puntaje       = $this->modelo->promedioPuntaje();
        $ultimas_solicitudes  = $this->modelo->ultimasSolicitudes();
        $ultimas_evaluaciones = $this->modelo->ultimasEvaluaciones();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}