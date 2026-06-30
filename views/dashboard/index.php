<?php
// views/dashboard/index.php
require_once __DIR__ . '/../../guards/auth_guard.php';
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <span class="page-subtitle">Resumen general del sistema</span>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value"><?= $total_empleados ?></span>
            <span class="stat-label">Empleados activos</span>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <span class="stat-value"><?= $pendientes ?></span>
            <span class="stat-label">Licencias pendientes</span>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">⭐</div>
        <div class="stat-info">
            <span class="stat-value"><?= $total_evaluaciones ?></span>
            <span class="stat-label">Evaluaciones realizadas</span>
        </div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon">📈</div>
        <div class="stat-info">
            <span class="stat-value"><?= $prom_puntaje ?? '—' ?></span>
            <span class="stat-label">Puntaje promedio</span>
        </div>
    </div>
</div>

<!-- Últimas solicitudes -->
<div class="card">
    <div class="card-header">
        <h2>Últimas solicitudes de licencia</h2>
        <a href="index.php?page=solicitudes" class="btn btn-sm">Ver todas →</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Empleado</th>
                <th>Tipo</th>
                <th>Desde</th>
                <th>Días</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ultimas_solicitudes as $row):
            $badge = match($row['estado']) {
                'Aprobada'  => 'badge-success',
                'Rechazada' => 'badge-danger',
                default     => 'badge-warning',
            };
        ?>
        <tr>
            <td>#<?= $row['nro_solicitud'] ?></td>
            <td><?= htmlspecialchars($row['empleado']) ?></td>
            <td><?= htmlspecialchars($row['tipo_licencia']) ?></td>
            <td><?= $row['fecha_inicio'] ?></td>
            <td><?= $row['dias_solicitados'] ?></td>
            <td><span class="badge <?= $badge ?>"><?= $row['estado'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Últimas evaluaciones -->
<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <h2>Últimas evaluaciones</h2>
        <a href="index.php?page=evaluaciones" class="btn btn-sm">Ver todas →</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Período</th>
                <th>Puntaje</th>
                <th>Evaluador</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ultimas_evaluaciones as $row):
            $color = $row['puntaje'] >= 9 ? 'badge-success'
                   : ($row['puntaje'] >= 7 ? 'badge-info' : 'badge-warning');
        ?>
        <tr>
            <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
            <td><?= $row['periodo'] ?></td>
            <td><span class="badge <?= $color ?>"><?= $row['puntaje'] ?></span></td>
            <td><?= htmlspecialchars($row['evaluador']) ?></td>
            <td><?= $row['fecha_evaluacion'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>