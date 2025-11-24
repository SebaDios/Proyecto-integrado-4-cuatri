<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}
require_once BASE_PATH . '/inc/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    header('Location: views/dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/controllers/auth.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $auth = new AuthController();
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        header('Location: views/dashboard.php');
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Antojitos ALKASE</title>
    <link rel="stylesheet" href="assets/css.css">
</head>
<body>
    <div class="login-container">
        <h1>Antojitos ALKASE</h1>
        <h2>Iniciar Sesión</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div>
                <label>Usuario:</label>
                <input type="text" name="username" required>
            </div>
            
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
