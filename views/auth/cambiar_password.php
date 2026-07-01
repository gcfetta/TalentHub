<?php
// views/auth/cambiar_password.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>🔑 Cambiar contraseña</h1>
    <span class="page-subtitle">Es tu primer ingreso, elegí una nueva contraseña</span>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card form-container">
    <div style="padding:1.5rem">
    <form method="POST" action="index.php?page=cambiar_password">
        <div class="form-group">
            <label>Contraseña actual (tu legajo)</label>
            <input type="password" name="actual" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="nueva" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
            <label>Repetir nueva contraseña</label>
            <input type="password" name="repetir" class="form-control" required minlength="6">
        </div>
        <div style="display:flex;gap:.8rem;margin-top:1.5rem">
            <button type="submit" class="btn">💾 Guardar</button>
        </div>
    </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>