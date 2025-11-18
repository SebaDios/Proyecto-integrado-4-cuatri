<?php
require_once '../../inc/session.php';
requireAdmin(); // Solo admin puede acceder

require_once '../../models/product.php';
require_once '../../inc/functions.php';

$productModel = new Product();

// Configuración de paginación
$perPage = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Manejar búsqueda
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearching = !empty($searchTerm);

if ($isSearching) {
    // Búsqueda paginada
    $totalProducts = $productModel->getSearchCount($searchTerm);
    $totalPages = max(1, ceil($totalProducts / $perPage));
    $page = min($page, $totalPages); // Asegurar que la página no exceda el total
    $products = $productModel->searchPaginated($searchTerm, $page, $perPage);
} else {
    // Listado paginado
    $totalProducts = $productModel->getTotalCount();
    $totalPages = max(1, ceil($totalProducts / $perPage));
    $page = min($page, $totalPages); // Asegurar que la página no exceda el total
    $products = $productModel->getPaginated($page, $perPage);
}

// Obtener productos próximos a vencer (3 días)
$expiringProducts = $productModel->getExpiringSoon(3);

// Obtener productos con stock bajo
$lowStockProducts = $productModel->getLowStock();

$message = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $message = 'Producto creado exitosamente';
            break;
        case 'updated':
            $message = 'Producto actualizado exitosamente';
            break;
        case 'deleted':
            $message = 'Producto eliminado exitosamente';
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
    <title>Gestionar Productos - Antojitos ALKASE</title>
    <link rel="stylesheet" href="../../assets/css.css">
</head>
<body>
    <header>
        <h1>Gestión de Productos</h1>
        <a href="../dashboard.php">← Volver al Dashboard</a>
    </header>

    <main>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($lowStockProducts)): ?>
            <div class="alert-warning" style="background-color: #f8d7da; border: 2px solid #dc3545; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
                <h3 style="color: #721c24; margin-top: 0; display: flex; align-items: center;">
                    <span style="font-size: 24px; margin-right: 10px;"></span>
                    Alerta: Productos con Stock Bajo
                </h3>
                <p style="color: #721c24; margin-bottom: 10px;">
                    Los siguientes productos tienen stock igual o por debajo del mínimo establecido:
                </p>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($lowStockProducts as $lowStock): 
                        $diferencia_stock = $lowStock['stock_actual'] - $lowStock['stock_minimo'];
                        
                        // Determinar color según la urgencia
                        if ($lowStock['stock_actual'] == 0) {
                            $color_alerta = '#dc3545';
                            $estado_texto = 'SIN STOCK';
                        } elseif ($lowStock['stock_actual'] < $lowStock['stock_minimo'] / 2) {
                            $color_alerta = '#dc3545';
                            $estado_texto = 'CRÍTICO';
                        } elseif ($lowStock['stock_actual'] <= $lowStock['stock_minimo']) {
                            $color_alerta = '#fd7e14';
                            $estado_texto = 'BAJO';
                        } else {
                            $color_alerta = '#ffc107';
                            $estado_texto = 'ATENCIÓN';
                        }
                    ?>
                        <li style="background-color: #fff; padding: 10px; margin-bottom: 8px; border-left: 4px solid <?php echo $color_alerta; ?>; border-radius: 3px;">
                            <strong><?php echo htmlspecialchars($lowStock['nombre']); ?></strong>
                            <span style="color: <?php echo $color_alerta; ?>; font-weight: bold; margin-left: 10px;">
                                Stock: <?php echo htmlspecialchars($lowStock['stock_actual']); ?> unidades
                                (Mínimo: <?php echo htmlspecialchars($lowStock['stock_minimo']); ?>)
                                - <span style="text-transform: uppercase;"><?php echo $estado_texto; ?></span>
                            </span>
                            <?php if ($diferencia_stock < 0): ?>
                                <span style="margin-left: 10px; color: #dc3545; font-weight: bold;">
                                    Faltan <?php echo abs($diferencia_stock); ?> unidades
                                </span>
                            <?php elseif ($diferencia_stock == 0): ?>
                                <span style="margin-left: 10px; color: #fd7e14; font-weight: bold;">
                                    En el límite mínimo
                                </span>
                            <?php endif; ?>
                            <a href="edit.php?id=<?php echo $lowStock['id_producto']; ?>" 
                               style="float: right; color: #007bff; text-decoration: none;">
                                Editar →
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($expiringProducts)): ?>
            <div class="alert-warning" style="background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
                <h3 style="color: #856404; margin-top: 0; display: flex; align-items: center;">
                    <span style="font-size: 24px; margin-right: 10px;"></span>
                Alerta: Productos Próximos a Vencer (3 días o menos)
                </h3>
                <p style="color: #856404; margin-bottom: 10px;">
                    Los siguientes productos perecederos están próximos a vencer:
                </p>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($expiringProducts as $expiring): 
                        $fecha_vencimiento = new DateTime($expiring['fecha_vencimiento']);
                        $hoy = new DateTime();
                        $hoy->setTime(0, 0, 0);
                        $fecha_vencimiento->setTime(0, 0, 0);
                        
                        // Calcular días restantes
                        if ($fecha_vencimiento < $hoy) {
                            // Producto vencido
                            $dias_restantes = 0;
                            $dias_texto = 'VENCIDO';
                            $color_alerta = '#dc3545';
                        } else {
                            $diferencia = $hoy->diff($fecha_vencimiento);
                            $dias_restantes = $diferencia->days;
                            
                            if ($dias_restantes == 0) {
                                $dias_texto = 'HOY';
                                $color_alerta = '#dc3545';
                            } elseif ($dias_restantes == 1) {
                                $dias_texto = '1 día';
                                $color_alerta = '#fd7e14';
                            } else {
                                $dias_texto = $dias_restantes . ' días';
                                $color_alerta = '#ffc107';
                            }
                        }
                    ?>
                        <li style="background-color: #fff; padding: 10px; margin-bottom: 8px; border-left: 4px solid <?php echo $color_alerta; ?>; border-radius: 3px;">
                            <strong><?php echo htmlspecialchars($expiring['nombre']); ?></strong>
                            <span style="color: <?php echo $color_alerta; ?>; font-weight: bold; margin-left: 10px;">
                                 Vence en: <?php echo $dias_texto; ?> 
                                (<?php echo date('d/m/Y', strtotime($expiring['fecha_vencimiento'])); ?>)
                            </span>
                            <span style="margin-left: 10px; color: #666;">
                                Stock: <?php echo htmlspecialchars($expiring['stock_actual']); ?> unidades
                            </span>
                            <a href="edit.php?id=<?php echo $expiring['id_producto']; ?>" 
                               style="float: right; color: #007bff; text-decoration: none;">
                                Editar →
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="create.php" class="btn-primary">+ Crear Nuevo Producto</a>
            
            <!-- Barra de búsqueda -->
            <form method="GET" action="" style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 500px; margin-left: 20px;">
                <?php if (!$isSearching && $page > 1): ?>
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                <?php endif; ?>
                <input type="text" 
                       name="search" 
                       placeholder="Buscar productos" 
                       value="<?php echo htmlspecialchars($searchTerm); ?>"
                       style="flex: 1; padding: 10px; border: 2px solid #ddd; border-radius: 25px; font-size: 14px; outline: none; transition: border-color 0.3s;"
                       onfocus="this.style.borderColor='#007bff';"
                       onblur="this.style.borderColor='#ddd';">
                <button type="submit" 
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 25px; cursor: pointer; font-size: 14px; transition: background-color 0.3s;"
                        onmouseover="this.style.backgroundColor='#0056b3';"
                        onmouseout="this.style.backgroundColor='#007bff';">
                     Buscar
                </button>
                <?php if ($isSearching): ?>
                    <a href="index.php" 
                       style="padding: 10px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 25px; font-size: 14px; transition: background-color 0.3s;"
                       onmouseover="this.style.backgroundColor='#5a6268';"
                       onmouseout="this.style.backgroundColor='#6c757d';">
                        ✕ Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($isSearching): ?>
            <div style="margin-bottom: 15px; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #007bff; border-radius: 3px;">
                <strong>Resultados de búsqueda:</strong> 
                <?php echo $totalProducts; ?> producto(s) encontrado(s) para "<em><?php echo htmlspecialchars($searchTerm); ?></em>"
                <?php if ($totalPages > 1): ?>
                    - Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 15px; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #007bff; border-radius: 3px;">
                <strong>Total de productos:</strong> <?php echo $totalProducts; ?>
                <?php if ($totalPages > 1): ?>
                    - Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        // Función para resaltar términos de búsqueda
        function highlightSearch($text, $searchTerm) {
            if (empty($searchTerm) || empty($text)) {
                return htmlspecialchars($text);
            }
            $pattern = '/(' . preg_quote($searchTerm, '/') . ')/i';
            return preg_replace($pattern, '<mark style="background-color: #ffeb3b; padding: 2px 4px; border-radius: 3px;">$1</mark>', htmlspecialchars($text));
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio Venta</th>
                    <th>Precio Compra</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Perecedero</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="12" style="text-align: center; padding: 30px;">
                        <?php if ($isSearching): ?>
                            <p style="color: #666; font-size: 16px;">
                                 No se encontraron productos que coincidan con "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                            </p>
                            <p style="color: #999; font-size: 14px; margin-top: 10px;">
                                Intenta con otros términos de búsqueda o <a href="index.php" style="color: #007bff;">ver todos los productos</a>
                            </p>
                        <?php else: ?>
                            No hay productos registrados
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id_producto']); ?></td>
                    <td><?php echo $isSearching ? highlightSearch($product['nombre'], $searchTerm) : htmlspecialchars($product['nombre']); ?></td>
                    <td><?php 
                        $descripcion = substr($product['descripcion'] ?? '', 0, 50) . (strlen($product['descripcion'] ?? '') > 50 ? '...' : '');
                        echo $isSearching ? highlightSearch($descripcion, $searchTerm) : htmlspecialchars($descripcion);
                    ?></td>
                    <td><?php echo $isSearching ? highlightSearch($product['categoria'] ?? 'Sin categoría', $searchTerm) : htmlspecialchars($product['categoria'] ?? 'Sin categoría'); ?></td>
                    <td>$<?php echo number_format($product['precio_venta'], 2); ?></td>
                    <td>$<?php echo $product['precio_compra'] ? number_format($product['precio_compra'], 2) : 'N/A'; ?></td>
                    <td>
                        <span style="color: <?php echo $product['stock_actual'] <= $product['stock_minimo'] ? 'red' : 'green'; ?>;">
                            <?php echo htmlspecialchars($product['stock_actual']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($product['stock_minimo']); ?></td>
                    <td><?php echo $product['es_perecedero'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo $product['fecha_vencimiento'] ? date('d/m/Y', strtotime($product['fecha_vencimiento'])) : 'N/A'; ?></td>
                    <td><?php echo $product['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $product['id_producto']; ?>">Editar</a>
                        <a href="delete.php?id=<?php echo $product['id_producto']; ?>" 
                           onclick="return confirm('¿Está seguro de eliminar este producto?')">
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php
        // Mostrar paginador si hay más de una página
        if ($totalPages > 1) {
            // Construir URL base para paginación
            $urlParams = [];
            if ($isSearching) {
                $urlParams[] = 'search=' . urlencode($searchTerm);
            }
            $urlBase = 'index.php?' . (!empty($urlParams) ? implode('&', $urlParams) . '&' : '') . 'page=';
            
            echo paginador_tablas($page, $totalPages, $urlBase);
        }
        ?>
    </main>
</body>
</html>

