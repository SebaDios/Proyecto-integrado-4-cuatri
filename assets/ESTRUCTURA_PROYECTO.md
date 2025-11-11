# Estructura del Proyecto - Sistema de Gestión Antojitos ALKASE

## Organización de Carpetas

```
ProyectoIntegrado/
│
├── assets/                    # Recursos estáticos
│   ├── css.css               # Estilos globales
│   └── js.js                 # JavaScript global
│
├── config/                    # Configuración del sistema
│   └── database.php          # Clase de conexión a MySQL
│
├── controllers/               # Controladores (lógica de negocio)
│   ├── auth.php              # Autenticación (login/logout)
│   ├── users.php             # Gestión de usuarios
│   ├── inventory.php         # Gestión de inventario
│   ├── sales.php             # Gestión de ventas
│   └── logout.php            # Cierre de sesión
│
├── models/                    # Modelos (acceso a datos)
│   ├── user.php              # Modelo de usuarios
│   ├── product.php           # Modelo de productos
│   └── sale.php              # Modelo de ventas
│
├── views/                     # Vistas (interfaz de usuario)
│   ├── login.php             # Página de inicio de sesión
│   ├── dashboard.php         # Panel principal
│   ├── inventario.php        # Gestión de inventario
│   ├── ventas.php            # Punto de venta
│   ├── usuario.php           # Vista de usuario
│   │
│   └── users/                 # Vistas de gestión de usuarios
│       ├── index.php         # Lista de usuarios
│       ├── create.php        # Crear usuario
│       ├── edit.php          # Editar usuario
│       └── delete.php        # Eliminar usuario
│
├── inc/                       # Archivos de inclusión
│   ├── session.php           # Funciones de sesión y permisos
│   └── functions.php         # Funciones auxiliares
│
├── sql/                       # Scripts de base de datos
│   └── database.sql          # Estructura de tablas
│
├── public/                    # Archivos públicos (si se usa .htaccess)
│
└── index.php                  # Punto de entrada principal
```

##  Diseño de Base de Datos

### Tablas Principales

#### 1. **usuarios**
Almacena información de usuarios del sistema con roles y autenticación.

**Campos:**
- `id_usuario` (PK): Identificador único
- `nombre_usuario`: Usuario para login (único)
- `password_hash`: Contraseña hasheada con password_hash()
- `nombre_completo`: Nombre real del usuario
- `rol`: ENUM('Admin', 'Usuario') - Nivel de acceso
- `email`: Correo electrónico
- `fecha_creacion`: Timestamp de creación
- `activo`: BOOLEAN - Estado del usuario

#### 2. **productos**
Catálogo de productos con control de stock y alertas.

**Campos:**
- `id_producto` (PK): Identificador único
- `nombre`: Nombre del producto
- `descripcion`: Descripción detallada
- `categoria`: Categoría del producto
- `precio_venta`: Precio de venta al público
- `precio_compra`: Precio de compra (costo)
- `stock_actual`: Cantidad disponible
- `stock_minimo`: Umbral para alertas
- `es_perecedero`: BOOLEAN - Si requiere control de vencimiento
- `fecha_vencimiento`: Fecha de vencimiento (si aplica)
- `ultima_actualizacion`: Timestamp de última modificación
- `activo`: BOOLEAN - Estado del producto

#### 3. **ventas**
Registro de transacciones de venta.

**Campos:**
- `id_venta` (PK): Identificador único
- `id_usuario` (FK): Usuario que realizó la venta
- `fecha_venta`: Timestamp de la venta
- `total`: Monto total de la venta
- `metodo_pago`: ENUM('Efectivo', 'Tarjeta', 'Transferencia')
- `estado`: ENUM('Completada', 'Cancelada')

#### 4. **detalle_ventas**
Detalle de productos vendidos en cada transacción.

**Campos:**
- `id_detalle` (PK): Identificador único
- `id_venta` (FK): Venta a la que pertenece
- `id_producto` (FK): Producto vendido
- `cantidad`: Cantidad vendida
- `precio_unitario`: Precio al momento de la venta
- `subtotal`: Cantidad × precio_unitario

#### 5. **movimientos_inventario**
Historial de entradas, salidas y ajustes de inventario.

**Campos:**
- `id_movimiento` (PK): Identificador único
- `id_producto` (FK): Producto afectado
- `tipo_movimiento`: ENUM('Entrada', 'Salida', 'Ajuste')
- `cantidad`: Cantidad del movimiento
- `motivo`: Razón del movimiento
- `id_usuario` (FK): Usuario que realizó el movimiento
- `fecha_movimiento`: Timestamp del movimiento

### Relaciones

```
usuarios (1) ──→ (N) ventas
usuarios (1) ──→ (N) movimientos_inventario
productos (1) ──→ (N) detalle_ventas
productos (1) ──→ (N) movimientos_inventario
ventas (1) ──→ (N) detalle_ventas
```

##  Sistema de Roles y Permisos

### Roles

1. **Admin**: Acceso completo al sistema
   - Gestión de usuarios (CRUD completo)
   - Gestión de inventario (CRUD completo)
   - Registrar ventas
   - Ver y modificar todas las ventas
   - Ver reportes completos

2. **Usuario**: Acceso limitado
   - Registrar ventas
   - Ver solo sus propias ventas (sin modificar)
   - Ver reportes personales

### Implementación de Permisos

#### 1. Verificación de Sesión (`inc/session.php`)

```php
// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Verificar si es administrador
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'Admin';
}

// Requerir login obligatorio
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Requerir permisos de administrador
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}
```

#### 2. Uso en Controladores

**Ejemplo en `controllers/users.php`:**
```php
require_once '../inc/session.php';
requireAdmin(); // Solo Admin puede acceder
```

**Ejemplo en `controllers/sales.php`:**
```php
require_once '../inc/session.php';
requireLogin(); // Cualquier usuario autenticado puede acceder

// Dentro de las funciones:
if (!isAdmin()) {
    // Usuario solo ve sus propias ventas
    $filter = " WHERE id_usuario = " . $_SESSION['user_id'];
}
```

#### 3. Uso en Vistas

**Ejemplo en `views/dashboard.php`:**
```php
<?php if (isAdmin()): ?>
    <a href="users/index.php">Gestionar Usuarios</a>
<?php endif; ?>
```

##  Flujo de Autenticación

1. Usuario accede a `index.php` o `views/login.php`
2. Ingresa credenciales (usuario y contraseña)
3. `controllers/auth.php` valida contra la base de datos
4. Si es válido, se crea sesión con:
   - `$_SESSION['user_id']`
   - `$_SESSION['user_name']`
   - `$_SESSION['user_role']`
   - `$_SESSION['full_name']`
5. Redirección a `views/dashboard.php`
6. Cada página verifica permisos antes de mostrar contenido

##  Convenciones de Código

### Nomenclatura
- **Archivos**: snake_case (ej: `auth.php`, `user_model.php`)
- **Clases**: PascalCase (ej: `AuthController`, `Database`)
- **Funciones**: camelCase (ej: `isLoggedIn()`, `requireAdmin()`)
- **Variables**: snake_case (ej: `$user_id`, `$nombre_usuario`)

### Seguridad
-  Uso de PDO con prepared statements
-  Hash de contraseñas con `password_hash()`
-  Sanitización de inputs con `htmlspecialchars()`
-  Regeneración de ID de sesión
-  Validación de permisos en cada controlador

### Estructura de Controladores
```php
<?php
require_once '../config/database.php';
require_once '../inc/session.php';

class NombreController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Métodos del controlador
}
?>
```

##  Instalación y Configuración

1. **Crear la base de datos:**
   ```sql
   CREATE DATABASE antojitos_alkase;
   ```

2. **Importar estructura:**
   ```bash
   mysql -u root -p antojitos_alkase < sql/database.sql
   ```

3. **Configurar conexión:**
   Editar `config/database.php` con tus credenciales:
   ```php
   private $host = 'localhost';
   private $db_name = 'antojitos_alkase';
   private $username = 'root';
   private $password = '';
   ```

4. **Crear usuario administrador inicial:**
   ```sql
   INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, rol)
   VALUES ('admin', '$2y$10$...', 'Administrador', 'Admin');
   ```
   (Usar `password_hash('tu_contraseña', PASSWORD_DEFAULT)` para generar el hash)

##  Próximos Pasos

1. (hecho) Estructura de carpetas
2. (hecho) Diseño de base de datos
3. (hecho) Sistema de autenticación
4. (hecho) Módulo de gestión de usuarios
5. (pendiente) Módulo de gestión de inventario
6. (pendiente) Módulo de punto de venta
7. (pendiente) Sistema de alertas de stock
8. (pendiente) Reportes y estadísticas

