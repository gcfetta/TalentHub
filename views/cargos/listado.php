<?php
// views/cargos/index.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1>🏷️ Cargos</h1>
    <span class="page-subtitle"><?= count($cargos) ?> registros en total</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><?= $_GET['ok'] == 1 ? 'Cargo guardado correctamente.' : 'Cargo eliminado con éxito.' ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= $_GET['error'] === 'tiene_empleados' ? 'No se puede eliminar: tiene empleados activos asignados.' : 'No se puede eliminar: está referenciado en el historial laboral.' ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Estructura Jerárquica</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
            <a href="index.php?page=cargos&accion=nuevo" class="btn">+ Nuevo cargo</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($cargos)): ?>
        <div class="empty-state"><span class="empty-icon">🏷️</span> No hay cargos registrados en el sistema.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre del Cargo</th>
                <th>Nivel</th>
                <th>Banda Mín.</th>
                <th>Banda Máx.</th>
                <th style="text-align: center;">Empleados Activos</th>
                <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cargos as $c): ?>
            <tr>
                <td style="color: var(--text-muted); font-weight: 600;">#<?= $c['cargo_cod'] ?></td>
                <td><strong style="color: var(--text-primary); font-size: 0.92rem;"><?= htmlspecialchars($c['nombre']) ?></strong></td>
                <td><span class="badge badge-info" style="font-weight: 600;"><?= htmlspecialchars($c['nivel']) ?></span></td>
                <td style="font-weight: 500; color: #475569;">$ <?= number_format($c['banda_salarial_min'], 0, ',', '.') ?></td>
                <td style="font-weight: 500; color: #475569;">$ <?= number_format($c['banda_salarial_max'], 0, ',', '.') ?></td>
                <td style="text-align: center;">
                    <?php if ($c['empleados_activos'] > 0): ?>
                        <span class="badge badge-success" style="background: rgba(16, 185, 129, 0.1); color: var(--success); font-weight: 700;">
                            <?= $c['empleados_activos'] ?> activos
                        </span>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.88rem;">0</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                    <div class="table-actions" style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=cargos&accion=editar&id=<?= $c['cargo_cod'] ?>" class="btn btn-sm btn-secondary">Editar</a>
                            <?php if ($c['empleados_activos'] == 0): ?>
                                <a href="index.php?page=cargos&accion=eliminar&id=<?= $c['cargo_cod'] ?>"
                                   class="btn btn-sm btn-danger"
                                   data-confirm="¿Seguro que deseas eliminar el cargo «<?= htmlspecialchars($c['nombre']) ?>»?">Eliminar</a>
                            <?php endif; ?>
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