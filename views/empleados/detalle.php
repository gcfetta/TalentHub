<?php
// views/empleados/detalle.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>👤 <?= htmlspecialchars($empleado['apellido'] . ', ' . $empleado['nombre']) ?></h1>
    <span class="page-subtitle">Legajo #<?= $empleado['legajo'] ?></span>
</div>

<div style="display:flex; gap:1rem; margin-bottom:1rem;">
    <a href="index.php?page=empleados" class="btn btn-secondary btn-sm">← Volver</a>
    <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
        <a href="index.php?page=empleados&accion=editar&legajo=<?= $empleado['legajo'] ?>" class="btn btn-sm">Editar</a>
    <?php endif; ?>
</div>

<!-- Datos personales -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h2>Datos personales</h2></div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.2rem; padding:1.4rem;">
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Mail</div>
            <div><?= htmlspecialchars($empleado['mail']) ?></div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Teléfono</div>
            <div><?= htmlspecialchars($empleado['telefono'] ?? '—') ?></div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Departamento</div>
            <div><?= htmlspecialchars($empleado['departamento']) ?></div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Localidad</div>
            <div><?= htmlspecialchars($empleado['localidad']) ?></div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Fecha de ingreso</div>
            <div><?= $empleado['fecha_ingreso'] ?></div>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;margin-bottom:.3rem">Supervisor</div>
            <div><?= htmlspecialchars($empleado['supervisor'] ?? 'Sin supervisor') ?></div>
        </div>
    </div>
</div>

<!-- Historial de cargos -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h2>Historial de cargos</h2></div>
    <?php if (empty($historial)): ?>
        <div class="empty-state"><span class="empty-icon">🏷️</span>Sin historial de cargos.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Cargo</th>
                <th>Nivel</th>
                <th>Banda salarial</th>
                <th>Desde</th>
                <th>Hasta</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historial as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['cargo']) ?></td>
                <td><?= htmlspecialchars($h['nivel']) ?></td>
                <td>$<?= number_format($h['banda_salarial_min'],0,',','.') ?> – $<?= number_format($h['banda_salarial_max'],0,',','.') ?></td>
                <td><?= $h['fecha_desde'] ?></td>
                <td>
                    <?php if ($h['fecha_hasta']): ?>
                        <?= $h['fecha_hasta'] ?>
                    <?php else: ?>
                        <span class="badge badge-success">Actual</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Evaluaciones -->
<div class="card">
    <div class="card-header"><h2>Evaluaciones de desempeño</h2></div>
    <?php if (empty($evaluaciones)): ?>
        <div class="empty-state"><span class="empty-icon">⭐</span>Sin evaluaciones registradas.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Puntaje</th>
                <th>Observaciones</th>
                <th>Evaluador</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($evaluaciones as $ev):
            $color = $ev['puntaje'] >= 9 ? 'badge-success'
                   : ($ev['puntaje'] >= 7 ? 'badge-info' : 'badge-warning');
        ?>
            <tr>
                <td><?= $ev['periodo'] ?></td>
                <td><span class="badge <?= $color ?>"><?= $ev['puntaje'] ?></span></td>
                <td><?= htmlspecialchars($ev['observaciones'] ?? '—') ?></td>
                <td><?= htmlspecialchars($ev['evaluador']) ?></td>
                <td><?= $ev['fecha_evaluacion'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>