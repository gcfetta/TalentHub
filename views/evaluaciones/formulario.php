<?php
// views/evaluaciones/formulario.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>➕ Nueva evaluación de desempeño</h1>
</div>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php?page=evaluaciones" class="btn-back" style="margin: 0;">← Volver</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-form-wrapper">
    <form method="POST" action="index.php?page=evaluaciones&accion=guardar" class="form-container">

        <div class="form-grid-2">
            <div class="form-group" style="position: relative;">
                <label>Empleado evaluado <span>*</span></label>
                
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
            <div class="form-group">
                </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Período <span>*</span> <small style="color:var(--text-muted)">(ej: 2024-S1, 2024-Anual)</small></label>
                <input type="text" name="periodo" class="form-control"
                       placeholder="2024-S1"
                       value="<?= htmlspecialchars($_POST['periodo'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha de evaluación <span>*</span></label>
                <input type="date" name="fecha_evaluacion" class="form-control"
                       value="<?= $_POST['fecha_evaluacion'] ?? date('Y-m-d') ?>" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Puntaje <span>*</span> <small style="color:var(--text-muted)">(0.00 — 10.00)</small></label>
                <input type="number" name="puntaje" id="puntaje" class="form-control"
                       min="0" max="10" step="0.01" placeholder="8.50"
                       value="<?= htmlspecialchars($_POST['puntaje'] ?? '') ?>" required>
                <div style="margin-top:0.6rem;height:6px;background:var(--bg-hover);border-radius:999px;overflow:hidden">
                    <div id="barra-puntaje" style="height:100%;width:0%;border-radius:999px;transition:width .3s,background .3s"></div>
                </div>
                <div id="label-puntaje" style="font-size:0.78rem;color:var(--text-muted);margin-top:0.3rem;font-weight:700"></div>
            </div>
            <div class="form-group">
                </div>
        </div>

        <div class="form-group">
            <label>Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="4"
                      placeholder="Comentarios sobre el desempeño del empleado..."
                      style="resize:vertical"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">💾 Guardar evaluación</button>
            <a href="index.php?page=evaluaciones" class="btn-cancel">Cancelar</a>
        </div>

    </form>
</div>

<script>
// --- Lógica del Buscador Predictivo de Empleados ---
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('busqueda-empleado');
    const hiddenLegajo = document.getElementById('legajo-seleccionado');
    const listaDesplegable = document.getElementById('lista-desplegable-empleados');
    const opciones = document.querySelectorAll('.opcion-empleado');

    if (!inputBusqueda) return;

    // Abrir y filtrar la lista al tipear
    inputBusqueda.addEventListener('input', function() {
        const query = inputBusqueda.value.trim().toLowerCase();
        let visibles = 0;

        if (query.length === 0) {
            listaDesplegable.style.display = 'none';
            hiddenLegajo.value = ''; // Resetea el valor si borra
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

    // Mostrar lista completa al hacer click en el campo vacío
    inputBusqueda.addEventListener('click', function() {
        if (inputBusqueda.value.trim() === '') {
            opciones.forEach(opcion => opcion.style.display = 'block');
            listaDesplegable.style.display = 'block';
        }
    });

    // Seleccionar una opción de la lista
    opciones.forEach(opcion => {
        opcion.addEventListener('click', function() {
            const legajo = opcion.getAttribute('data-legajo');
            const nombreCompleto = opcion.querySelector('strong').textContent;

            inputBusqueda.value = nombreCompleto;
            hiddenLegajo.value = legajo;
            listaDesplegable.style.display = 'none';
        });

        // Efecto hover sutil en las opciones
        opcion.addEventListener('mouseenter', () => opcion.style.background = '#f1f5f9');
        opcion.addEventListener('mouseleave', () => opcion.style.background = '#fff');
    });

    // Cerrar la lista si se hace click fuera del buscador
    document.addEventListener('click', function(e) {
        if (!inputBusqueda.contains(e.target) && !listaDesplegable.contains(e.target)) {
            listaDesplegable.style.display = 'none';
        }
    });
});

// --- Lógica de la Barra Visual de Puntaje ---
const puntajeInput  = document.getElementById('puntaje');
const barraPuntaje  = document.getElementById('barra-puntaje');
const labelPuntaje  = document.getElementById('label-puntaje');

const NIVELES = [
    { min: 8.5, color: '#10b981', label: '⭐ Sobresaliente' },
    { min: 7.0, color: '#3b82f6', label: '✅ Bueno' },
    { min: 5.0, color: '#f59e0b', label: '⚠️ Regular' },
    { min: 0.0, color: '#ef4444', label: '❌ Insuficiente' },
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
actualizarBarra();
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>