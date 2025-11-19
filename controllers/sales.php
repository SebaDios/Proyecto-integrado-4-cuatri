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
        
        if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            return ['success' => false, 'message' => 'Debe seleccionar al menos un platillo'];
        }
        
        if (empty($data['metodo_pago']) || !in_array($data['metodo_pago'], ['Efectivo', 'Tarjeta'])) {
            return ['success' => false, 'message' => 'Método de pago inválido'];
        }
        
        // Calcular total
        $total = 0;
        $items = [];
        
        foreach ($data['items'] as $item) {
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
            
            $items[] = [
                'id_platillo' => $platillo['id_platillo'],
                'nombre' => $platillo['nombre_platillo'],
                'precio' => $precio,
                'cantidad' => $cantidad
            ];
            
            $total += $subtotal;
        }
        
        if (count($items) === 0) {
            return ['success' => false, 'message' => 'No hay items válidos en la orden'];
        }
        
        // Crear la venta
        $id_usuario = $_SESSION['user_id'];
        $nombre_cliente = trim($data['nombre_cliente']);
        $metodo_pago = $data['metodo_pago'];
        
        $result = $this->saleModel->createSale($id_usuario, $nombre_cliente, $total, $metodo_pago, $items);
        
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
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
}
?>

