<?php
// views/empleados/formulario.php
$es_nuevo = $empleado === null;
$titulo   = $es_nuevo ? 'Nuevo empleado' : 'Editar empleado';
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1><?= $es_nuevo ? '➕' : '✏️' ?> <?= $titulo ?></h1>
</div>

<div style="margin-bottom: 1.5rem;">
    <a href="index.php?page=empleados" class="btn-back" style="margin: 0;">← Volver</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-form-wrapper">
    <form method="POST" action="index.php?page=empleados&accion=guardar" class="form-container">
        <input type="hidden" name="es_nuevo" value="<?= $es_nuevo ? 1 : 0 ?>">

        <div class="form-grid-2">
            <div class="form-group">
                <label>Legajo <span>*</span></label>
                <input type="number" name="legajo" class="form-control"
                       value="<?= htmlspecialchars($empleado['legajo'] ?? '') ?>"
                       <?= $es_nuevo ? '' : 'readonly' ?> required>
            </div>
            <div class="form-group">
                </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Nombre <span>*</span></label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($empleado['nombre'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Apellido <span>*</span></label>
                <input type="text" name="apellido" class="form-control"
                       value="<?= htmlspecialchars($empleado['apellido'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Mail <span>*</span></label>
                <input type="email" name="mail" class="form-control"
                       value="<?= htmlspecialchars($empleado['mail'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha de ingreso <span>*</span></label>
                <input type="date" name="fecha_ingreso" class="form-control"
                       value="<?= htmlspecialchars($empleado['fecha_ingreso'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control"
                       value="<?= htmlspecialchars($empleado['telefono'] ?? '') ?>">
            </div>
            
            <div class="form-group" style="position: relative;">
                <label>Localidad <span>*</span></label>
                <input type="text" id="busqueda-localidad" class="form-control" autocomplete="off"
                       placeholder="Escribí para buscar localidad..."
                       value="<?php
                            if (isset($empleado['localidad_cod'])) {
                                foreach ($localidades as $loc) {
                                    if ($loc['localidad_cod'] == $empleado['localidad_cod']) {
                                        echo htmlspecialchars($loc['nombre'] . ' — ' . $loc['provincia']);
                                        break;
                                    }
                                }
                            }
                       ?>">
                <input type="hidden" name="localidad_cod" id="localidad-seleccionada" 
                       value="<?= htmlspecialchars($empleado['localidad_cod'] ?? '') ?>" required>
                
                <div id="lista-desplegable-localidades" 
                     style="position: absolute; top: 100%; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 2px solid var(--accent); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 999; display: none; margin-top: 0.25rem;">
                    <?php foreach ($localidades as $loc): ?>
                        <div class="opcion-localidad" 
                             style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.9rem; background-color: #ffffff; color: #1e293b;"
                             data-cod="<?= htmlspecialchars($loc['localidad_cod']) ?>" 
                             data-busqueda="<?= htmlspecialchars(mb_strtolower($loc['nombre'] . ' ' . $loc['provincia'])) ?>">
                            <strong><?= htmlspecialchars($loc['nombre']) ?></strong> — <span style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($loc['provincia']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group" style="position: relative;">
                <label>Departamento <span>*</span></label>
                <input type="text" id="busqueda-departamento" class="form-control" autocomplete="off"
                       placeholder="Escribí para buscar departamento..."
                       value="<?php
                            if (isset($empleado['depto_cod'])) {
                                foreach ($departamentos as $d) {
                                    if ($d['depto_cod'] == $empleado['depto_cod']) {
                                        echo htmlspecialchars($d['nombre']);
                                        break;
                                    }
                                }
                            }
                       ?>">
                <input type="hidden" name="depto_cod" id="depto-seleccionado" 
                       value="<?= htmlspecialchars($empleado['depto_cod'] ?? '') ?>" required>
                
                <div id="lista-desplegable-departamentos" 
                     style="position: absolute; top: 100%; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 2px solid var(--accent); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 998; display: none; margin-top: 0.25rem;">
                    <?php foreach ($departamentos as $d): ?>
                        <div class="opcion-depto" 
                             style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.9rem; background-color: #ffffff; color: #1e293b;"
                             data-cod="<?= htmlspecialchars($d['depto_cod']) ?>" 
                             data-busqueda="<?= htmlspecialchars(mb_strtolower($d['nombre'])) ?>">
                            <strong><?= htmlspecialchars($d['nombre']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Supervisor</label>
                <?php
                    $sup_actual_texto = '';
                    foreach ($supervisores as $sup) {
                        if (($empleado['supervisor_legajo'] ?? null) == $sup['legajo']) {
                            $sup_actual_texto = $sup['nombre_completo'] . ' (' . $sup['legajo'] . ')';
                            break;
                        }
                    }
                ?>
                <input type="text" id="supervisor_buscar" class="form-control"
                    list="supervisores-datalist" autocomplete="off"
                    placeholder="Buscar supervisor por nombre..."
                    value="<?= htmlspecialchars($sup_actual_texto) ?>">
                <datalist id="supervisores-datalist">
                    <?php foreach ($supervisores as $sup): ?>
                        <?php if ($sup['legajo'] == ($empleado['legajo'] ?? -1)) continue; ?>
                        <option value="<?= htmlspecialchars($sup['nombre_completo']) ?> (<?= $sup['legajo'] ?>)">
                    <?php endforeach; ?>
                </datalist>
                <input type="hidden" name="supervisor_legajo" id="supervisor_legajo"
                    value="<?= htmlspecialchars($empleado['supervisor_legajo'] ?? '') ?>">
            </div>
        </div>

        <?php if ($es_nuevo): ?>
        <div class="form-grid-2">
            <div class="form-group" style="position: relative;">
                <label>Cargo inicial</label>
                <input type="text" id="busqueda-cargo" class="form-control" autocomplete="off"
                       placeholder="Escribí para buscar cargo..."
                       value="">
                <input type="hidden" name="cargo_cod" id="cargo-seleccionado" value="">
                
                <div id="lista-desplegable-cargos" 
                     style="position: absolute; top: 100%; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background-color: #ffffff; border: 2px solid var(--accent); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 997; display: none; margin-top: 0.25rem;">
                    <div class="opcion-cargo" 
                         style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.9rem; background-color: #ffffff; color: var(--text-muted);"
                         data-cod="" data-busqueda="">
                        <em>Sin asignar por ahora</em>
                    </div>
                    <?php foreach ($cargos as $c): ?>
                        <div class="opcion-cargo" 
                             style="padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); font-size: 0.9rem; background-color: #ffffff; color: #1e293b;"
                             data-cod="<?= htmlspecialchars($c['cargo_cod']) ?>" 
                             data-busqueda="<?= htmlspecialchars(mb_strtolower($c['nombre'] . ' ' . $c['nivel'])) ?>">
                            <strong><?= htmlspecialchars($c['nombre']) ?></strong> <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 0.5rem;">(<?= htmlspecialchars($c['nivel']) ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                </div>
        </div>
        <?php endif; ?>

        <div class="form-actions" style="justify-content: flex-start;">
            <button type="submit" class="btn-submit">💾 Guardar</button>
            <a href="index.php?page=empleados" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Configuración del motor predictivo genérico
    function setupPredictiveSearch(inputId, listId, hiddenId, optionClass, selectCallback) {
        const input = document.getElementById(inputId);
        const lista = document.getElementById(listId);
        const hidden = document.getElementById(hiddenId);
        const opciones = document.querySelectorAll('.' + optionClass);

        if (!input || !lista) return;

        input.addEventListener('input', function() {
            const query = input.value.trim().toLowerCase();
            let visibles = 0;

            if (query.length === 0) {
                lista.style.display = 'none';
                hidden.value = '';
                return;
            }

            opciones.forEach(op => {
                const searchContext = op.getAttribute('data-busqueda');
                if (searchContext.includes(query)) {
                    op.style.display = 'block';
                    visibles++;
                } else {
                    op.style.display = 'none';
                }
            });

            lista.style.display = visibles > 0 ? 'block' : 'none';
        });

        input.addEventListener('click', function() {
            if (input.value.trim() === '') {
                opciones.forEach(op => op.style.display = 'block');
                lista.style.display = 'block';
            }
        });

        opciones.forEach(op => {
            op.addEventListener('click', function() {
                if (selectCallback) {
                    selectCallback(op, input, hidden);
                }
                lista.style.display = 'none';
            });
            op.addEventListener('mouseenter', () => op.style.background = '#f1f5f9');
            op.addEventListener('mouseleave', () => op.style.background = '#ffffff');
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !lista.contains(e.target)) {
                lista.style.display = 'none';
            }
        });
    }

    // Inicializar Localidades
    setupPredictiveSearch('busqueda-localidad', 'lista-desplegable-localidades', 'localidad-seleccionada', 'opcion-localidad', function(op, input, hidden) {
        input.value = op.textContent.replace(/\s+/g, ' ').trim();
        hidden.value = op.getAttribute('data-cod');
    });

    // Inicializar Departamentos
    setupPredictiveSearch('busqueda-departamento', 'lista-desplegable-departamentos', 'depto-seleccionado', 'opcion-depto', function(op, input, hidden) {
        input.value = op.querySelector('strong').textContent;
        hidden.value = op.getAttribute('data-cod');
    });

    // Inicializar Cargos (Si existe en la vista)
    if (document.getElementById('busqueda-cargo')) {
        setupPredictiveSearch('busqueda-cargo', 'lista-desplegable-cargos', 'cargo-seleccionado', 'opcion-cargo', function(op, input, hidden) {
            const strong = op.querySelector('strong');
            if (strong) {
                const span = op.querySelector('span');
                input.value = strong.textContent + ' ' + span.textContent;
            } else {
                input.value = op.textContent.trim(); // "Sin asignar por ahora"
            }
            hidden.value = op.getAttribute('data-cod');
        });
    }

    // Supervisor nativo con Datalist integrado
    const buscarSup = document.getElementById('supervisor_buscar');
    const ocultoSup = document.getElementById('supervisor_legajo');
    if (buscarSup && ocultoSup) {
        buscarSup.addEventListener('input', () => {
            const match = buscarSup.value.match(/\((\d+)\)\s*$/);
            ocultoSup.value = match ? match[1] : '';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>