<?php
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../models/sale.php';

class SalesController {
    private $saleModel;
    
    public function __construct() {
        $this->saleModel = new Sale();
    }
    
    // Procesar una nueva orden
    public function processOrder() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        // Validar datos
        if (empty($data['nombre_cliente'])) {
            return ['success' => false, 'message' => 'El nombre del cliente es requerido'];
        }
        
        // Separar items en platillos y productos (bebidas)
        $platillosItems = isset($data['platillos']) && is_array($data['platillos']) ? $data['platillos'] : [];
        $productosItems = isset($data['productos']) && is_array($data['productos']) ? $data['productos'] : [];
        
        // Compatibilidad con formato anterior (solo platillos)
        if (empty($platillosItems) && empty($productosItems) && isset($data['items'])) {
            $platillosItems = $data['items'];
        }
        
        if (empty($platillosItems) && empty($productosItems)) {
            return ['success' => false, 'message' => 'Debe seleccionar al menos un platillo o bebida'];
        }
        
        if (empty($data['metodo_pago']) || !in_array($data['metodo_pago'], ['Efectivo', 'Tarjeta'])) {
            return ['success' => false, 'message' => 'Método de pago inválido'];
        }
        
        // Validar tipo de servicio
        if (empty($data['tipo_servicio']) || !in_array($data['tipo_servicio'], ['Para llevar', 'Comer aquí'])) {
            return ['success' => false, 'message' => 'Tipo de servicio inválido'];
        }
        
        // Calcular total y procesar platillos
        $total = 0;
        $platillos = [];
        
        foreach ($platillosItems as $item) {
            if (empty($item['id_platillo']) || empty($item['cantidad']) || $item['cantidad'] <= 0) {
                continue;
            }
            
            // Obtener información del platillo
            $platillo = $this->saleModel->getPlatilloById($item['id_platillo']);
            
            if (!$platillo) {
                return ['success' => false, 'message' => 'Platillo no encontrado: ' . $item['id_platillo']];
            }
            
            $precio = floatval($platillo['precio_platillo']);
            $cantidad = intval($item['cantidad']);
            $subtotal = $precio * $cantidad;
            
            $platillos[] = [
                'id_platillo' => $platillo['id_platillo'],
                'nombre' => $platillo['nombre_platillo'],
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
            
            $total += $subtotal;
        }
        
        // Calcular total y procesar productos (bebidas) con validación de stock
        $productos = [];
        
        foreach ($productosItems as $item) {
            if (empty($item['id_producto']) || empty($item['cantidad']) || $item['cantidad'] <= 0) {
                continue;
            }
            
            // Obtener información del producto
            $producto = $this->saleModel->getProductoById($item['id_producto']);
            
            if (!$producto) {
                return ['success' => false, 'message' => 'Producto no encontrado: ' . $item['id_producto']];
            }
            
            // Validar stock antes de agregar
            $cantidad = intval($item['cantidad']);
            $stockCheck = $this->saleModel->checkStock($item['id_producto'], $cantidad);
            
            if (!$stockCheck['available']) {
                return [
                    'success' => false, 
                    'message' => 'Stock insuficiente para ' . $producto['nombre'] . '. ' . $stockCheck['message'],
                    'producto' => $producto['nombre'],
                    'stock_disponible' => $stockCheck['stock_disponible'] ?? 0
                ];
            }
            
            $precio = floatval($producto['precio_venta']);
            $subtotal = $precio * $cantidad;
            
            $productos[] = [
                'id_producto' => $producto['id_producto'],
                'nombre' => $producto['nombre'],
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
            
            $total += $subtotal;
        }
        
        if (count($platillos) === 0 && count($productos) === 0) {
            return ['success' => false, 'message' => 'No hay items válidos en la orden'];
        }
        
        // Crear la venta
        $id_usuario = $_SESSION['user_id'];
        $nombre_cliente = trim($data['nombre_cliente']);
        $metodo_pago = $data['metodo_pago'];
        $tipo_servicio = $data['tipo_servicio'];
        
        $result = $this->saleModel->createSaleWithProducts($id_usuario, $nombre_cliente, $total, $metodo_pago, $tipo_servicio, $platillos, $productos);
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Orden registrada exitosamente',
                'id_venta' => $result['id_venta'],
                'total' => $total
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al registrar la orden: ' . ($result['error'] ?? 'Error desconocido')
            ];
        }
    }
    
    // Obtener platillos para el menú
    public function getPlatillos() {
        requireLogin();
        
        $platillos = $this->saleModel->getPlatillos();
        
        // Agrupar por categoría
        $categorias = [];
        foreach ($platillos as $platillo) {
            $categoria = $platillo['categoria_platillo'];
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [];
            }
            $categorias[$categoria][] = $platillo;
        }
        
        return [
            'success' => true,
            'platillos' => $platillos,
            'categorias' => $categorias
        ];
    }
    
    // Obtener bebidas para el menú
    public function getBebidas() {
        requireLogin();
        
        $bebidas = $this->saleModel->getBebidas();
        
        // Agrupar por categoría
        $categorias = [];
        foreach ($bebidas as $bebida) {
            $categoria = $bebida['categoria'];
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [];
            }
            $categorias[$categoria][] = $bebida;
        }
        
        return [
            'success' => true,
            'bebidas' => $bebidas,
            'categorias' => $categorias
        ];
    }
    
    // Verificar stock de un producto
    public function checkProductStock() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Método no permitido'];
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id_producto']) || empty($data['cantidad'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }
        
        $stockCheck = $this->saleModel->checkStock($data['id_producto'], intval($data['cantidad']));
        
        return [
            'success' => $stockCheck['available'],
            'available' => $stockCheck['available'],
            'message' => $stockCheck['message'] ?? '',
            'stock_disponible' => $stockCheck['stock_disponible'] ?? 0
        ];
    }
}

// Manejar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $controller = new SalesController();
    
    switch ($_GET['action']) {
        case 'create_order':
            echo json_encode($controller->processOrder());
            exit;
            
        case 'get_platillos':
            echo json_encode($controller->getPlatillos());
            exit;
            
        case 'get_bebidas':
            echo json_encode($controller->getBebidas());
            exit;
            
        case 'check_stock':
            echo json_encode($controller->checkProductStock());
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
}
?>

