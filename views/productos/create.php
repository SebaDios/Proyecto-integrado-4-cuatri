<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/product.php';

$productModel = new Product();
$categories = $productModel->getCategories();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = trim($_POST['categoria']);
    $precio_venta = $_POST['precio_venta'];
    $precio_compra = $_POST['precio_compra'] ? $_POST['precio_compra'] : null;
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $es_perecedero = isset($_POST['es_perecedero']) ? 1 : 0;
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
    
    // Validaciones
    if (empty($nombre) || empty($precio_venta) || !isset($stock_actual) || !isset($stock_minimo)) {
        $error = 'Los campos obligatorios deben ser completados (Nombre, Precio Venta, Stock Actual, Stock Mínimo)';
    } elseif ($precio_venta < 0) {
        $error = 'El precio de venta no puede ser negativo';
    } elseif ($precio_compra !== null && $precio_compra < 0) {
        $error = 'El precio de compra no puede ser negativo';
    } elseif ($stock_actual < 0) {
        $error = 'El stock actual no puede ser negativo';
    } elseif ($stock_minimo < 0) {
        $error = 'El stock mínimo no puede ser negativo';
    } else {
        // Verificar si el producto ya existe
        if ($productModel->nameExists($nombre)) {
            $error = 'Ya existe un producto con ese nombre';
        } else {
            if ($productModel->create($nombre, $descripcion, $categoria, $precio_venta, $precio_compra, 
                                     $stock_actual, $stock_minimo, $es_perecedero, $fecha_vencimiento)) {
                header('Location: index.php?msg=created');
                exit();
            } else {
                $error = 'Error al crear el producto';
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
    <title>Crear Producto - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <header>
        <h1>Crear Nuevo Producto</h1>
        <a href="index.php">← Volver a Productos</a>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre del Producto: *</label>
                <input type="text" name="nombre" required 
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="3"><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label>Categoría:</label>
                <input type="text" name="categoria" list="categorias"
                       value="<?php echo isset($_POST['categoria']) ? htmlspecialchars($_POST['categoria']) : ''; ?>">
                <datalist id="categorias">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label>Precio de Venta: *</label>
                <input type="number" name="precio_venta" step="0.01" min="0" required 
                       value="<?php echo isset($_POST['precio_venta']) ? htmlspecialchars($_POST['precio_venta']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Precio de Compra:</label>
                <input type="number" name="precio_compra" step="0.01" min="0" 
                       value="<?php echo isset($_POST['precio_compra']) ? htmlspecialchars($_POST['precio_compra']) : ''; ?>">
                <small>Opcional - Costo del producto</small>
            </div>

            <div class="form-group">
                <label>Stock Actual: *</label>
                <input type="number" name="stock_actual" min="0" required 
                       value="<?php echo isset($_POST['stock_actual']) ? htmlspecialchars($_POST['stock_actual']) : '0'; ?>">
            </div>

            <div class="form-group">
                <label>Stock Mínimo: *</label>
                <input type="number" name="stock_minimo" min="0" required 
                       value="<?php echo isset($_POST['stock_minimo']) ? htmlspecialchars($_POST['stock_minimo']) : '5'; ?>">
                <small>Cantidad mínima antes de alerta de stock bajo</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="es_perecedero" <?php echo (isset($_POST['es_perecedero'])) ? 'checked' : ''; ?>>
                    Producto Perecedero
                </label>
            </div>

            <div class="form-group" id="fecha-vencimiento-group" style="display: none;">
                <label>Fecha de Vencimiento:</label>
                <input type="date" name="fecha_vencimiento" 
                       value="<?php echo isset($_POST['fecha_vencimiento']) ? htmlspecialchars($_POST['fecha_vencimiento']) : ''; ?>">
            </div>

            <div class="form-actions">
                <button type="submit">Crear Producto</button>
                <a href="index.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Mostrar/ocultar campo de fecha de vencimiento según checkbox
        document.querySelector('input[name="es_perecedero"]').addEventListener('change', function() {
            document.getElementById('fecha-vencimiento-group').style.display = this.checked ? 'block' : 'none';
        });
        
        // Verificar estado inicial
        if (document.querySelector('input[name="es_perecedero"]').checked) {
            document.getElementById('fecha-vencimiento-group').style.display = 'block';
        }
    </script>
</body>
</html>

