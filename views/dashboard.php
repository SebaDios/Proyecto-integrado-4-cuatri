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
    <header>
        <h1>Antojitos ALKASE</h1>
        <div class="user-info">
            <span>Usuario: <?php echo htmlspecialchars($user_name); ?></span>
            <span>Rol: <?php echo htmlspecialchars($user_role); ?></span>
            <a href="../controllers/logout.php">Cerrar Sesión</a>
        </div>
    </header>

    <main>
        <h2>Bienvenido, <?php echo htmlspecialchars($user_name); ?></h2>
        <p>Seleccione la acción a realizar:</p>

        <div class="dashboard-menu">
            
            <?php if ($is_admin): ?>
            <!-- OPCIÓN 1: Gestionar Usuarios (SOLO ADMIN) -->
            <div class="menu-option">
                <a href="users/index.php">
                    <h3>Gestionar Usuarios</h3>
                    <p>Crear, modificar y eliminar usuarios del sistema</p>
                </a>
            </div>
            
            <!-- OPCIÓN 2: Gestionar Inventario (SOLO ADMIN) -->
            <div class="menu-option">
                <a href="inventory/index.php">
                    <h3>Gestionar Inventario</h3>
                    <p>Administrar productos y alertas de stock</p>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- OPCIÓN 3: Punto de Venta (AMBOS ROLES) -->
            <div class="menu-option">
                <a href="sales/pos.php">
                    <h3>Punto de Venta</h3>
                    <p>Registrar nuevas ventas y comandas</p>
                </a>
            </div>
            
            <!-- OPCIÓN 4: Registros (AMBOS ROLES con diferentes permisos) -->
            <div class="menu-option">
                <a href="sales/reports.php">
                    <h3>Registros</h3>
                    <p>
                        <?php if ($is_admin): ?>
                            Ver y modificar ventas, cortes de caja y reportes
                        <?php else: ?>
                            Ver ventas personales y corte de caja
                        <?php endif; ?>
                    </p>
                </a>
            </div>
            
        </div>
    </main>
</body>
</html>
