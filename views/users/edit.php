<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/User.php';

$userModel = new User();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_GET['id'];
$user = $userModel->getById($user_id);

if (!$user) {
    header('Location: index.php?msg=error');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($username) || empty($full_name) || empty($role)) {
        $error = 'Los campos obligatorios deben ser completados';
    } else {
        // Verificar si el username ya existe (excluyendo el usuario actual)
        if ($userModel->usernameExists($username, $user_id)) {
            $error = 'El nombre de usuario ya existe';
        } else {
            if ($userModel->update($user_id, $username, $full_name, $email, $role, $activo)) {
                // Si hay nueva contraseña, actualizarla
                if (!empty($_POST['new_password'])) {
                    if ($_POST['new_password'] === $_POST['confirm_password']) {
                        $userModel->updatePassword($user_id, $_POST['new_password']);
                    }
                }
                
                header('Location: index.php?msg=updated');
                exit();
            } else {
                $error = 'Error al actualizar el usuario';
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
    <title>Editar Usuario - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <header>
        <h1>Editar Usuario</h1>
        <a href="index.php">← Volver a Usuarios</a>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre de Usuario: *</label>
                <input type="text" name="username" required 
                       value="<?php echo htmlspecialchars($user['nombre_usuario']); ?>">
            </div>

            <div class="form-group">
                <label>Nombre Completo: *</label>
                <input type="text" name="full_name" required 
                       value="<?php echo htmlspecialchars($user['nombre_completo']); ?>">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group">
                <label>Rol: *</label>
                <select name="role" required>
                    <option value="Admin" <?php echo $user['rol'] == 'Admin' ? 'selected' : ''; ?>>
                        Administrador
                    </option>
                    <option value="Usuario" <?php echo $user['rol'] == 'Usuario' ? 'selected' : ''; ?>>
                        Usuario
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="activo" <?php echo $user['activo'] ? 'checked' : ''; ?>>
                    Usuario Activo
                </label>
            </div>

            <hr>
            <h3>Cambiar Contraseña (opcional)</h3>

            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="password" name="new_password" minlength="6">
                <small>Dejar en blanco para mantener la contraseña actual</small>
            </div>

            <div class="form-group">
                <label>Confirmar Nueva Contraseña:</label>
                <input type="password" name="confirm_password" minlength="6">
            </div>

            <div class="form-actions">
                <button type="submit">Actualizar Usuario</button>
                <a href="index.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
