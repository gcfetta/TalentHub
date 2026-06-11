<?php
// views/evaluaciones/detalle.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';

$puntaje = (float)$evaluacion['puntaje'];
$color_puntaje = match(true) {
    $puntaje >= 9 => 'var(--success)',
    $puntaje >= 7 => 'var(--info)',
    $puntaje >= 5 => 'var(--warning)',
    default       => 'var(--danger)',
};
$label_puntaje = match(true) {
    $puntaje >= 9 => '⭐ Sobresaliente',
    $puntaje >= 7 => '✅ Bueno',
    $puntaje >= 5 => '⚠️ Regular',
    default       => '❌ Insuficiente',
};
?>

<div class="page-header">
    <h1>⭐ Evaluación #<?= $evaluacion['evaluacion_id'] ?></h1>
    <span class="page-subtitle">
        <?= htmlspecialchars($evaluacion['apellido'] . ', ' . $evaluacion['nombre']) ?>
        — Período <?= htmlspecialchars($evaluacion['periodo']) ?>
    </span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Evaluación guardada correctamente.</div>
<?php endif; ?>

<div style="margin-bottom:1rem">
    <a href="index.php?page=evaluaciones" class="btn btn-secondary btn-sm">← Volver al listado</a>
</div>

<!-- Stats rápidas -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:1.5rem">
    <div class="stat-card" style="border-left-color:<?= $color_puntaje ?>">
        <span class="stat-icon">🎯</span>
        <div class="stat-info">
            <span class="stat-value" style="color:<?= $color_puntaje ?>"><?= number_format($puntaje, 2) ?></span>
            <span class="stat-label">Puntaje obtenido</span>
        </div>
    </div>
    <div class="stat-card info">
        <span class="stat-icon">📊</span>
        <div class="stat-info">
            <span class="stat-value"><?= $promedio !== null ? number_format($promedio, 2) : '—' ?></span>
            <span class="stat-label">Promedio histórico</span>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">📋</span>
        <div class="stat-info">
            <span class="stat-value"><?= count($historial) ?></span>
            <span class="stat-label">Evaluaciones totales</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <!-- Datos de la evaluación -->
    <div class="card">
        <div class="card-header"><h2>Datos de la evaluación</h2></div>
        <div style="padding:1.4rem">

            <div style="margin-bottom:1.5rem;text-align:center">
                <div style="font-size:3rem;font-weight:800;color:<?= $color_puntaje ?>;line-height:1">
                    <?= number_format($puntaje, 2) ?>
                </div>
                <div style="color:<?= $color_puntaje ?>;font-weight:600;margin-top:0.3rem">
                    <?= $label_puntaje ?>
                </div>
                <!-- Barra de progreso -->
                <div style="margin-top:0.8rem;height:8px;background:var(--bg-hover);border-radius:999px;overflow:hidden">
                    <div style="height:100%;width:<?= ($puntaje * 10) ?>%;background:<?= $color_puntaje ?>;border-radius:999px"></div>
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem">sobre 10 puntos</div>
            </div>

            <table style="width:100%;border-collapse:collapse">
                <?php
                $filas = [
                    ['Período',       htmlspecialchars($evaluacion['periodo'])],
                    ['Fecha',         $evaluacion['fecha_evaluacion']],
                    ['Empleado',      htmlspecialchars($evaluacion['apellido'] . ', ' . $evaluacion['nombre'])],
                    ['Cargo',         htmlspecialchars($evaluacion['cargo']  ?? '—')],
                    ['Nivel',         htmlspecialchars($evaluacion['nivel']  ?? '—')],
                    ['Evaluador',     htmlspecialchars($evaluacion['eval_apellido'] . ', ' . $evaluacion['eval_nombre'])],
                ];
                foreach ($filas as [$k, $v]): ?>
                <tr style="border-top:1px solid var(--border)">
                    <td style="padding:.6rem .4rem;font-size:.8rem;color:var(--text-muted);font-weight:600;width:40%"><?= $k ?></td>
                    <td style="padding:.6rem .4rem;font-size:.9rem"><?= $v ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($evaluacion['observaciones']): ?>
            <div style="margin-top:1.2rem;padding:1rem;background:var(--bg-hover);border-radius:8px">
                <div style="font-size:.8rem;color:var(--text-muted);font-weight:600;margin-bottom:.4rem">OBSERVACIONES</div>
                <p style="font-size:.9rem;line-height:1.6"><?= nl2br(htmlspecialchars($evaluacion['observaciones'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historial del empleado -->
    <div class="card">
        <div class="card-header">
            <h2>Historial del empleado</h2>
            <span class="badge badge-info"><?= count($historial) ?> evaluaciones</span>
        </div>

        <?php if (empty($historial)): ?>
            <div class="empty-state" style="padding:2rem">Sin historial.</div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Período</th>
                    <th>Puntaje</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historial as $h):
                $p = (float)$h['puntaje'];
                $b = match(true) {
                    $p >= 9 => 'badge-success',
                    $p >= 7 => 'badge-info',
                    $p >= 5 => 'badge-warning',
                    default => 'badge-danger',
                };
                $es_actual = $h['evaluacion_id'] == $evaluacion['evaluacion_id'];
            ?>
                <tr <?= $es_actual ? 'style="background:rgba(99,102,241,0.08)"' : '' ?>>
                    <td>
                        <?= htmlspecialchars($h['periodo']) ?>
                        <?php if ($es_actual): ?>
                            <span class="badge badge-info" style="font-size:.65rem">actual</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= $b ?>"><?= number_format($p, 2) ?></span></td>
                    <td style="color:var(--text-muted);font-size:.85rem"><?= $h['fecha_evaluacion'] ?></td>
                    <td>
                        <?php if (!$es_actual): ?>
                        <a href="index.php?page=evaluaciones&accion=ver&id=<?= $h['evaluacion_id'] ?>"
                           class="btn btn-sm btn-secondary">Ver</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>