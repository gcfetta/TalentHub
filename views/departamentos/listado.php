<?php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1>🏬 Departamentos</h1>
    <span class="page-subtitle"><?= count($departamentos) ?> registros en total</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><?= $_GET['ok']==1 ? 'Departamento guardado correctamente.' : 'Departamento eliminado con éxito.' ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= $_GET['error']==='tiene_empleados' ? 'No se puede eliminar: tiene empleados asignados.' : 'No se puede eliminar: está referenciado en el sistema.' ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Estructura de departamentos</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
            <a href="index.php?page=departamentos&accion=nuevo" class="btn">+ Nuevo departamento</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($departamentos)): ?>
        <div class="empty-state"><span class="empty-icon">🏬</span> No hay departamentos registrados.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Departamento</th>
                <th>Localidad</th>
                <th>Provincia</th>
                <th style="text-align: center;">Empleados</th>
                <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($departamentos as $d): ?>
            <tr>
                <td style="color: var(--text-muted); font-weight: 600;">#<?= $d['depto_cod'] ?></td>
                <td><strong style="color: var(--text-primary); font-size: 0.92rem;"><?= htmlspecialchars($d['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($d['localidad']) ?></td>
                <td><?= htmlspecialchars($d['provincia']) ?></td>
                <td style="text-align: center;">
                    <?php if ($d['empleados'] > 0): ?>
                        <span class="badge badge-success" style="background: rgba(16, 185, 129, 0.1); color: var(--success); font-weight: 700;">
                            <?= $d['empleados'] ?> asignados
                        </span>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.88rem;">0</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                    <div class="table-actions" style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=departamentos&accion=editar&id=<?= $d['depto_cod'] ?>" class="btn btn-sm btn-secondary">Editar</a>
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