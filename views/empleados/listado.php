<?php
// views/empleados/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>👥 Empleados</h1>
    <span class="page-subtitle"><?= count($empleados) ?> registros en total</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Empleado guardado correctamente.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Nómina Activa</h2>
        <div style="display: flex; gap: 0.5rem;">
            <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                <a href="index.php?page=empleados&accion=exportar" class="btn btn-secondary">⬇ Exportar CSV</a>
                <a href="index.php?page=empleados&accion=nuevo" class="btn">+ Nuevo empleado</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($empleados)): ?>
        <div class="empty-state">
            <span class="empty-icon">👥</span>
            No hay empleados registrados en el sistema.
        </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Cargo actual</th>
                <th>Departamento</th>
                <th>Localidad</th>
                <th>Ingreso</th>
                <th>Evaluación</th>
                <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        foreach ($empleados as $idx => $emp): 
            $nombre_completo = $emp['nombre_completo'];
            $inicial = mb_substr(trim($emp['nombre_completo']), 0, 1);
            
            $bg_avatars = ['#a855f7', '#3b82f6', '#ec4899', '#10b981', '#f59e0b'];
            $avatar_color = $bg_avatars[$idx % count($bg_avatars)];
            
            $promedio = $emp['promedio_evaluaciones'];
            $scoreStyle = ($promedio !== null && $promedio >= 7) ? 'high' : 'low';
        ?>
        <tr>
            <!-- Columna Principal de Empleado con Avatar e ID -->
            <td style="display: flex; align-items: center;">
                <div class="avatar-circle" style="background: <?= $avatar_color ?>;"><?= htmlspecialchars($inicial) ?></div>
                <div class="employee-meta">
                    <strong><?= htmlspecialchars($nombre_completo) ?></strong>
                    <span class="employee-id">#<?= htmlspecialchars($emp['legajo']) ?></span>
                </div>
            </td>
            
            <td><?= htmlspecialchars($emp['cargo_actual'] ?? '—') ?></td>
            <td><?= htmlspecialchars($emp['departamento'] ?? '—') ?></td>
            <td><?= htmlspecialchars($emp['localidad'] ?? '—') ?></td>
            <td><?= htmlspecialchars($emp['fecha_ingreso'] ?? '—') ?></td>
            
            <!-- Columna de Promedio Evaluaciones -->
            <td>
                <?php if ($promedio !== null): ?>
                    <span class="score-badge <?= $scoreStyle ?>">
                        <?= number_format($promedio, 1) ?>
                    </span>
                <?php else: ?>
                    <span class="score-badge low">0.0</span>
                <?php endif; ?>
            </td>
            
            <!-- Botones de Acción perfectamente alineados a la derecha -->
            <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                <div class="table-actions" style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                    <a href="index.php?page=empleados&accion=ver&legajo=<?= $emp['legajo'] ?>"
                       class="btn btn-sm btn-secondary">Ver</a>
                    
                    <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                        <a href="index.php?page=empleados&accion=editar&legajo=<?= $emp['legajo'] ?>"
                           class="btn btn-sm btn-secondary">Editar</a>
                        <a href="index.php?page=empleados&accion=eliminar&legajo=<?= $emp['legajo'] ?>"
                           class="btn btn-sm btn-danger"
                           data-confirm="¿Eliminar al empleado <?= htmlspecialchars($nombre_completo) ?>?">
                           Eliminar
                        </a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>