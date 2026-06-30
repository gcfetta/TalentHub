<?php
$es_nuevo = $departamento === null;
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1><?= $es_nuevo ? '➕ Nuevo departamento' : '✏️ Editar departamento' ?></h1>
</div>
<div style="margin-bottom:1rem">
    <a href="index.php?page=departamentos" class="btn btn-secondary btn-sm">← Volver</a>
</div>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="card form-container">
    <div style="padding:1.5rem">
    <form method="POST" action="index.php?page=departamentos&accion=guardar">
        <input type="hidden" name="es_nuevo"  value="<?= $es_nuevo ? 1 : 0 ?>">
        <input type="hidden" name="depto_cod" value="<?= $departamento['depto_cod'] ?? '' ?>">

        <div class="form-group">
            <label>Nombre del departamento *</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($departamento['nombre'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Localidad *</label>
            <select name="localidad_cod" class="form-control" required>
                <option value="">Seleccioná...</option>
                <?php foreach ($localidades as $loc): ?>
                    <option value="<?= $loc['localidad_cod'] ?>"
                        <?= ($departamento['localidad_cod'] ?? '') == $loc['localidad_cod'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['nombre']) ?> — <?= htmlspecialchars($loc['provincia']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.8rem;margin-top:1.5rem">
            <button type="submit" class="btn">💾 Guardar</button>
            <a href="index.php?page=departamentos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>