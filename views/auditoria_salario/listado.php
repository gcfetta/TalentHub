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
        <h2>Historial de cambios <span class="badge badge-info" style="margin-left: 0.5rem; font-size: 0.7rem;">Trigger automático</span></h2>
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
                <th>Cargo anterior & Banda</th>
                <th>Cargo nuevo & Banda</th>
                <th style="text-align: right; padding-right: 1.5rem;">Fecha de cambio</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td style="color: var(--text-muted); font-weight: 600;">#<?= $r['auditoria_sal_id'] ?></td>
                
                <td>
                    <div class="employee-meta">
                        <strong style="color: var(--text-primary); font-size: 0.92rem;"><?= htmlspecialchars($r['empleado']) ?></strong>
                        <span class="employee-id">#<?= htmlspecialchars($r['legajo']) ?></span>
                    </div>
                </td>
                
                <td>
                    <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                        <span class="badge badge-secondary" style="align-self: flex-start; background: #e2e8f0; color: #475569; font-weight: 600;">
                            <?= htmlspecialchars($r['cargo_anterior']) ?>
                        </span>
                        <small style="color: var(--text-muted); font-weight: 500; font-size: 0.78rem;">
                            $<?= number_format($r['ant_min'], 0, ',', '.') ?> - $<?= number_format($r['ant_max'], 0, ',', '.') ?>
                        </small>
                    </div>
                </td>
                
                <td>
                    <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                        <span class="badge badge-success" style="align-self: flex-start; background: rgba(16, 185, 129, 0.1); color: var(--success); font-weight: 700;">
                            <?= htmlspecialchars($r['cargo_nuevo']) ?>
                        </span>
                        <small style="color: var(--text-muted); font-weight: 500; font-size: 0.78rem;">
                            $<?= number_format($r['nvo_min'], 0, ',', '.') ?> - $<?= number_format($r['nvo_max'], 0, ',', '.') ?>
                        </small>
                    </div>
                </td>
                
                <td style="text-align: right; padding-right: 1.5rem; color: var(--text-muted); font-weight: 500;">
                    <?= $r['fecha_change'] ?? $r['fecha_cambio'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>