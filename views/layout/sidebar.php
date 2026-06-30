<?php
// views/layout/sidebar.php
$page_actual = $_GET['page'] ?? 'dashboard';
$rol = $_SESSION['rol'] ?? 'Empleado';

function menu_item($page, $label, $icono, $page_actual) {
    $activo = ($page_actual === $page) ? 'active' : '';
    echo "<a href='index.php?page={$page}' class='menu-item {$activo}'>{$icono} {$label}</a>";
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">🏢</span>
        <span class="brand-name">TalentHub</span>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="user-rol"><?= htmlspecialchars($rol) ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">General</div>
        <?php menu_item('dashboard', 'Dashboard', '📊', $page_actual); ?>

        <div class="nav-section-label">Gestión</div>
        <?php menu_item('empleados', 'Empleados', '👥', $page_actual); ?>
        <?php menu_item('solicitudes', 'Licencias', '📋', $page_actual); ?>
        <?php menu_item('evaluaciones', 'Evaluaciones', '⭐', $page_actual); ?>

        <?php if (in_array($rol, ['Administrador', 'RRHH'])): ?>
        <div class="nav-section-label">Administración</div>
        <?php menu_item('cargos', 'Cargos', '🏷️', $page_actual); ?>
        <?php menu_item('departamentos', 'Departamentos', '🏬', $page_actual); ?>
        <?php menu_item('auditoria_salario', 'Auditoría salarial', '🧾', $page_actual); ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="index.php?page=logout" class="btn-logout">🚪 Cerrar sesión</a>
    </div>
</aside>

<main class="main-content">