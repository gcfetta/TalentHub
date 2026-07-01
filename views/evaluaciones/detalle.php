<?php
// views/evaluaciones/detalle.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';

$puntaje = (float)$evaluacion['puntaje'];
$scoreStyle = match(true) {
    $puntaje >= 8.5 => 'high',
    $puntaje >= 7.0 => 'mid',
    default         => 'low',
};
$label_puntaje = match(true) {
    $puntaje >= 9 => '⭐ Sobresaliente',
    $puntaje >= 7 => '✅ Rendimiento Bueno',
    $puntaje >= 5 => '⚠️ Regular',
    default       => '❌ Insuficiente',
};
?>

<div class="page-header" style="flex-direction: row; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <h1>⭐ Evaluación #<?= $evaluacion['evaluacion_id'] ?></h1>
        <span class="score-badge <?= $scoreStyle ?>" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
            <?= number_format($puntaje, 2) ?>
        </span>
    </div>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Evaluación guardada correctamente.</div>
<?php endif; ?>

<div style="margin-bottom: 2rem; display: flex; gap: 0.75rem;">
    <a href="index.php?page=evaluaciones" class="btn-back" style="margin: 0;">← Volver al listado</a>
</div>

<!-- Datos principales de la evaluación en formato Horizontal Amplio -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>📄 Detalles del Rendimiento</h2>
    </div>
    
    <div style="padding: 2rem;">
        <!-- Grilla principal de 3 columnas coherente con el resto del sistema -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 1.5rem;">
            <div class="detail-item">
                <span class="detail-label">Empleado Evaluado</span>
                <span class="detail-value" style="color: var(--accent); font-weight: 700;">
                    <?= htmlspecialchars($evaluacion['apellido'] . ', ' . $evaluacion['nombre']) ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Cargo Actual</span>
                <span class="detail-value"><?= htmlspecialchars($evaluacion['cargo'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nivel de Puesto</span>
                <span class="badge badge-info" style="align-self: flex-start; font-weight: 600; margin-top: 0.1rem;">
                    <?= htmlspecialchars($evaluacion['nivel'] ?? '—') ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Período Evaluado</span>
                <span class="detail-value" style="font-weight: 700;"><?= htmlspecialchars($evaluacion['periodo']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de Calificación</span>
                <span class="detail-value"><?= $evaluacion['fecha_evaluacion'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Líder Evaluador</span>
                <span class="detail-value"><?= htmlspecialchars($evaluacion['eval_apellido'] . ', ' . $evaluacion['eval_nombre']) ?></span>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 1.5rem 0;">

        <!-- Bloques informativos destacados (Fichas de Métricas Rápidas) -->
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 200px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Calificación</span>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.1rem;">
                    <span class="score-badge <?= $scoreStyle ?>" style="font-size: 1.1rem; padding: 0.2rem 0.6rem;">
                        <?= number_format($puntaje, 2) ?>
                    </span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);"><?= $label_puntaje ?></span>
                </div>
            </div>
            
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 180px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Promedio Histórico</span>
                <span style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.1rem;">
                    <?= $promedio !== null ? number_format($promedio, 2) : '—' ?>
                </span>
            </div>

            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 180px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Revisiones Realizadas</span>
                <span style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); margin-top: 0.1rem;">
                    <?= count($historial) ?> evaluaciones
                </span>
            </div>
        </div>

        <?php if ($evaluacion['observaciones']): ?>
        <div style="margin-top: 1.5rem; padding: 1.2rem; background: #f8fafc; border-left: 4px solid var(--accent); border-radius: 8px;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 0.4rem; letter-spacing: 0.05em;">Feedback y Observaciones</div>
            <p style="font-size: 0.92rem; line-height: 1.6; color: #334155; font-style: italic; margin: 0;">"<?= nl2br(htmlspecialchars($evaluacion['observaciones'])) ?>"</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Historial de evaluaciones anteriores del mismo empleado -->
<div class="card">
    <div class="card-header">
        <h2>📜 Historial de Evaluaciones de la Línea de Tiempo</h2>
    </div>

    <?php if (empty($historial)): ?>
        <div class="empty-state" style="padding: 2.5rem;">El empleado no cuenta con un historial de evaluaciones previo.</div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Período Evaluado</th>
                <th>Puntaje Obtenido</th>
                <th>Fecha de Registro</th>
                <th style="text-align: right; padding-right: 1.5rem;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historial as $h):
            // Corrección aquí: leemos directamente la clave 'puntaje' que viene del archivo original
            $p = (float)$h['puntaje'];
            $loopStyle = $p >= 8.5 ? 'high' : ($p >= 7.0 ? 'mid' : 'low');
            $es_actual = $h['evaluacion_id'] == $evaluacion['evaluacion_id'];
        ?>
            <tr <?= $es_actual ? 'style="background: rgba(124, 58, 237, 0.04); font-weight: 600;"' : '' ?>>
                <td>
                    <?= htmlspecialchars($h['periodo']) ?>
                    <?php if ($es_actual): ?>
                        <span class="badge badge-info" style="font-size: 0.65rem; margin-left: 0.5rem; padding: 0.15rem 0.5rem;">Actual</span>
                    <?php endif; ?>
                </td>
                <td><span class="score-badge <?= $loopStyle ?>"><?= number_format($p, 2) ?></span></td>
                <td style="color: var(--text-muted);"><?= $h['fecha_evaluacion'] ?></td>
                <td style="text-align: right; padding-right: 1.5rem; vertical-align: middle;">
                    <?php if (!$es_actual): ?>
                    <a href="index.php?page=evaluaciones&accion=ver&id=<?= $h['evaluacion_id'] ?>"
                       class="btn btn-sm btn-secondary">Ver informe</a>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic; padding-right: 0.5rem;">Visualizando</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>