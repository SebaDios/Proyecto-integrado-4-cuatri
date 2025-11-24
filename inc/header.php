<?php
// Header unificado para todo el sistema
// Incluir este archivo en todas las vistas con: include_once '../inc/header.php'; o según la ruta relativa

// Obtener información del usuario si está logueado
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$is_logged_in = isset($_SESSION['user_id']);

// Determinar la ruta base según la ubicación del archivo que incluye el header
// Esto permite que el header funcione desde diferentes niveles de carpetas
$base_path = '';
$script_path = $_SERVER['PHP_SELF'];
if (strpos($script_path, '/views/users/') !== false || 
    strpos($script_path, '/views/productos/') !== false ||
    strpos($script_path, '/views/sales/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_path, '/views/') !== false) {
    $base_path = '../';
}
?>
<header class="main-header">
    <div class="header-left">
        <img src="<?php echo $base_path; ?>assets/images/logo.jpg" alt="Logo Antojitos Alkase" class="logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="logo-placeholder" style="display: none; width: 60px; height: 60px; background: #fdab25; border-radius: 50%; align-items: center; justify-content: center; color: #6c5336; font-weight: bold; font-size: 24px;">A</div>
        <h1 class="company-name">Antojitos Alkase</h1>
    </div>
    <?php if ($is_logged_in): ?>
    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($user_role); ?></span>
        </div>
        <a href="<?php echo $base_path; ?>controllers/logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>
    <?php endif; ?>
</header>

