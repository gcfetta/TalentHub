<?php
// views/solicitudes/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>📋 Licencias</h1>
    <span class="page-subtitle"><span id="contador-registros"><?= count($solicitudes) ?></span> solicitudes</span>
</div>

<?php
$estado_actual_filtro = $_GET['estado'] ?? '';
$filtros = ['' => 'Todas', 'Pendiente' => 'Pendientes', 'Aprobada' => 'Aprobadas', 'Rechazada' => 'Rechazadas'];
?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.8rem; margin-bottom:1.5rem">
    <div style="display:flex; gap:.6rem">
        <?php foreach ($filtros as $valor => $etiqueta): ?>
            <a href="index.php?page=solicitudes<?= $valor ? "&estado={$valor}" : '' ?>"
               class="btn btn-sm <?= $estado_actual_filtro === $valor ? '' : 'btn-secondary' ?>">
                <?= $etiqueta ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Operación realizada correctamente.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <h2>Solicitudes de licencia</h2>
        
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            
            <div style="display: flex; gap: 0.4rem; align-items: center; position: relative;">
                <input type="text" id="buscador-instantaneo" class="form-control" placeholder="Buscar por nombre o legajo..." 
                       style="min-width: 260px; padding: 0.5rem 2.5rem 0.5rem 1rem; border-radius: 10px;">
                
                <button type="button" id="btn-limpiar" class="btn btn-sm btn-secondary" 
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 0.2rem 0.5rem; display: none; background: transparent !important; border: none !important; color: var(--text-muted) !important; font-size: 0.85rem; z-index: 10;" title="Limpiar búsqueda">✕</button>
            </div>

            <a href="index.php?page=solicitudes&accion=nueva" class="btn">+ Nueva solicitud</a>
        </div>
    </div>

    <?php if (empty($solicitudes)): ?>
        <div class="empty-state" id="estado-vacio-general">
            <span class="empty-icon">📋</span>
            No hay solicitudes registradas.
        </div>
    <?php else: ?>
    <div id="contenedor-tabla-solicitudes">
        <table class="table" id="tabla-solicitudes">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Empleado</th>
                    <th>Tipo de licencia</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($solicitudes as $s):
                $badge = match($s['estado']) {
                    'Aprobada'  => 'badge-success',
                    'Rechazada' => 'badge-danger',
                    default     => 'badge-warning',
                };
                
                // Extraemos o definimos el legajo de forma segura en base a lo que devuelva tu consulta SQL ($s['legajo'])
                $legajo_emp = isset($s['legajo']) ? (string)$s['legajo'] : '';
            ?>
                <tr class="fila-solicitud" 
                    data-empleado="<?= htmlspecialchars(mb_strtolower($s['empleado'])) ?>" 
                    data-legajo="<?= htmlspecialchars($legajo_emp) ?>">
                    <td><strong style="color: var(--text-muted);">#<?= $s['nro_solicitud'] ?></strong></td>
                    <td>
                        <div class="employee-meta">
                            <strong style="color: var(--text-primary); font-size: 0.92rem;"><?= htmlspecialchars($s['empleado']) ?></strong>
                            <?php if ($legajo_emp): ?>
                                <span class="employee-id">#<?= htmlspecialchars($legajo_emp) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($s['tipo_licencia']) ?></td>
                    <td><?= $s['fecha_inicio'] ?></td>
                    <td><?= $s['fecha_fin'] ?></td>
                    <td><?= $s['dias_solicitados'] ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $s['estado'] ?></span></td>
                    <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                        <a href="index.php?page=solicitudes&accion=ver&nro=<?= $s['nro_solicitud'] ?>"
                           class="btn btn-sm btn-secondary">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="empty-state" id="estado-vacio-busqueda" style="display: none; padding: 3rem;">
        <span class="empty-icon">🔍</span>
        No se encontraron solicitudes que coincidan con la búsqueda.
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-instantaneo');
    const btnLimpiar = document.getElementById('btn-limpiar');
    const filas = document.querySelectorAll('.fila-solicitud');
    const contador = document.getElementById('contador-registros');
    const tablaContenedor = document.getElementById('contenedor-tabla-solicitudes');
    const vacioBusqueda = document.getElementById('estado-vacio-busqueda');

    if (!buscador) return;

    buscador.addEventListener('input', function() {
        const query = buscador.value.trim().toLowerCase();
        let encontrados = 0;

        if (query.length > 0) {
            btnLimpiar.style.display = 'block';
        } else {
            btnLimpiar.style.display = 'none';
        }

        filas.forEach(fila => {
            const empleado = fila.getAttribute('data-empleado');
            const legajo = fila.getAttribute('data-legajo');
            
            // CORRECCIÓN: Compara la query tanto con el nombre como con el legajo
            if (empleado.includes(query) || legajo.includes(query)) {
                fila.style.display = '';
                encontrados++;
            } else {
                fila.style.display = 'none';
            }
        });

        if (contador) {
            contador.textContent = encontrados;
        }

        if (encontrados === 0 && query.length > 0) {
            if (tablaContenedor) tablaContenedor.style.display = 'none';
            if (vacioBusqueda) vacioBusqueda.style.display = 'block';
        } else {
            if (tablaContenedor) tablaContenedor.style.display = 'block';
            if (vacioBusqueda) vacioBusqueda.style.display = 'none';
        }
    });

    btnLimpiar.addEventListener('click', function() {
        buscador.value = '';
        btnLimpiar.style.display = 'none';
        
        filas.forEach(fila => fila.style.display = '');
        if (contador) {
            contador.textContent = filas.length;
        }
        
        if (tablaContenedor) tablaContenedor.style.display = 'block';
        if (vacioBusqueda) vacioBusqueda.style.display = 'none';
        
        buscador.focus();
    });
});
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>