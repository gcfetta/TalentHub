<?php
// views/empleados/listado.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';

$es_empleado_raso = ($_SESSION['rol'] ?? '') === 'Empleado';
?>

<div class="page-header">
    <h1>👥 Empleados</h1>
    <span class="page-subtitle"><span id="contador-registros"><?= count($empleados) ?></span> registros en total</span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Empleado guardado correctamente.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <h2>Nómina Activa</h2>
        
        <!-- Contenedor derecho para Buscador Instantáneo + Botones de Acción -->
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            
            <!-- Buscador en tiempo real sin botón de submit -->
            <div style="display: flex; gap: 0.4rem; align-items: center; position: relative;"> <input type="text" id="buscador-instantaneo" class="form-control" placeholder="Buscar por nombre o legajo..." 
                    style="min-width: 260px; padding: 0.5rem 2.5rem 0.5rem 1rem; border-radius: 10px;">
                
                <button type="button" id="btn-limpiar" class="btn btn-sm btn-secondary" 
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 0.2rem 0.5rem; display: none; background: transparent !important; border: none !important; color: var(--text-muted) !important; font-size: 0.85rem; z-index: 10;" title="Limpiar búsqueda">✕</button>
            </div>

            <!-- Botones de Gestión (CSV / Nuevo Empleado) -->
            <div style="display: flex; gap: 0.5rem;">
                <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                    <a href="index.php?page=empleados&accion=exportar" class="btn btn-secondary">⬇ Exportar CSV</a>
                    <a href="index.php?page=empleados&accion=nuevo" class="btn">+ Nuevo empleado</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (empty($empleados)): ?>
        <div class="empty-state" id="estado-vacio-general">
            <span class="empty-icon">👥</span>
            No hay empleados registrados en el sistema.
        </div>
    <?php else: ?>
    <!-- ID añadido para control exclusivo del buscador dinámico -->
    <div id="contenedor-tabla-empleados">
        <table class="table" id="tabla-empleados">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Cargo actual</th>
                    <th>Departamento</th>
                    <th>Localidad</th>
                    <th>Ingreso</th>
                    <?php if (!$es_empleado_raso): ?><th>Evaluación</th><?php endif; ?>
                    <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            foreach ($empleados as $idx => $emp): 
                $nombre_completo = $emp['nombre_completo'];
                $inicial = mb_substr(trim($emp['nombre_completo']), 0, 1);
                
                $bg_avatars = ['#a855f7', '#3b82f6', '#ec4899', '#10b981', '#f59e0b'];
                $avatar_color = $bg_avatars[$idx % count($bg_avatars)];
                
                $promedio = $emp['promedio_evaluaciones'];
                $scoreStyle = ($promedio !== null && $promedio >= 7) ? 'high' : 'low';
            ?>
            <!-- Guardamos el legajo y nombre en atributos de datos (data-*) para búsquedas ultra precisas -->
            <tr class="fila-empleado" data-legajo="<?= htmlspecialchars($emp['legajo']) ?>" data-nombre="<?= htmlspecialchars(mb_strtolower($nombre_completo)) ?>">
                <!-- Columna Principal de Empleado con Avatar e ID -->
                <td style="display: flex; align-items: center;">
                    <div class="avatar-circle" style="background: <?= $avatar_color ?>;"><?= htmlspecialchars($inicial) ?></div>
                    <div class="employee-meta">
                        <strong><?= htmlspecialchars($nombre_completo) ?></strong>
                        <span class="employee-id">#<?= htmlspecialchars($emp['legajo']) ?></span>
                    </div>
                </td>
                
                <td><?= htmlspecialchars($emp['cargo_actual'] ?? '—') ?></td>
                <td><?= htmlspecialchars($emp['departamento'] ?? '—') ?></td>
                <td><?= htmlspecialchars($emp['localidad'] ?? '—') ?></td>
                <td><?= htmlspecialchars($emp['fecha_ingreso'] ?? '—') ?></td>
                
                <!-- Columna de Promedio Evaluaciones -->
                <?php if (!$es_empleado_raso): ?>
                <td>
                    <?php if ($promedio !== null): ?>
                        <span class="score-badge <?= $scoreStyle ?>">
                            <?= number_format($promedio, 1) ?>
                        </span>
                    <?php else: ?>
                        <span class="score-badge low">0.0</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                
                <!-- Botones de Acción perfectamente alineados a la derecha -->
                <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                    <div class="table-actions" style="display: inline-flex; gap: 0.4rem; justify-content: flex-end;">
                        <a href="index.php?page=empleados&accion=ver&legajo=<?= $emp['legajo'] ?>"
                           class="btn btn-sm btn-secondary">Ver</a>
                        
                        <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
                            <a href="index.php?page=empleados&accion=editar&legajo=<?= $emp['legajo'] ?>"
                               class="btn btn-sm btn-secondary">Editar</a>
                        <a href="index.php?page=empleados&accion=eliminar&legajo=<?= $emp['legajo'] ?>"
                           class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar al empleado <?= htmlspecialchars($nombre_completo) ?>?">
                               Eliminar
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mensaje alternativo si el filtro instantáneo no encuentra nada -->
    <div class="empty-state" id="estado-vacio-busqueda" style="display: none; padding: 3rem;">
        <span class="empty-icon">🔍</span>
        No se encontraron empleados que coincidan con la búsqueda.
    </div>
    <?php endif; ?>
</div>

<!-- Lógica JavaScript Dinámica en tiempo real -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-instantaneo');
    const btnLimpiar = document.getElementById('btn-limpiar');
    const filas = document.querySelectorAll('.fila-empleado');
    const contador = document.getElementById('contador-registros');
    const tablaContenedor = document.getElementById('contenedor-tabla-empleados');
    const vacioBusqueda = document.getElementById('estado-vacio-busqueda');

    if (!buscador) return;

    buscador.addEventListener('input', function() {
        const query = buscador.value.trim().toLowerCase();
        let encontrados = 0; // Variable corregida sin espacios internos

        // Mostrar u Ocultar el botón "✕" si hay texto escrito
        if (query.length > 0) {
            btnLimpiar.style.display = 'block';
        } else {
            btnLimpiar.style.display = 'none';
        }

        // Evaluar cada fila de la tabla en tiempo real
        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre');
            const legajo = fila.getAttribute('data-legajo');

            if (nombre.includes(query) || legajo.includes(query)) {
                fila.style.display = '';
                encontrados++;
            } else {
                fila.style.display = 'none';
            }
        });

        // Actualizar el número del contador dinámico superior
        if (contador) {
            contador.textContent = encontrados;
        }

        // Si el filtro no dio resultados, ocultar tabla y mostrar aviso visual
        if (encontrados === 0 && query.length > 0) {
            if (tablaContenedor) tablaContenedor.style.display = 'none';
            if (vacioBusqueda) vacioBusqueda.style.display = 'block';
        } else {
            if (tablaContenedor) tablaContenedor.style.display = 'block';
            if (vacioBusqueda) vacioBusqueda.style.display = 'none';
        }
    });

    // Acción del botón Limpiar "✕"
    btnLimpiar.addEventListener('click', function() {
        buscador.value = '';
        btnLimpiar.style.display = 'none';
        
        // Restaurar visibilidad completa de la nómina original
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