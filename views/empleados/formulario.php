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
            <div class="form-group">
                <label>Localidad <span>*</span></label>
                <select name="localidad_cod" class="form-control" required>
                    <option value="">Seleccioná...</option>
                    <?php foreach ($localidades as $loc): ?>
                        <option value="<?= $loc['localidad_cod'] ?>"
                            <?= ($empleado['localidad_cod'] ?? '') == $loc['localidad_cod'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['nombre']) ?> — <?= htmlspecialchars($loc['provincia']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Departamento <span>*</span></label>
                <select name="depto_cod" class="form-control" required>
                    <option value="">Seleccioná...</option>
                    <?php foreach ($departamentos as $d): ?>
                        <option value="<?= $d['depto_cod'] ?>"
                            <?= ($empleado['depto_cod'] ?? '') == $d['depto_cod'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

            <script>
            (function () {
                const buscar = document.getElementById('supervisor_buscar');
                const oculto = document.getElementById('supervisor_legajo');
                buscar.addEventListener('input', () => {
                    const match = buscar.value.match(/\((\d+)\)\s*$/);
                    oculto.value = match ? match[1] : '';
                });
            })();
            </script>
        </div>

        <?php if ($es_nuevo): ?>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Cargo inicial</label>
                <select name="cargo_cod" class="form-control">
                    <option value="">Sin asignar por ahora</option>
                    <?php foreach ($cargos as $c): ?>
                        <option value="<?= $c['cargo_cod'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?> (<?= htmlspecialchars($c['nivel']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
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

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>