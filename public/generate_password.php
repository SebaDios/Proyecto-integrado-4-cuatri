<?php
/**
 * Script auxiliar para generar hash de contraseñas
 * USO: Accede a este archivo desde el navegador y proporciona la contraseña
 * 
 * IMPORTANTE: Elimina este archivo después de usarlo por seguridad
 */

// Solo permitir acceso local
if ($_SERVER['SERVER_ADDR'] !== $_SERVER['REMOTE_ADDR'] && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die('Acceso denegado');
}

$password_hash = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Hash de Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            font-size: 14px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .hash {
            word-break: break-all;
            font-family: monospace;
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Generador de Hash de Contraseña</h1>
    
    <div class="warning">
        <strong>⚠️ Advertencia:</strong> Elimina este archivo después de usarlo por seguridad.
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="text" id="password" name="password" required 
                   value="<?php echo htmlspecialchars($password); ?>" 
                   placeholder="Ingresa la contraseña">
        </div>
        <button type="submit">Generar Hash</button>
    </form>
    
    <?php if ($password_hash): ?>
    <div class="result">
        <h3>Hash generado:</h3>
        <div class="hash"><?php echo htmlspecialchars($password_hash); ?></div>
        <p><strong>SQL para insertar:</strong></p>
        <div class="hash">
INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol, activo)
VALUES (
    'tu_usuario',
    '<?php echo htmlspecialchars($password_hash); ?>',
    'Nombre Completo',
    'email@ejemplo.com',
    'Admin',
    1
);
        </div>
    </div>
    <?php endif; ?>
</body>
</html>

