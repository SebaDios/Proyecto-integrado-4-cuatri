<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    
    // Validaciones
    if (empty($username) || empty($password) || empty($full_name) || empty($role)) {
        $error = 'Todos los campos obligatorios deben ser completados';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $userModel = new User();
        
        // Verificar si el usuario ya existe
        if ($userModel->usernameExists($username)) {
            $error = 'El nombre de usuario ya existe';
        } else {
            if ($userModel->create($username, $password, $full_name, $email, $role)) {
                header('Location: index.php?msg=created');
                exit();
            } else {
                $error = 'Error al crear el usuario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <?php include_once '../../inc/header.php'; ?>
    
    <div style="padding: 1rem 2rem; background: #efebe0; border-bottom: 1px solid #907952;">
        <a href="index.php" class="btn-secondary">← Volver a Usuarios</a>
    </div>

    <main>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre de Usuario: *</label>
                <input type="text" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Contraseña: *</label>
                <input type="password" name="password" required minlength="6">
                <small>Mínimo 6 caracteres</small>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña: *</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>

            <div class="form-group">
                <label>Nombre Completo: *</label>
                <input type="text" name="full_name" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Rol: *</label>
                <select name="role" required>
                    <option value="">Seleccione un rol</option>
                    <option value="Admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Admin') ? 'selected' : ''; ?>>
                        Administrador
                    </option>
                    <option value="Usuario" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Usuario') ? 'selected' : ''; ?>>
                        Usuario
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit">Crear Usuario</button>
                <a href="index.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
