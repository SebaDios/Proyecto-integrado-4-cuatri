<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/product.php';

$productModel = new Product();
$categories = $productModel->getCategories();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];
$product = $productModel->getById($product_id);

if (!$product) {
    header('Location: index.php?msg=error');
    exit();
}

$error = '';

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
    $activo = isset($_POST['activo']) ? 1 : 0;
    
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
        // Verificar si el nombre ya existe en el inventario (excluyendo el producto actual)
        if ($productModel->nameExists($nombre, $product_id)) {
            $error = 'Ya existe otro producto con ese nombre en el inventario. Por favor, verifica el nombre o edita el producto existente.';
        } else {
            if ($productModel->update($product_id, $nombre, $descripcion, $categoria, $precio_venta, 
                                     $precio_compra, $stock_actual, $stock_minimo, $es_perecedero, 
                                     $fecha_vencimiento, $activo)) {
                header('Location: index.php?msg=updated');
                exit();
            } else {
                $error = 'Error al actualizar el producto';
            }
        }
    }
    
    // Actualizar datos del producto para mostrar en el formulario
    $product = [
        'id_producto' => $product_id,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'categoria' => $categoria,
        'precio_venta' => $precio_venta,
        'precio_compra' => $precio_compra,
        'stock_actual' => $stock_actual,
        'stock_minimo' => $stock_minimo,
        'es_perecedero' => $es_perecedero,
        'fecha_vencimiento' => $fecha_vencimiento,
        'activo' => $activo
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <?php include_once '../../inc/header.php'; ?>
    
    <div style="padding: 1rem 2rem; background: #efebe0; border-bottom: 1px solid #907952;">
        <a href="index.php" class="btn-secondary">← Volver a Productos</a>
    </div>

    <main>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre del Producto: *</label>
                <input type="text" name="nombre" required 
                       value="<?php echo htmlspecialchars($product['nombre']); ?>">
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($product['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Categoría:</label>
                <input type="text" name="categoria" list="categorias"
                       value="<?php echo htmlspecialchars($product['categoria'] ?? ''); ?>">
                <datalist id="categorias">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label>Precio de Venta: *</label>
                <input type="number" name="precio_venta" step="0.01" min="0" required 
                       value="<?php echo htmlspecialchars($product['precio_venta']); ?>">
            </div>

            <div class="form-group">
                <label>Precio de Compra:</label>
                <input type="number" name="precio_compra" step="0.01" min="0" 
                       value="<?php echo $product['precio_compra'] ? htmlspecialchars($product['precio_compra']) : ''; ?>">
                <small>Opcional - Costo del producto</small>
            </div>

            <div class="form-group">
                <label>Stock Actual: *</label>
                <input type="number" name="stock_actual" min="0" required 
                       value="<?php echo htmlspecialchars($product['stock_actual']); ?>">
            </div>

            <div class="form-group">
                <label>Stock Mínimo: *</label>
                <input type="number" name="stock_minimo" min="0" required 
                       value="<?php echo htmlspecialchars($product['stock_minimo']); ?>">
                <small>Cantidad mínima antes de alerta de stock bajo</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="es_perecedero" <?php echo $product['es_perecedero'] ? 'checked' : ''; ?>>
                    Producto Perecedero
                </label>
            </div>

            <div class="form-group" id="fecha-vencimiento-group" style="display: <?php echo $product['es_perecedero'] ? 'block' : 'none'; ?>;">
                <label>Fecha de Vencimiento:</label>
                <input type="date" name="fecha_vencimiento" 
                       value="<?php echo $product['fecha_vencimiento'] ? date('Y-m-d', strtotime($product['fecha_vencimiento'])) : ''; ?>">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="activo" <?php echo $product['activo'] ? 'checked' : ''; ?>>
                    Producto Activo
                </label>
            </div>

            <div class="form-actions">
                <button type="submit">Actualizar Producto</button>
                <a href="index.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Mostrar/ocultar campo de fecha de vencimiento según checkbox
        document.querySelector('input[name="es_perecedero"]').addEventListener('change', function() {
            document.getElementById('fecha-vencimiento-group').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>

