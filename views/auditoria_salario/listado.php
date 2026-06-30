<?php
// views/auditoria_salario/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>🧾 Auditoría salarial</h1>
    <span class="page-subtitle"><?= count($registros) ?> cambios de cargo registrados</span>
</div>

<div class="card">
    <div class="card-header">
        <h2>Historial de cambios (registrado automáticamente por trigger)</h2>
    </div>

    <?php if (empty($registros)): ?>
        <div class="empty-state">
            <span class="empty-icon">🧾</span>
            No hay cambios de cargo registrados todavía.
        </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Empleado</th>
                <th>Cargo anterior</th>
                <th>Cargo nuevo</th>
                <th>Fecha de cambio</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td>#<?= $r['auditoria_sal_id'] ?></td>
                <td><?= htmlspecialchars($r['empleado']) ?> (<?= $r['legajo'] ?>)</td>
                <td>
                    <?= htmlspecialchars($r['cargo_anterior']) ?>
                    <small style="color:var(--text-muted)">($<?= $r['ant_min'] ?> - $<?= $r['ant_max'] ?>)</small>
                </td>
                <td>
                    <?= htmlspecialchars($r['cargo_nuevo']) ?>
                    <small style="color:var(--text-muted)">($<?= $r['nvo_min'] ?> - $<?= $r['nvo_max'] ?>)</small>
                </td>
                <td><?= $r['fecha_cambio'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>