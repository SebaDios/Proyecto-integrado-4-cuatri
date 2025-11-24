<?php
require_once '../inc/session.php';
requireLogin(); // Verificar que el usuario esté autenticado

$user_name = $_SESSION['full_name'];
$user_role = $_SESSION['user_role'];
$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../assets/css.css">
</head>
<body>
    <?php include_once '../inc/header.php'; ?>

    <main>
        <h2>Bienvenido, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Seleccione la acción a realizar:</p>

        <div class="dashboard-menu">
            
            <?php if ($is_admin): ?>
            <!-- OPCIÓN 1: Gestionar Usuarios (SOLO ADMIN) -->
            <div class="menu-option">
                <a href="users/index.php">
                    <h3>Gestionar Usuarios</h3>
                    
                </a>
            </div>
            
            <!-- OPCIÓN 2: Gestionar Inventario (SOLO ADMIN) -->
            <div class="menu-option">
                <a href="productos/index.php">
                    <h3>Gestionar Inventario</h3>
                    
                </a>
            </div>
            <?php endif; ?>
            
            <!-- OPCIÓN 3: Punto de Venta (AMBOS ROLES) -->
            <div class="menu-option">
                <a href="sales/pos.php">
                    <h3>Punto de Venta</h3>
                    
                </a>
            </div>
            
            <!-- OPCIÓN 4: Registros (AMBOS ROLES con diferentes permisos) -->
            <div class="menu-option">
                <a href="sales/reports.php">
                    <h3>Registros</h3>
                    
                </a>
            </div>
            
        </div>
    </main>
</body>
</html>
