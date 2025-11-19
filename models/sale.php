<?php
require_once __DIR__ . '/../config/database.php';

class Sale {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Obtener todos los platillos disponibles
    public function getPlatillos() {
        $query = "SELECT id_platillo, nombre_platillo, categoria_platillo, precio_platillo 
                  FROM platillos 
                  ORDER BY categoria_platillo, nombre_platillo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener platillo por ID
    public function getPlatilloById($id) {
        $query = "SELECT id_platillo, nombre_platillo, categoria_platillo, precio_platillo 
                  FROM platillos 
                  WHERE id_platillo = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Crear una nueva venta
    public function createSale($id_usuario, $nombre_cliente, $total, $metodo_pago, $items) {
        try {
            $this->conn->beginTransaction();
            
            // Insertar la venta
            $query = "INSERT INTO ventas (id_usuario, nombre_cliente, total, metodo_pago, estado) 
                      VALUES (:id_usuario, :nombre_cliente, :total, :metodo_pago, 'Completada')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':nombre_cliente', $nombre_cliente);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':metodo_pago', $metodo_pago);
            $stmt->execute();
            
            $id_venta = $this->conn->lastInsertId();
            
            // Insertar los detalles de la venta (tabla dedicada a platillos)
            $detailQuery = "INSERT INTO detalle_ventas_platillos (id_venta, id_platillo, cantidad, precio_unitario, subtotal) 
                           VALUES (:id_venta, :id_platillo, :cantidad, :precio_unitario, :subtotal)";
            
            $detailStmt = $this->conn->prepare($detailQuery);
            
            foreach ($items as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $detailStmt->bindParam(':id_venta', $id_venta);
                $detailStmt->bindParam(':id_platillo', $item['id_platillo']);
                $detailStmt->bindParam(':cantidad', $item['cantidad']);
                $detailStmt->bindParam(':precio_unitario', $item['precio']);
                $detailStmt->bindParam(':subtotal', $subtotal);
                $detailStmt->execute();
            }
            
            // Guardar nombre del cliente en un campo adicional si existe
            // Por ahora, podemos usar un campo de notas o crear una tabla adicional
            // Por simplicidad, asumiremos que podemos agregar un campo nombre_cliente a ventas
            // Si no existe, lo omitiremos por ahora
            
            $this->conn->commit();
            return ['success' => true, 'id_venta' => $id_venta];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Obtener todas las ventas
    public function getAllSales($limit = 100) {
        $query = "SELECT v.id_venta, v.fecha_venta, v.nombre_cliente, v.total, v.metodo_pago, v.estado,
                         u.nombre_completo as usuario
                  FROM ventas v
                  INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                  ORDER BY v.fecha_venta DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener detalles de una venta (platillos)
    public function getSaleDetails($id_venta) {
        $query = "SELECT dvp.id_detalle_platillo, dvp.cantidad, dvp.precio_unitario, dvp.subtotal,
                         p.nombre_platillo, p.categoria_platillo
                  FROM detalle_ventas_platillos dvp
                  INNER JOIN platillos p ON dvp.id_platillo = p.id_platillo
                  WHERE dvp.id_venta = :id_venta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_venta', $id_venta);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener venta completa con detalles
    public function getSaleById($id_venta) {
        $query = "SELECT v.id_venta, v.fecha_venta, v.nombre_cliente, v.total, v.metodo_pago, v.estado,
                         u.nombre_completo as usuario
                  FROM ventas v
                  INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE v.id_venta = :id_venta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_venta', $id_venta);
        $stmt->execute();
        
        $venta = $stmt->fetch();
        
        if ($venta) {
            $venta['detalles'] = $this->getSaleDetails($id_venta);
        }
        
        return $venta;
    }
}
?>

