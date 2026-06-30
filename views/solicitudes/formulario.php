<?php
// views/solicitudes/formulario.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>➕ Nueva solicitud de licencia</h1>
</div>

<div style="margin-bottom:1rem">
    <a href="index.php?page=solicitudes" class="btn btn-secondary btn-sm">← Volver</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card form-container">
<div style="padding:1.5rem">
<form method="POST" action="index.php?page=solicitudes&accion=guardar">

    <!-- Solo RRHH/Admin pueden elegir el empleado; el empleado se carga solo -->
    <?php if (in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])): ?>
    <div class="form-group">
        <label>Empleado *</label>
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
    <?php else: ?>
        <input type="hidden" name="legajo" value="<?= $_SESSION['legajo'] ?>">
        <div class="form-group">
            <label>Empleado</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['nombre']) ?>" readonly>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Tipo de licencia *</label>
        <select name="tipo_lic_cod" id="tipo_lic_cod" class="form-control" required>
            <option value="">Seleccioná...</option>
            <?php foreach ($tipos as $t): ?>
                <option value="<?= $t['tipo_lic_cod'] ?>"
                        data-dias="<?= $t['dias_max'] ?>"
                        data-cert="<?= $t['requiere_certificado'] ?>"
                        data-rem="<?= $t['remunerada'] ?>"
                    <?= ($t['tipo_lic_cod'] == ($_POST['tipo_lic_cod'] ?? '')) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nombre']) ?>
                    (máx. <?= $t['dias_max'] ?> días<?= $t['remunerada'] ? ', remunerada' : ', sin goce' ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Info dinámica del tipo seleccionado -->
    <div id="tipo-info" style="display:none;margin-bottom:1.2rem">
        <div class="alert alert-warning" style="margin:0">
            <span id="info-cert"></span>
            <span id="info-rem"></span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
            <label>Fecha de inicio *</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                   value="<?= $_POST['fecha_inicio'] ?? '' ?>" required>
        </div>
        <div class="form-group">
            <label>Fecha de fin *</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                   value="<?= $_POST['fecha_fin'] ?? '' ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>Días calculados</label>
        <input type="text" id="dias_calc" class="form-control" value="—" readonly
               style="background:var(--bg-hover);cursor:default">
    </div>

    <div class="form-group">
        <label>Observación</label>
        <input type="text" name="observacion" class="form-control"
               placeholder="Opcional..."
               value="<?= htmlspecialchars($_POST['observacion'] ?? '') ?>">
    </div>

    <div style="display:flex;gap:.8rem;margin-top:1.5rem">
        <button type="submit" class="btn">💾 Enviar solicitud</button>
        <a href="index.php?page=solicitudes" class="btn btn-secondary">Cancelar</a>
    </div>

</form>
</div>
</div>

<script>
// Calcular días automáticamente y mostrar info del tipo
const tipoSel    = document.getElementById('tipo_lic_cod');
const fechaIni   = document.getElementById('fecha_inicio');
const fechaFin   = document.getElementById('fecha_fin');
const diasCalc   = document.getElementById('dias_calc');
const tipoInfo   = document.getElementById('tipo-info');
const infoCert   = document.getElementById('info-cert');
const infoRem    = document.getElementById('info-rem');

function calcularDias() {
    if (fechaIni.value && fechaFin.value) {
        const diff = (new Date(fechaFin.value) - new Date(fechaIni.value)) / 86400000 + 1;
        diasCalc.value = diff > 0 ? diff + ' días' : '⚠️ Fecha inválida';
    }
}

function mostrarInfoTipo() {
    const opt = tipoSel.options[tipoSel.selectedIndex];
    if (!opt.value) { tipoInfo.style.display = 'none'; return; }

    const cert = opt.dataset.cert === '1';
    const rem  = opt.dataset.rem  === '1';
    infoCert.textContent = cert ? '📎 Requiere certificado adjunto. ' : '';
    infoRem.textContent  = rem  ? '💰 Licencia remunerada.' : '🚫 Sin goce de sueldo.';
    tipoInfo.style.display = 'block';
}

tipoSel.addEventListener('change', mostrarInfoTipo);
fechaIni.addEventListener('change', calcularDias);
fechaFin.addEventListener('change', calcularDias);
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>