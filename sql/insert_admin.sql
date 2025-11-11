-- Script para crear el usuario administrador inicial
-- IMPORTANTE: Cambiar la contraseña después del primer login

-- INSTRUCCIONES:
-- 1. Para generar un hash de contraseña, usa el script: public/generate_password.php
-- 2. O ejecuta en PHP: password_hash('tu_contraseña', PASSWORD_DEFAULT)
-- 3. Reemplaza el hash en este archivo antes de ejecutarlo

-- Contraseña por defecto: 'admin123'
-- NOTA: El hash de abajo es un ejemplo. Genera uno nuevo para mayor seguridad.
-- Hash generado con: password_hash('admin123', PASSWORD_DEFAULT)

-- Usuario Administrador
INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol, activo)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123 (CAMBIAR)
    'Administrador del Sistema',
    'admin@antojitosalkase.com',
    'Admin',
    1
);

-- Usuario de prueba (rol Usuario)
-- Contraseña: 'usuario123'
-- NOTA: Este hash es solo para pruebas. Genera uno nuevo en producción.
INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol, activo)
VALUES (
    'usuario',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- usuario123 (CAMBIAR)
    'Usuario de Prueba',
    'usuario@antojitosalkase.com',
    'Usuario',
    1
);

-- ALTERNATIVA: Ejecutar desde PHP para generar hash único
-- <?php
-- $password = 'admin123';
-- $hash = password_hash($password, PASSWORD_DEFAULT);
-- echo "INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol, activo) VALUES ('admin', '$hash', 'Administrador', 'admin@ejemplo.com', 'Admin', 1);";
-- ?>

