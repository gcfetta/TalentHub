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
    <div class="stat-card primary">
        <div class="stat-card-top">
            <span class="stat-value"><?= $total_empleados ?></span>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-label">Empleados activos</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-card-top">
            <span class="stat-value"><?= $pendientes ?></span>
            <div class="stat-icon">⏳</div>
        </div>
        <div class="stat-label">Licencias pendientes</div>
    </div>
    <div class="stat-card success">
        <div class="stat-card-top">
            <span class="stat-value"><?= $total_evaluaciones ?></span>
            <div class="stat-icon">⭐</div>
        </div>
        <div class="stat-label">Evaluaciones realizadas</div>
    </div>
    <div class="stat-card info">
        <div class="stat-card-top">
            <span class="stat-value"><?= $prom_puntaje ?? '—' ?></span>
            <div class="stat-icon">📈</div>
        </div>
        <div class="stat-label">Puntaje promedio</div>
    </div>
</div>

<div class="dashboard-panels-grid">

    <div class="card">
        <div class="card-header">
            <h2>📄 Últimas licencias</h2>
            <a href="index.php?page=solicitudes" class="btn-sm">Ver todas →</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($ultimas_solicitudes, 0, 5) as $row):
                $badge = match($row['estado']) {
                    'Aprobada'  => 'badge-success',
                    'Rechazada' => 'badge-danger',
                    default     => 'badge-warning',
                };
            ?>
            <tr>
                <td><?= htmlspecialchars($row['empleado']) ?></td>
                <td><?= htmlspecialchars($row['tipo_licencia']) ?></td>
                <td><span class="badge <?= $badge ?>"><?= $row['estado'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>⭐ Últimas evaluaciones</h2>
            <a href="index.php?page=evaluaciones" class="btn-sm">Ver todas →</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Período</th>
                    <th>Puntaje</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($ultimas_evaluaciones, 0, 5) as $row):
                $scoreStyle = $row['puntaje'] >= 8.5 ? 'high' : ($row['puntaje'] >= 7.0 ? 'mid' : 'low');
            ?>
            <tr>
                <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                <td><?= $row['periodo'] ?></td>
                <td><span class="score-badge <?= $scoreStyle ?>"><?= number_format($row['puntaje'], 2) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h2>👥 Empleados <span class="badge badge-info" style="margin-left: 0.5rem;"><?= $total_empleados ?></span></h2>
        <a href="index.php?page=empleados&action=nuevo" class="btn-primary">+ Nuevo empleado</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Evaluación</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php 
        // Asumiendo que reutilizas datos o posees la variable de la lista general de empleados. 
        // Si no cuentas con $empleados_list, puedes usar temporalmente $ultimas_evaluaciones para poblarla.
        $lista_render = isset($empleados_list) ? $empleados_list : $ultimas_evaluaciones;
        foreach (array_slice($lista_render, 0, 4) as $idx => $emp): 
            $nombre_completo = isset($emp['nombre']) ? ($emp['nombre'] . ' ' . $emp['apellido']) : ($emp['empleado'] ?? 'Empleado');
            $inicial = mb_substr($nombre_completo, 0, 1);
            
            // Colores aleatorios/fijos para los avatares circulares de la maqueta
            $bg_avatars = ['#a855f7', '#3b82f6', '#ec4899', '#10b981'];
            $avatar_color = $bg_avatars[$idx % count($bg_avatars)];
            
            $puntaje_num = $emp['puntaje'] ?? null;
            $scoreStyle = $puntaje_num >= 8.5 ? 'high' : ($puntaje_num >= 7.0 ? 'mid' : 'low');
        ?>
        <tr>
            <td style="display: flex; align-items: center;">
                <div class="avatar-circle" style="background: <?= $avatar_color ?>;"><?= $inicial ?></div>
                <div class="employee-meta">
                    <strong><?= htmlspecialchars($nombre_completo) ?></strong>
                    <span class="employee-id">#100<?= $idx + 2 ?></span>
                </div>
            </td>
            <td><?= htmlspecialchars($emp['cargo'] ?? 'Desarrollador PHP') ?></td>
            <td><?= htmlspecialchars($emp['departamento'] ?? 'Sistemas') ?></td>
            <td>
                <?php if ($puntaje_num): ?>
                    <span class="score-badge <?= $scoreStyle ?>"><?= number_format($puntaje_num, 2) ?></span>
                <?php else: ?>
                    <span style="color: var(--text-muted);">—</span>
                <?php endif; ?>
            </td>
            <td style="text-align: right; color: var(--text-muted); cursor: pointer;">•••</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>