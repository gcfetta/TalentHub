<?php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1>🏷️ Cargos</h1>
    <span class="page-subtitle"><?= count($cargos) ?> registros</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><?= $_GET['ok']==1 ? 'Cargo guardado.' : 'Cargo eliminado.' ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= $_GET['error']==='tiene_empleados' ? 'No se puede eliminar: tiene empleados activos asignados.' : 'No se puede eliminar: está referenciado en el historial.' ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Estructura de cargos</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
            <a href="index.php?page=cargos&accion=nuevo" class="btn">+ Nuevo cargo</a>
        <?php endif; ?>
    </div>
    <?php if (empty($cargos)): ?>
        <div class="empty-state"><span class="empty-icon">🏷️</span> No hay cargos registrados.</div>
    <?php else: ?>
    <table class="table">
        <thead><tr>
            <th>#</th><th>Cargo</th><th>Nivel</th><th>Banda mín.</th><th>Banda máx.</th><th>Empleados activos</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        <?php foreach ($cargos as $c): ?>
            <tr>
                <td><?= $c['cargo_cod'] ?></td>
                <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($c['nivel']) ?></td>
                <td>$ <?= number_format($c['banda_salarial_min'], 2, ',', '.') ?></td>
                <td>$ <?= number_format($c['banda_salarial_max'], 2, ',', '.') ?></td>
                <td><?= $c['empleados_activos'] ?></td>
                <td>
                    <div class="table-actions">
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=cargos&accion=editar&id=<?= $c['cargo_cod'] ?>" class="btn btn-sm">Editar</a>
                            <?php if ($c['empleados_activos'] == 0): ?>
                                <a href="index.php?page=cargos&accion=eliminar&id=<?= $c['cargo_cod'] ?>"
                                   class="btn btn-sm btn-danger"
                                   data-confirm="¿Eliminar el cargo «<?= htmlspecialchars($c['nombre']) ?>»?">Eliminar</a>
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