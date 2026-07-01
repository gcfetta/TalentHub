<?php
// views/empleados/detalle.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="page-header">
    <h1>👤 <?= htmlspecialchars($empleado['apellido'] . ', ' . $empleado['nombre']) ?></h1>
    <span class="page-subtitle">Legajo #<?= $empleado['legajo'] ?></span>
</div>

<div style="margin-bottom: 2rem; display: flex; gap: 0.75rem;">
    <a href="index.php?page=empleados" class="btn-back" style="margin: 0;">← Volver</a>
    <?php if (in_array($_SESSION['rol'], ['Administrador','RRHH'])): ?>
        <a href="index.php?page=empleados&accion=editar&legajo=<?= $empleado['legajo'] ?>" class="btn" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">Editar perfil</a>
    <?php endif; ?>
</div>

<!-- Datos personales distribuidos con mejor equilibrio espacial -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>📄 Información General</h2>
    </div>
    
    <div style="padding: 2rem;">
        <!-- Grilla principal de datos clave -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 1.5rem;">
            <div class="detail-item">
                <span class="detail-label">Mail</span>
                <span class="detail-value" style="color: var(--accent); font-weight: 700;"><?= htmlspecialchars($empleado['mail']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Teléfono</span>
                <span class="detail-value"><?= htmlspecialchars($empleado['telefono'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Departamento</span>
                <span class="detail-value"><?= htmlspecialchars($empleado['departamento']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Localidad</span>
                <span class="detail-value"><?= htmlspecialchars($empleado['localidad']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de ingreso</span>
                <span class="detail-value"><?= $empleado['fecha_ingreso'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Supervisor directo</span>
                <span class="detail-value" style="font-weight: 700;"><?= htmlspecialchars($empleado['supervisor'] ?? 'Sin supervisor') ?></span>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 1.5rem 0;">

        <!-- Fichas destacadas (Métricas rápidas de rendimiento y tiempo) -->
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 180px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Antigüedad</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);"><?= $empleado['anios_antiguedad'] ?> años</span>
            </div>
            
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 220px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Promedio Evaluaciones</span>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.1rem;">
                    <?php 
                        $prom = $empleado['promedio_evaluaciones']; 
                        $badgeClass = ($prom !== null && $prom >= 7) ? 'high' : 'low';
                    ?>
                    <span class="score-badge <?= $badgeClass ?>" style="font-size: 0.9rem; padding: 0.2rem 0.6rem;">
                        <?= $prom !== null ? number_format($prom, 2) : '0.00' ?>
                    </span>
                    <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Puntaje general</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de cargos -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>💼 Historial de cargos</h2>
    </div>
    <?php if (empty($historial)): ?>
        <div class="empty-state"><span class="empty-icon">🏷️</span>Sin historial de cargos registrados.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Cargo asignado</th>
                <th>Nivel</th>
                <th>Banda salarial</th>
                <th>Desde</th>
                <th>Hasta</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historial as $h): ?>
            <tr>
                <td><strong style="color: var(--text-primary);"><?= htmlspecialchars($h['cargo']) ?></strong></td>
                <td><span class="badge badge-info" style="font-weight: 600;"><?= htmlspecialchars($h['nivel']) ?></span></td>
                <td style="font-weight: 500; color: #475569;">$<?= number_format($h['banda_salarial_min'],0,',','.') ?> – $<?= number_format($h['banda_salarial_max'],0,',','.') ?></td>
                <td><?= $h['fecha_desde'] ?></td>
                <td>
                    <?php if ($h['fecha_hasta']): ?>
                        <?= $h['fecha_hasta'] ?>
                    <?php else: ?>
                        <span class="badge badge-success">Puesto Actual</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Evaluaciones de desempeño -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>⭐ Evaluaciones de desempeño</h2>
    </div>
    <?php if (empty($evaluaciones)): ?>
        <div class="empty-state" style="padding: 2.5rem;"><span class="empty-icon">⭐</span>El empleado no cuenta con evaluaciones registradas todavía.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Puntaje</th>
                <th>Observaciones realizadas</th>
                <th>Evaluador</th>
                <th>Fecha Evaluación</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($evaluaciones as $ev):
            $scoreStyle = $ev['puntaje'] >= 8.5 ? 'high' : ($ev['puntaje'] >= 7.0 ? 'mid' : 'low');
        ?>
            <tr>
                <td><strong><?= $ev['periodo'] ?></strong></td>
                <td><span class="score-badge <?= $scoreStyle ?>"><?= number_format($ev['puntaje'], 2) ?></span></td>
                <td style="color: #475569; font-style: italic; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    "<?= htmlspecialchars($ev['observaciones'] ?? 'Sin comentarios adicionales') ?>"
                </td>
                <td><?= htmlspecialchars($ev['evaluador']) ?></td>
                <td style="color: var(--text-muted);"><?= $ev['fecha_evaluacion'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>