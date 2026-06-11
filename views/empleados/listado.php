<?php
// views/empleados/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>👥 Empleados</h1>
    <span class="page-subtitle"><?= count($empleados) ?> registros</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Empleado guardado correctamente.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Nómina</h2>
        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
            <a href="index.php?page=empleados&accion=nuevo" class="btn">+ Nuevo empleado</a>
        <?php endif; ?>
    </div>

    <?php if (empty($empleados)): ?>
        <div class="empty-state">
            <span class="empty-icon">👥</span>
            No hay empleados registrados.
        </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Legajo</th>
                <th>Apellido y nombre</th>
                <th>Cargo actual</th>
                <th>Departamento</th>
                <th>Localidad</th>
                <th>Ingreso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empleados as $emp): ?>
            <tr>
                <td><strong><?= $emp['legajo'] ?></strong></td>
                <td><?= htmlspecialchars($emp['apellido'] . ', ' . $emp['nombre']) ?></td>
                <td><?= htmlspecialchars($emp['cargo_actual'] ?? '—') ?></td>
                <td><?= htmlspecialchars($emp['departamento']) ?></td>
                <td><?= htmlspecialchars($emp['localidad']) ?></td>
                <td><?= $emp['fecha_ingreso'] ?></td>
                <td>
                    <div class="table-actions">
                        <a href="index.php?page=empleados&accion=ver&legajo=<?= $emp['legajo'] ?>"
                           class="btn btn-sm btn-secondary">Ver</a>
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=empleados&accion=editar&legajo=<?= $emp['legajo'] ?>"
                               class="btn btn-sm">Editar</a>
                            <a href="index.php?page=empleados&accion=eliminar&legajo=<?= $emp['legajo'] ?>"
                               class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar al empleado <?= htmlspecialchars($emp['nombre']) ?>? Esta acción no se puede deshacer.">
                               Eliminar</a>
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