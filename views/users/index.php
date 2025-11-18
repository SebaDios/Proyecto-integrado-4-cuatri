<?php
require_once '../../inc/session.php';
requireAdmin(); // Solo admin puede acceder

require_once '../../models/user.php';

$userModel = new User();
$users = $userModel->getAll();

$message = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $message = 'Usuario creado exitosamente';
            break;
        case 'updated':
            $message = 'Usuario actualizado exitosamente';
            break;
        case 'deleted':
            $message = 'Usuario eliminado exitosamente';
            break;
        case 'error':
            $message = 'Error al procesar la operación';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <header>
        <h1>Gestión de Usuarios</h1>
        <a href="../dashboard.php">← Volver al Dashboard</a>
    </header>

    <main>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="create.php" class="btn-primary">+ Crear Nuevo Usuario</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        No hay usuarios registrados
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($user['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['rol']); ?></td>
                    <td><?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($user['fecha_creacion'])); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $user['id_usuario']; ?>">Editar</a>
                        <?php if ($user['id_usuario'] != $_SESSION['user_id']): ?>
                            <a href="delete.php?id=<?php echo $user['id_usuario']; ?>" 
                               onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                Eliminar
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
