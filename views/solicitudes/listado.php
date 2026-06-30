<?php
// views/solicitudes/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>📋 Licencias</h1>
    <span class="page-subtitle"><?= count($solicitudes) ?> solicitudes</span>
</div>

<?php
$estado_actual_filtro = $_GET['estado'] ?? '';
$busqueda_actual      = $_GET['q'] ?? '';
$filtros = ['' => 'Todas', 'Pendiente' => 'Pendientes', 'Aprobada' => 'Aprobadas', 'Rechazada' => 'Rechazadas'];
?>
<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.8rem; margin-bottom:1rem">
    <div style="display:flex; gap:.6rem">
        <?php foreach ($filtros as $valor => $etiqueta): ?>
            <a href="index.php?page=solicitudes<?= $valor ? "&estado={$valor}" : '' ?><?= $busqueda_actual ? '&q=' . urlencode($busqueda_actual) : '' ?>"
               class="btn btn-sm <?= $estado_actual_filtro === $valor ? '' : 'btn-secondary' ?>">
                <?= $etiqueta ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="index.php" style="display:flex; gap:.4rem">
        <input type="hidden" name="page" value="solicitudes">
        <?php if ($estado_actual_filtro): ?>
            <input type="hidden" name="estado" value="<?= htmlspecialchars($estado_actual_filtro) ?>">
        <?php endif; ?>
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o legajo..."
               value="<?= htmlspecialchars($busqueda_actual) ?>" style="min-width:220px">
        <button type="submit" class="btn btn-sm">🔍 Buscar</button>
        <?php if ($busqueda_actual): ?>
            <a href="index.php?page=solicitudes<?= $estado_actual_filtro ? "&estado={$estado_actual_filtro}" : '' ?>"
               class="btn btn-sm btn-secondary">✕</a>
        <?php endif; ?>
    </form>
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
                <td><?= htmlspecialchars($s['empleado']) ?></td>
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