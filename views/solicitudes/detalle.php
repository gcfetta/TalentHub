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

<div class="page-header" style="flex-direction: row; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <h1>📋 Solicitud #<?= $solicitud['nro_solicitud'] ?></h1>
        <span class="badge <?= $badge ?>" style="font-size: 0.85rem; padding: 0.35rem 0.8rem;"><?= $solicitud['estado'] ?></span>
    </div>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Operación realizada correctamente.</div>
<?php endif; ?>

<div style="margin-bottom: 2rem; display: flex; gap: 0.75rem;">
    <a href="index.php?page=solicitudes" class="btn-back" style="margin: 0;">← Volver</a>
</div>

<!-- Datos principales de la solicitud (Estilo Empleado) -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>📄 Información General de Licencia</h2>
    </div>
    
    <div style="padding: 2rem;">
        <!-- Grilla principal de 3 columnas para alinear con Empleados -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 1.5rem;">
            <div class="detail-item">
                <span class="detail-label">Empleado Solicitante</span>
                <span class="detail-value" style="color: var(--accent); font-weight: 700;">
                    <?= htmlspecialchars($solicitud['apellido'] . ', ' . $solicitud['nombre']) ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tipo de licencia</span>
                <span class="detail-value" style="font-weight: 700;"><?= htmlspecialchars($solicitud['tipo_licencia']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de solicitud emitída</span>
                <span class="detail-value"><?= $solicitud['fecha_solicitud'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha inicio de licencia</span>
                <span class="detail-value"><?= $solicitud['fecha_inicio'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha fin de licencia</span>
                <span class="detail-value"><?= $solicitud['fecha_fin'] ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Duración Total solicitada</span>
                <span class="detail-value"><?= $solicitud['dias_solicitados'] ?> días hábiles</span>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 1.5rem 0;">

        <!-- Fichas destacadas sobre requisitos de la licencia -->
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 180px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Acreditación Médica</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);">
                    <?= $solicitud['requiere_certificado'] ? '✅ Requerida' : '❌ No necesaria' ?>
                </span>
            </div>
            
            <div style="background: var(--bg-base); padding: 1rem 1.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 0.2rem; min-width: 180px;">
                <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Estado Salarial</span>
                <span style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary);">
                    <?= $solicitud['remunerada'] ? '✅ Remunerada' : '❌ Sin Goce' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Gestión de estado (solo roles con permiso y si está Pendiente) -->
<?php if ($puede_gestionar && $es_pendiente): ?>
<div class="card" style="margin-bottom: 2rem; background: var(--bg-hover);">
    <div class="card-header" style="border-bottom: none; padding-bottom: 0;">
        <h2>⚖️ Decisión del supervisor</h2>
    </div>
    <div style="padding: 1.5rem 2rem;">
        <form method="POST" action="index.php?page=solicitudes&accion=cambiar_estado" style="display: flex; gap: 1.5rem; align-items: flex-end;">
            <input type="hidden" name="nro_solicitud" value="<?= $solicitud['nro_solicitud'] ?>">
            <input type="hidden" name="estado_actual" value="<?= $solicitud['estado'] ?>">

            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Observaciones de respuesta</label>
                <input type="text" name="observacion" class="form-control" style="background: var(--bg-surface);"
                       placeholder="Ingresa un motivo en caso de rechazo u observación de aprobación...">
            </div>

            <div style="display:flex; gap: 0.75rem; flex-shrink: 0;">
                <button type="submit" name="estado_nuevo" value="Aprobada" class="btn btn-success" style="padding: 0.8rem 1.5rem;">
                    ✅ Aprobar Licencia
                </button>
                <button type="submit" name="estado_nuevo" value="Rechazada" class="btn btn-danger" style="padding: 0.8rem 1.5rem;">
                    ❌ Rechazar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Documentos adjuntos -->
<?php if (!empty($documentos)): ?>
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>📎 Documentos Adjuntos (Certificados)</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Tipo de Documento</th>
                <th>Archivo</th>
                <th>Fecha de Carga</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($documentos as $doc): ?>
            <tr>
                <td><strong style="color: var(--text-primary);"><?= htmlspecialchars($doc['tipo_documento']) ?></strong></td>
                <td>
                    <a href="<?= htmlspecialchars($doc['archivo_path']) ?>" target="_blank" style="font-weight: 500; display: inline-flex; align-items: center; gap: 0.4rem;">
                        📄 Ver archivo subido
                    </a>
                </td>
                <td style="color: var(--text-muted);"><?= $doc['fecha_carga'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Historial de estados -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2>📜 Auditoría y Trazabilidad</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha Modificación</th>
                <th>Estado Anterior</th>
                <th>Nuevo Estado</th>
                <th>Observación del Gestor</th>
                <th>Autorizado Por</th>
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
                <td style="color: var(--text-muted);"><?= $a['fecha_cambio'] ?></td>
                <td><?= $a['estado_anterior'] ? '<span class="badge badge-secondary">' . $a['estado_anterior'] . '</span>' : '—' ?></td>
                <td><span class="badge <?= $badge_nuevo ?>"><?= $a['estado_nuevo'] ?></span></td>
                <td style="color: #475569; font-style: italic;">
                    "<?= htmlspecialchars($a['observacion'] ?? 'Procesamiento automático / Sin comentarios') ?>"
                </td>
                <td><strong style="color: var(--text-primary);"><?= htmlspecialchars($a['supervisor']) ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>