<?php
// views/solicitudes/formulario.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>➕ Nueva solicitud de licencia</h1>
</div>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php?page=solicitudes" class="btn-back" style="margin: 0;">← Volver</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-form-wrapper">
    <form method="POST" action="index.php?page=solicitudes&accion=guardar" class="form-container">

        <div class="form-grid-2">
            <?php if (in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor'])): ?>
            <div class="form-group" style="position: relative;">
                <label>Empleado <span>*</span></label>
                
                <input type="text" id="busqueda-empleado" class="form-control" autocomplete="off"
                       placeholder="Escribí legajo, nombre o apellido..." 
                       value="<?php
                            if (isset($_POST['legajo'])) {
                                foreach ($empleados as $e) {
                                    if ($e['legajo'] == $_POST['legajo']) {
                                        echo htmlspecialchars($e['nombre_completo']);
                                        break;
                                    }
                                }
                            }
                       ?>">
                
                <input type="hidden" name="legajo" id="legajo-seleccionado" 
                       value="<?= htmlspecialchars($_POST['legajo'] ?? '') ?>" required>
                
                <div id="lista-desplegable-empleados" 
                     style="position: absolute; top: 100%; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 2px solid var(--accent); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 999; display: none; margin-top: 0.25rem;">
                    <?php foreach ($empleados as $emp): ?>
                        <div class="opcion-empleado" 
                             style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.9rem; background-color: #ffffff; color: #1e293b;"
                             data-legajo="<?= htmlspecialchars($emp['legajo']) ?>" 
                             data-busqueda="<?= htmlspecialchars(mb_strtolower($emp['nombre_completo'] . ' ' . $emp['legajo'])) ?>">
                            <strong><?= htmlspecialchars($emp['nombre_completo']) ?></strong> 
                            <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 0.5rem;">#<?= htmlspecialchars($emp['legajo']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="legajo" value="<?= $_SESSION['legajo'] ?>">
                <div class="form-group">
                    <label>Empleado</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['nombre']) ?>" readonly>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Tipo de licencia <span>*</span></label>
                <select name="tipo_lic_cod" id="tipo_lic_cod" class="form-control" required>
                    <option value="">Seleccioná...</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t['tipo_lic_cod'] ?>"
                                data-dias="<?= $t['dias_max'] ?>"
                                data-cert="<?= $t['requiere_certificado'] ?>"
                                data-rem="<?= $t['remunerada'] ?>"
                            <?= ($t['tipo_lic_cod'] == ($_POST['tipo_lic_cod'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombre']) ?> (máx. <?= $t['dias_max'] ?> días)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="tipo-info" style="display:none; margin-bottom: 1.5rem;">
            <div class="alert alert-warning" style="margin:0; border-radius: 12px; font-weight: 600;">
                <span id="info-cert"></span>
                <span id="info-rem"></span>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Fecha de inicio <span>*</span></label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                       value="<?= $_POST['fecha_inicio'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha de fin <span>*</span></label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                       value="<?= $_POST['fecha_fin'] ?? '' ?>" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Días calculados</label>
                <input type="text" id="dias_calc" class="form-control" value="—" readonly
                       style="background:var(--bg-hover); cursor:default; font-weight: 700; color: var(--accent);">
            </div>
            <div class="form-group">
                <label>Observación</label>
                <input type="text" name="observacion" class="form-control"
                       placeholder="Opcional..."
                       value="<?= htmlspecialchars($_POST['observacion'] ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">💾 Enviar solicitud</button>
            <a href="index.php?page=solicitudes" class="btn-cancel">Cancelar</a>
        </div>

    </form>
</div>

<script>
// --- Buscador predictivo de Empleados (Para Admin/RRHH/Supervisores) ---
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('busqueda-empleado');
    const hiddenLegajo = document.getElementById('legajo-seleccionado');
    const listaDesplegable = document.getElementById('lista-desplegable-empleados');
    const opciones = document.querySelectorAll('.opcion-empleado');

    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function() {
            const query = inputBusqueda.value.trim().toLowerCase();
            let visibles = 0;

            if (query.length === 0) {
                listaDesplegable.style.display = 'none';
                hiddenLegajo.value = '';
                return;
            }

            opciones.forEach(opcion => {
                const contextoBusqueda = opcion.getAttribute('data-busqueda');
                if (contextoBusqueda.includes(query)) {
                    opcion.style.display = 'block';
                    visibles++;
                } else {
                    opcion.style.display = 'none';
                }
            });

            listaDesplegable.style.display = visibles > 0 ? 'block' : 'none';
        });

        inputBusqueda.addEventListener('click', function() {
            if (inputBusqueda.value.trim() === '') {
                opciones.forEach(opcion => opcion.style.display = 'block');
                listaDesplegable.style.display = 'block';
            }
        });

        opciones.forEach(opcion => {
            opcion.addEventListener('click', function() {
                inputBusqueda.value = opcion.querySelector('strong').textContent;
                hiddenLegajo.value = opcion.getAttribute('data-legajo');
                listaDesplegable.style.display = 'none';
            });

            opcion.addEventListener('mouseenter', () => opcion.style.background = '#f1f5f9');
            opcion.addEventListener('mouseleave', () => opcion.style.background = '#ffffff');
        });

        document.addEventListener('click', function(e) {
            if (!inputBusqueda.contains(e.target) && !listaDesplegable.contains(e.target)) {
                listaDesplegable.style.display = 'none';
            }
        });
    }
});

// --- Lógica de cálculo de fechas e info de licencia ---
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

// Ejecutar por si vuelve con datos de validación fallida de POST
if(tipoSel.value) mostrarInfoTipo();
if(fechaIni.value && fechaFin.value) calcularDias();
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>