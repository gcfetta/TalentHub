<?php
// views/solicitudes/detalle.php
require_once __DIR__ . '/../../views/layout/header.php';
require_once __DIR__ . '/../../views/layout/sidebar.php';

$badge = match($solicitud['estado']) {
    'Aprobada'  => 'badge-success',
    'Rechazada' => 'badge-danger',
    default     => 'badge-warning',
};
$puede_gestionar = in_array($_SESSION['rol'], ['Administrador', 'RRHH', 'Supervisor']);
$es_pendiente    = $solicitud['estado'] === 'Pendiente';
?>

<div class="page-header">
    <h1>📋 Solicitud #<?= $solicitud['nro_solicitud'] ?></h1>
    <span class="badge <?= $badge ?>"><?= $solicitud['estado'] ?></span>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Operación realizada correctamente.</div>
<?php endif; ?>

<div style="margin-bottom:1rem;">
    <a href="index.php?page=solicitudes" class="btn btn-secondary btn-sm">← Volver</a>
</div>

<!-- Datos de la solicitud -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h2>Datos de la solicitud</h2></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.2rem;padding:1.4rem">
        <div>
            <div class="detalle-label">Empleado</div>
            <div><?= htmlspecialchars($solicitud['apellido'] . ', ' . $solicitud['nombre']) ?></div>
        </div>
        <div>
            <div class="detalle-label">Tipo de licencia</div>
            <div><?= htmlspecialchars($solicitud['tipo_licencia']) ?></div>
        </div>
        <div>
            <div class="detalle-label">Fecha de solicitud</div>
            <div><?= $solicitud['fecha_solicitud'] ?></div>
        </div>
        <div>
            <div class="detalle-label">Fecha inicio</div>
            <div><?= $solicitud['fecha_inicio'] ?></div>
        </div>
        <div>
            <div class="detalle-label">Fecha fin</div>
            <div><?= $solicitud['fecha_fin'] ?></div>
        </div>
        <div>
            <div class="detalle-label">Días solicitados</div>
            <div><?= $solicitud['dias_solicitados'] ?></div>
        </div>
        <div>
            <div class="detalle-label">Requiere certificado</div>
            <div><?= $solicitud['requiere_certificado'] ? '✅ Sí' : '❌ No' ?></div>
        </div>
        <div>
            <div class="detalle-label">Remunerada</div>
            <div><?= $solicitud['remunerada'] ? '✅ Sí' : '❌ No' ?></div>
        </div>
    </div>
</div>

<!-- Gestión de estado (solo roles con permiso y si está Pendiente) -->
<?php if ($puede_gestionar && $es_pendiente): ?>
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h2>Gestionar solicitud</h2></div>
    <div style="padding:1.4rem">
        <form method="POST" action="index.php?page=solicitudes&accion=cambiar_estado">
            <input type="hidden" name="nro_solicitud" value="<?= $solicitud['nro_solicitud'] ?>">
            <input type="hidden" name="estado_actual"  value="<?= $solicitud['estado'] ?>">

            <div class="form-group">
                <label>Observación</label>
                <input type="text" name="observacion" class="form-control"
                       placeholder="Motivo de aprobación o rechazo...">
            </div>

            <div style="display:flex;gap:.8rem;margin-top:1rem">
                <button type="submit" name="estado_nuevo" value="Aprobada"
                        class="btn btn-success">✅ Aprobar</button>
                <button type="submit" name="estado_nuevo" value="Rechazada"
                        class="btn btn-danger">❌ Rechazar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Documentos adjuntos -->
<?php if (!empty($documentos)): ?>
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h2>Documentos adjuntos</h2></div>
    <table class="table">
        <thead>
            <tr><th>Tipo</th><th>Archivo</th><th>Fecha de carga</th></tr>
        </thead>
        <tbody>
        <?php foreach ($documentos as $doc): ?>
            <tr>
                <td><?= htmlspecialchars($doc['tipo_documento']) ?></td>
                <td><?= htmlspecialchars($doc['archivo_path']) ?></td>
                <td><?= $doc['fecha_carga'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Historial de estados -->
<div class="card">
    <div class="card-header"><h2>Historial de estados</h2></div>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Antes</th>
                <th>Después</th>
                <th>Observación</th>
                <th>Gestionó</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($auditoria as $a):
            $badge_nuevo = match($a['estado_nuevo']) {
                'Aprobada'  => 'badge-success',
                'Rechazada' => 'badge-danger',
                default     => 'badge-warning',
            };
        ?>
            <tr>
                <td><?= $a['fecha_cambio'] ?></td>
                <td><?= $a['estado_anterior'] ? '<span class="badge badge-secondary">' . $a['estado_anterior'] . '</span>' : '—' ?></td>
                <td><span class="badge <?= $badge_nuevo ?>"><?= $a['estado_nuevo'] ?></span></td>
                <td><?= htmlspecialchars($a['observacion'] ?? '—') ?></td>
                <td><?= htmlspecialchars($a['supervisor']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.detalle-label {
    font-size: .75rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: .3rem;
}
</style>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>