<?php
// views/solicitudes/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>📋 Licencias</h1>
    <span class="page-subtitle"><?= count($solicitudes) ?> solicitudes</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Operación realizada correctamente.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Solicitudes de licencia</h2>
        <a href="index.php?page=solicitudes&accion=nueva" class="btn">+ Nueva solicitud</a>
    </div>

    <?php if (empty($solicitudes)): ?>
        <div class="empty-state">
            <span class="empty-icon">📋</span>
            No hay solicitudes registradas.
        </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Empleado</th>
                <th>Tipo de licencia</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Días</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($solicitudes as $s):
            $badge = match($s['estado']) {
                'Aprobada'  => 'badge-success',
                'Rechazada' => 'badge-danger',
                default     => 'badge-warning',
            };
        ?>
            <tr>
                <td><strong>#<?= $s['nro_solicitud'] ?></strong></td>
                <td><?= htmlspecialchars($s['apellido'] . ', ' . $s['nombre']) ?></td>
                <td><?= htmlspecialchars($s['tipo_licencia']) ?></td>
                <td><?= $s['fecha_inicio'] ?></td>
                <td><?= $s['fecha_fin'] ?></td>
                <td><?= $s['dias_solicitados'] ?></td>
                <td><span class="badge <?= $badge ?>"><?= $s['estado'] ?></span></td>
                <td>
                    <a href="index.php?page=solicitudes&accion=ver&nro=<?= $s['nro_solicitud'] ?>"
                       class="btn btn-sm btn-secondary">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>