<?php
$es_nuevo = $cargo === null;
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="page-header">
    <h1><?= $es_nuevo ? '➕ Nuevo cargo' : '✏️ Editar cargo' ?></h1>
</div>
<div style="margin-bottom:1rem">
    <a href="index.php?page=cargos" class="btn btn-secondary btn-sm">← Volver</a>
</div>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="card form-container">
    <div style="padding:1.5rem">
    <form method="POST" action="index.php?page=cargos&accion=guardar">
        <input type="hidden" name="es_nuevo"  value="<?= $es_nuevo ? 1 : 0 ?>">
        <input type="hidden" name="cargo_cod" value="<?= $cargo['cargo_cod'] ?? '' ?>">

        <div class="form-group">
            <label>Nombre del cargo *</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($cargo['nombre'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Nivel jerárquico *</label>
            <select name="nivel_cod" class="form-control" required>
                <option value="">Seleccioná...</option>
                <?php foreach ($niveles as $nj): ?>
                    <option value="<?= $nj['nivel_cod'] ?>"
                        <?= ($cargo['nivel_cod'] ?? '') == $nj['nivel_cod'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nj['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
                <label>Banda salarial mínima *</label>
                <input type="number" name="banda_min" class="form-control" step="0.01" min="0"
                       value="<?= $cargo['banda_salarial_min'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Banda salarial máxima *</label>
                <input type="number" name="banda_max" class="form-control" step="0.01" min="0"
                       value="<?= $cargo['banda_salarial_max'] ?? '' ?>" required>
            </div>
        </div>
        <div style="display:flex;gap:.8rem;margin-top:1.5rem">
            <button type="submit" class="btn">💾 Guardar</button>
            <a href="index.php?page=cargos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>