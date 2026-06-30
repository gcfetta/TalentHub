<?php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1>🏬 Departamentos</h1>
    <span class="page-subtitle"><?= count($departamentos) ?> registros</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><?= $_GET['ok']==1 ? 'Departamento guardado.' : 'Departamento eliminado.' ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= $_GET['error']==='tiene_empleados' ? 'No se puede eliminar: tiene empleados asignados.' : 'No se puede eliminar: está referenciado.' ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Estructura de departamentos</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
            <a href="index.php?page=departamentos&accion=nuevo" class="btn">+ Nuevo departamento</a>
        <?php endif; ?>
    </div>
    <?php if (empty($departamentos)): ?>
        <div class="empty-state"><span class="empty-icon">🏬</span> No hay departamentos registrados.</div>
    <?php else: ?>
    <table class="table">
        <thead><tr>
            <th>#</th><th>Departamento</th><th>Localidad</th><th>Provincia</th><th>Empleados</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        <?php foreach ($departamentos as $d): ?>
            <tr>
                <td><?= $d['depto_cod'] ?></td>
                <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($d['localidad']) ?></td>
                <td><?= htmlspecialchars($d['provincia']) ?></td>
                <td><?= $d['empleados'] ?></td>
                <td>
                    <div class="table-actions">
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=departamentos&accion=editar&id=<?= $d['depto_cod'] ?>" class="btn btn-sm">Editar</a>
                            <?php if ($d['empleados'] == 0): ?>
                                <a href="index.php?page=departamentos&accion=eliminar&id=<?= $d['depto_cod'] ?>"
                                   class="btn btn-sm btn-danger"
                                   data-confirm="¿Eliminar «<?= htmlspecialchars($d['nombre']) ?>»?">Eliminar</a>
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