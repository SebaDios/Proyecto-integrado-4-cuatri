# Sistema de Roles y Permisos - Antojitos ALKASE

##  Descripción General

El sistema implementa un control de acceso basado en roles (RBAC - Role-Based Access Control) con dos niveles de usuario: **Admin** y **Usuario**.

##  Roles del Sistema

### 1. Administrador (Admin)

**Permisos completos:**
-  **Gestión de Usuarios**: Crear, leer, actualizar y eliminar usuarios
-  **Gestión de Inventario**: CRUD completo de productos
-  **Registrar Ventas**: Puede crear nuevas ventas
-  **Ver Todas las Ventas**: Acceso a todas las ventas del sistema
-  **Modificar Ventas**: Puede editar o cancelar cualquier venta
-  **Reportes Completos**: Acceso a estadísticas globales
-  **Movimientos de Inventario**: Registrar entradas, salidas y ajustes

### 2. Usuario

**Permisos limitados:**
-  **Registrar Ventas**: Puede crear nuevas ventas
-  **Ver Sus Ventas**: Solo puede ver las ventas que él mismo registró
-  **Modificar Ventas**: No puede editar o eliminar ventas (ni propias ni ajenas)
-  **Gestión de Usuarios**: Sin acceso
-  **Gestión de Inventario**: Sin acceso
-  **Reportes Personales**: Solo sus propias estadísticas

##  Implementación Técnica

### 1. Almacenamiento en Base de Datos

El rol se almacena en la tabla `usuarios`:

```sql
rol ENUM('Admin', 'Usuario') NOT NULL DEFAULT 'Usuario'
```

### 2. Sesión de Usuario

Cuando un usuario inicia sesión, se guardan en `$_SESSION`:

```php
$_SESSION['user_id']      // ID del usuario
$_SESSION['user_name']    // Nombre de usuario
$_SESSION['user_role']    // 'Admin' o 'Usuario'
$_SESSION['full_name']    // Nombre completo
```

### 3. Funciones de Verificación (`inc/session.php`)

#### `isLoggedIn()`
Verifica si el usuario está autenticado:
```php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}
```

#### `isAdmin()`
Verifica si el usuario es administrador:
```php
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'Admin';
}
```

#### `requireLogin()`
Fuerza la autenticación. Si no está logueado, redirige al login:
```php
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
```

#### `requireAdmin()`
Fuerza permisos de administrador. Si no es admin, redirige al dashboard:
```php
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}
```

##  Uso en Controladores

### Ejemplo 1: Controlador Solo para Admin

```php
<?php
// controllers/users.php
require_once '../inc/session.php';
requireAdmin(); // Solo Admin puede acceder

// Si llegamos aquí, el usuario es Admin
class UsersController {
    // ... código del controlador
}
?>
```

### Ejemplo 2: Controlador con Permisos Diferenciales

```php
<?php
// controllers/sales.php
require_once '../inc/session.php';
requireLogin(); // Cualquier usuario autenticado puede acceder

class SalesController {
    
    public function getAllSales() {
        if (isAdmin()) {
            // Admin ve todas las ventas
            $query = "SELECT * FROM ventas ORDER BY fecha_venta DESC";
        } else {
            // Usuario solo ve sus ventas
            $query = "SELECT * FROM ventas 
                     WHERE id_usuario = :user_id 
                     ORDER BY fecha_venta DESC";
        }
        // ... ejecutar query
    }
    
    public function updateSale($sale_id) {
        // Solo Admin puede modificar ventas
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'Sin permisos'];
        }
        // ... código de actualización
    }
}
?>
```

##  Uso en Vistas

### Mostrar/Ocultar Elementos según Rol

```php
<?php
require_once '../inc/session.php';
requireLogin();
?>

<!-- Menú de navegación -->
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="ventas.php">Ventas</a>
    
    <?php if (isAdmin()): ?>
        <!-- Solo visible para Admin -->
        <a href="users/index.php">Usuarios</a>
        <a href="inventario.php">Inventario</a>
    <?php endif; ?>
</nav>
```

### Botones Condicionales

```php
<!-- Botón de editar solo para Admin -->
<?php if (isAdmin()): ?>
    <a href="edit.php?id=<?php echo $venta['id']; ?>" class="btn btn-primary">
        Editar Venta
    </a>
<?php endif; ?>

<!-- Usuario solo puede ver -->
<?php if (!isAdmin()): ?>
    <span class="badge">Solo Lectura</span>
<?php endif; ?>
```

##  Filtrado de Datos por Rol

### En Modelos

```php
// models/sale.php
public function getAll($user_id = null) {
    $query = "SELECT v.*, u.nombre_completo 
              FROM ventas v
              JOIN usuarios u ON v.id_usuario = u.id_usuario";
    
    // Si se proporciona user_id, filtrar por usuario
    if ($user_id && !isAdmin()) {
        $query .= " WHERE v.id_usuario = :user_id";
    }
    
    $query .= " ORDER BY v.fecha_venta DESC";
    
    $stmt = $this->conn->prepare($query);
    
    if ($user_id && !isAdmin()) {
        $stmt->bindParam(':user_id', $user_id);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}
```

### En Controladores

```php
// controllers/sales.php
public function index() {
    requireLogin();
    
    $saleModel = new Sale();
    
    // Determinar qué ventas mostrar
    if (isAdmin()) {
        $sales = $saleModel->getAll(); // Todas las ventas
    } else {
        $sales = $saleModel->getAll($_SESSION['user_id']); // Solo del usuario
    }
    
    // Pasar a la vista
    include '../views/ventas/index.php';
}
```

##  Prevención de Acceso No Autorizado

### 1. Verificación en Cada Página

** INCORRECTO:**
```php
// Sin verificación - CUALQUIERA puede acceder
<?php
$users = $userModel->getAll();
?>
```

** CORRECTO:**
```php
<?php
require_once '../inc/session.php';
requireAdmin(); // Verificación obligatoria

$users = $userModel->getAll();
?>
```

### 2. Verificación en Formularios

```php
<?php
// Al procesar un formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    
    // Verificar permisos específicos
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        requireAdmin(); // Solo Admin puede eliminar
    }
    
    // Procesar formulario...
}
?>
```

### 3. Verificación en AJAX/API

```php
<?php
// api/delete_user.php
require_once '../inc/session.php';
requireAdmin(); // Verificar antes de procesar

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    // ... eliminar usuario
    echo json_encode(['success' => true]);
}
?>
```


##  Flujo de Verificación

```
Usuario accede a una página
        ↓
¿Está logueado? (requireLogin)
        ↓ NO
    Redirige a login.php
        ↓ SÍ
¿Requiere permisos de Admin? (requireAdmin)
        ↓ SÍ
    ¿Es Admin?
        ↓ NO
    Redirige a dashboard.php
        ↓ SÍ
    Acceso permitido
        ↓
    Renderizar contenido
```

##  Mejores Prácticas

1. **Siempre verificar permisos**: Nunca confíes solo en la UI, verifica en el backend
2. **Principio de menor privilegio**: Los usuarios solo tienen los permisos mínimos necesarios
3. **Logs de auditoría**: Considera registrar acciones importantes (crear, editar, eliminar)
4. **Validación doble**: Verifica permisos tanto en controladores como en modelos
5. **Mensajes claros**: Si un usuario intenta acceder sin permisos, muestra un mensaje claro

##  Extensión Futura

Si necesitas agregar más roles en el futuro:

1. Modificar el ENUM en la base de datos:
```sql
ALTER TABLE usuarios MODIFY rol ENUM('Admin', 'Usuario', 'Supervisor', 'Vendedor') NOT NULL;
```

2. Agregar funciones de verificación:
```php
function isSupervisor() {
    return isLoggedIn() && $_SESSION['user_role'] === 'Supervisor';
}

function requireSupervisor() {
    if (!isSupervisor()) {
        header('Location: dashboard.php');
        exit();
    }
}
```

