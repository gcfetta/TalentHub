<?php
// views/evaluaciones/formulario.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>➕ Nueva evaluación de desempeño</h1>
</div>

<div style="margin-bottom:1rem">
    <a href="index.php?page=evaluaciones" class="btn btn-secondary btn-sm">← Volver</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card form-container">
<div style="padding:1.5rem">
<form method="POST" action="index.php?page=evaluaciones&accion=guardar">

    <div class="form-group">
        <label>Empleado evaluado *</label>
        <select name="legajo" class="form-control" required>
            <option value="">Seleccioná...</option>
            <?php foreach ($empleados as $emp): ?>
                <option value="<?= $emp['legajo'] ?>"
                    <?= $emp['legajo'] == ($_POST['legajo'] ?? '') ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nombre_completo']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
            <label>Período * <small style="color:var(--text-muted)">(ej: 2024-S1, 2024-Anual)</small></label>
            <input type="text" name="periodo" class="form-control"
                   placeholder="2024-S1"
                   value="<?= htmlspecialchars($_POST['periodo'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Fecha de evaluación *</label>
            <input type="date" name="fecha_evaluacion" class="form-control"
                   value="<?= $_POST['fecha_evaluacion'] ?? date('Y-m-d') ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>Puntaje * <small style="color:var(--text-muted)">(0.00 — 10.00)</small></label>
        <input type="number" name="puntaje" id="puntaje" class="form-control"
               min="0" max="10" step="0.01" placeholder="8.50"
               value="<?= htmlspecialchars($_POST['puntaje'] ?? '') ?>" required>
        <!-- Barra visual del puntaje -->
        <div style="margin-top:0.6rem;height:6px;background:var(--bg-hover);border-radius:999px;overflow:hidden">
            <div id="barra-puntaje" style="height:100%;width:0%;border-radius:999px;transition:width .3s,background .3s"></div>
        </div>
        <div id="label-puntaje" style="font-size:0.78rem;color:var(--text-muted);margin-top:0.3rem"></div>
    </div>

    <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="4"
                  placeholder="Comentarios sobre el desempeño del empleado..."
                  style="resize:vertical"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
    </div>

    <div style="display:flex;gap:.8rem;margin-top:1.5rem">
        <button type="submit" class="btn">💾 Guardar evaluación</button>
        <a href="index.php?page=evaluaciones" class="btn btn-secondary">Cancelar</a>
    </div>

</form>
</div>
</div>

<script>
// Barra visual del puntaje
const puntajeInput  = document.getElementById('puntaje');
const barraPuntaje  = document.getElementById('barra-puntaje');
const labelPuntaje  = document.getElementById('label-puntaje');

const NIVELES = [
    { min: 9,   color: '#22c55e', label: '⭐ Sobresaliente' },
    { min: 7,   color: '#38bdf8', label: '✅ Bueno' },
    { min: 5,   color: '#f59e0b', label: '⚠️ Regular' },
    { min: 0,   color: '#ef4444', label: '❌ Insuficiente' },
];

function actualizarBarra() {
    const val = parseFloat(puntajeInput.value);
    if (isNaN(val) || val < 0 || val > 10) {
        barraPuntaje.style.width = '0%';
        labelPuntaje.textContent = '';
        return;
    }
    const nivel = NIVELES.find(n => val >= n.min);
    barraPuntaje.style.width      = (val * 10) + '%';
    barraPuntaje.style.background = nivel.color;
    labelPuntaje.textContent      = nivel.label;
    labelPuntaje.style.color      = nivel.color;
}

puntajeInput.addEventListener('input', actualizarBarra);
actualizarBarra(); // por si hay valor previo (error de formulario)
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>