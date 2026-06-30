<?php
// views/evaluaciones/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>⭐ Evaluaciones de Desempeño</h1>
    <span class="page-subtitle"><?= count($evaluaciones) ?> registros</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Evaluación guardada correctamente.</div>
<?php endif; ?>

<?php if (!empty($ranking)): ?>
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header">
        <h2>🏆 Ranking de empleados por desempeño</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Departamento</th>
                <th>Evaluaciones</th>
                <th>Promedio</th>
                <th>Máx</th>
                <th>Mín</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ranking as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($r['departamento']) ?></td>
                <td><?= $r['total_evaluaciones'] ?></td>
                <td><?= $r['promedio_puntaje'] ?? '—' ?></td>
                <td><?= $r['puntaje_maximo'] ?? '—' ?></td>
                <td><?= $r['puntaje_minimo'] ?? '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Historial de evaluaciones</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])): ?>
            <a href="index.php?page=evaluaciones&accion=nueva" class="btn">+ Nueva evaluación</a>
        <?php endif; ?>
    </div>

    <?php if (empty($evaluaciones)): ?>
        <div class="empty-state">
            <span class="empty-icon">⭐</span>
            No hay evaluaciones registradas.
        </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Empleado</th>
                <th>Cargo actual</th>
                <th>Período</th>
                <th>Puntaje</th>
                <th>Evaluador</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($evaluaciones as $ev):
            $puntaje = (float)$ev['puntaje'];
            $badge   = match(true) {
                $puntaje >= 9  => 'badge-success',
                $puntaje >= 7  => 'badge-info',
                $puntaje >= 5  => 'badge-warning',
                default        => 'badge-danger',
            };
        ?>
            <tr>
                <td><strong>#<?= $ev['evaluacion_id'] ?></strong></td>
                <td><?= htmlspecialchars($ev['apellido'] . ', ' . $ev['nombre']) ?></td>
                <td><?= htmlspecialchars($ev['cargo'] ?? '—') ?></td>
                <td><?= htmlspecialchars($ev['periodo']) ?></td>
                <td><span class="badge <?= $badge ?>"><?= number_format($puntaje, 2) ?></span></td>
                <td><?= htmlspecialchars($ev['eval_apellido'] . ', ' . $ev['eval_nombre']) ?></td>
                <td><?= $ev['fecha_evaluacion'] ?></td>
                <td>
                    <a href="index.php?page=evaluaciones&accion=ver&id=<?= $ev['evaluacion_id'] ?>"
                       class="btn btn-sm btn-secondary">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>