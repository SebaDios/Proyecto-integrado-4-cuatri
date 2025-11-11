# Sistema de Gestión - Antojitos ALKASE

Sistema web para la gestión de inventario y ventas de Antojitos ALKASE.

##  Características

-  Sistema de autenticación con roles (Admin/Usuario)
-  Gestión completa de usuarios (solo Admin)
-  Gestión de inventario con alertas de stock
-  Punto de venta integrado
-  Historial de ventas y reportes
-  Control de movimientos de inventario

##  Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior (o MariaDB)
- Servidor web (Apache/Nginx) o XAMPP/WAMP
- Extensiones PHP: PDO, PDO_MySQL

##  Instalación

### 1. Clonar o copiar el proyecto

Coloca el proyecto en la carpeta `htdocs` de XAMPP o en el directorio web de tu servidor.

### 2. Crear la base de datos

```sql
CREATE DATABASE antojitos_alkase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importar la estructura

```bash
mysql -u root -p antojitos_alkase < sql/database.sql
```

O desde phpMyAdmin:
- Selecciona la base de datos `antojitos_alkase`
- Ve a la pestaña "Importar"
- Selecciona el archivo `sql/database.sql`

### 4. Crear usuario administrador

```bash
mysql -u root -p antojitos_alkase < sql/insert_admin.sql
```

O ejecuta manualmente:
```sql
INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol, activo)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Administrador del Sistema',
    'admin@antojitosalkase.com',
    'Admin',
    1
);
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

 **IMPORTANTE:** Cambia la contraseña después del primer login.

### 5. Configurar la conexión

Edita el archivo `config/database.php` con tus credenciales:

```php
private $host = 'localhost';
private $db_name = 'antojitos_alkase';
private $username = 'root';
private $password = ''; // Tu contraseña de MySQL
```

### 6. Acceder al sistema

Abre tu navegador y ve a:
```
http://localhost/ProyectoIntegrado/
```

O si usas un servidor virtual:
```
http://antojitos-alkase.local/
```

##  Roles y Permisos

### Administrador (Admin)
-  Gestión completa de usuarios (crear, editar, eliminar)
-  Gestión completa de inventario
-  Registrar ventas
-  Ver y modificar todas las ventas
-  Acceso a todos los reportes

### Usuario
-  Registrar ventas
-  Ver solo sus propias ventas (sin modificar)
-  Ver reportes personales

##  Estructura del Proyecto

Ver `ESTRUCTURA_PROYECTO.md` para detalles completos.

##  Seguridad

- Contraseñas hasheadas con `password_hash()`
- Uso de prepared statements (PDO)
- Validación de permisos en cada controlador
- Sanitización de inputs
- Regeneración de ID de sesión

##  Notas de Desarrollo

- El sistema usa el patrón MVC (Modelo-Vista-Controlador)
- Las rutas son relativas, asegúrate de mantener la estructura de carpetas
- Los usuarios no se eliminan físicamente, se desactivan (`activo = 0`)

##  Solución de Problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo
- Revisa las credenciales en `config/database.php`
- Asegúrate de que la base de datos existe

### Error 404 en las rutas
- Verifica que estás accediendo desde la raíz del proyecto
- Revisa la configuración de tu servidor web

### Problemas de sesión
- Verifica que las cookies estén habilitadas
- Revisa los permisos de escritura en la carpeta de sesiones de PHP

##  Soporte

Para más información sobre la estructura y desarrollo, consulta `ESTRUCTURA_PROYECTO.md`.

