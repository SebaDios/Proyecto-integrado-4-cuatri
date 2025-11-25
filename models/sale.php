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
    public function createSale($id_usuario, $nombre_cliente, $total, $metodo_pago, $tipo_servicio, $items) {
        try {
            $this->conn->beginTransaction();
            
            // Insertar la venta
            $query = "INSERT INTO ventas (id_usuario, nombre_cliente, total, metodo_pago, tipo_servicio, estado) 
                      VALUES (:id_usuario, :nombre_cliente, :total, :metodo_pago, :tipo_servicio, 'Pendiente')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':nombre_cliente', $nombre_cliente);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':metodo_pago', $metodo_pago);
            $stmt->bindParam(':tipo_servicio', $tipo_servicio);
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
        $query = "SELECT v.id_venta, v.fecha_venta, v.nombre_cliente, v.total, v.metodo_pago, v.tipo_servicio, v.estado,
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
    
    // Obtener bebidas disponibles (productos con categoría de bebidas)
    public function getBebidas() {
        $query = "SELECT id_producto, nombre, categoria, precio_venta, stock_actual, stock_minimo
                  FROM productos 
                  WHERE activo = 1 
                  AND categoria IN ('Refrescos', 'Jugos', 'Aguas')
                  ORDER BY categoria, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener producto (bebida) por ID
    public function getProductoById($id) {
        $query = "SELECT id_producto, nombre, categoria, precio_venta, stock_actual, stock_minimo, activo
                  FROM productos 
                  WHERE id_producto = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Verificar stock disponible de un producto
    public function checkStock($id_producto, $cantidad_solicitada) {
        $producto = $this->getProductoById($id_producto);
        
        if (!$producto || !$producto['activo']) {
            return ['available' => false, 'message' => 'Producto no encontrado o inactivo'];
        }
        
        if ($producto['stock_actual'] < $cantidad_solicitada) {
            return [
                'available' => false, 
                'message' => 'Stock insuficiente. Disponible: ' . $producto['stock_actual'],
                'stock_disponible' => $producto['stock_actual']
            ];
        }
        
        return [
            'available' => true,
            'stock_disponible' => $producto['stock_actual']
        ];
    }
    
    // Descontar stock de un producto
    public function descontarStock($id_producto, $cantidad) {
        try {
            // Asegurar que los valores sean enteros
            $id_producto = intval($id_producto);
            $cantidad = intval($cantidad);
            
            if ($cantidad <= 0) {
                error_log("descontarStock: Cantidad inválida ($cantidad) para producto $id_producto");
                return false;
            }
            
            // Primero verificar que hay suficiente stock (dentro de la transacción actual)
            $producto = $this->getProductoById($id_producto);
            if (!$producto) {
                error_log("descontarStock: Producto no encontrado: $id_producto");
                return false;
            }
            
            $stock_actual = intval($producto['stock_actual']);
            if ($stock_actual < $cantidad) {
                error_log("descontarStock: Stock insuficiente para producto $id_producto. Disponible: $stock_actual, Solicitado: $cantidad");
                return false;
            }
            
            // Actualizar el stock usando bindValue en lugar de bindParam para evitar problemas con referencias
            // Usar nombres de parámetros diferentes para la condición WHERE
            $query = "UPDATE productos 
                      SET stock_actual = stock_actual - :cantidad_desc,
                          ultima_actualizacion = CURRENT_TIMESTAMP
                      WHERE id_producto = :id_producto 
                      AND stock_actual >= :cantidad_min";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
            $stmt->bindValue(':cantidad_desc', $cantidad, PDO::PARAM_INT);
            $stmt->bindValue(':cantidad_min', $cantidad, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            
            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                error_log("descontarStock: Error al ejecutar UPDATE: " . print_r($errorInfo, true));
                return false;
            }
            
            $rowsAffected = $stmt->rowCount();
            if ($rowsAffected === 0) {
                error_log("descontarStock: No se actualizó ninguna fila para producto $id_producto. Stock actual: $stock_actual, Cantidad solicitada: $cantidad");
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("descontarStock: Excepción PDO: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("descontarStock: Excepción: " . $e->getMessage());
            return false;
        }
    }
    
    // Crear una nueva venta con soporte para platillos y productos (bebidas)
    public function createSaleWithProducts($id_usuario, $nombre_cliente, $total, $metodo_pago, $tipo_servicio, $platillos, $productos) {
        try {
            $this->conn->beginTransaction();
            
            // Validar stock de productos antes de crear la venta
            foreach ($productos as $producto) {
                $stockCheck = $this->checkStock($producto['id_producto'], $producto['cantidad']);
                if (!$stockCheck['available']) {
                    $this->conn->rollBack();
                    return [
                        'success' => false, 
                        'error' => $stockCheck['message'],
                        'producto' => $producto['id_producto']
                    ];
                }
            }
            
            // Insertar la venta
            $query = "INSERT INTO ventas (id_usuario, nombre_cliente, total, metodo_pago, tipo_servicio, estado) 
                      VALUES (:id_usuario, :nombre_cliente, :total, :metodo_pago, :tipo_servicio, 'Pendiente')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':nombre_cliente', $nombre_cliente);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':metodo_pago', $metodo_pago);
            $stmt->bindParam(':tipo_servicio', $tipo_servicio);
            $stmt->execute();
            
            $id_venta = $this->conn->lastInsertId();
            
            // Insertar los detalles de platillos
            if (count($platillos) > 0) {
                $detailQuery = "INSERT INTO detalle_ventas_platillos (id_venta, id_platillo, cantidad, precio_unitario, subtotal) 
                               VALUES (:id_venta, :id_platillo, :cantidad, :precio_unitario, :subtotal)";
                
                $detailStmt = $this->conn->prepare($detailQuery);
                
                foreach ($platillos as $item) {
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $detailStmt->bindParam(':id_venta', $id_venta);
                    $detailStmt->bindParam(':id_platillo', $item['id_platillo']);
                    $detailStmt->bindParam(':cantidad', $item['cantidad']);
                    $detailStmt->bindParam(':precio_unitario', $item['precio']);
                    $detailStmt->bindParam(':subtotal', $subtotal);
                    $detailStmt->execute();
                }
            }
            
            // Insertar los detalles de productos (bebidas) y descontar stock
            if (count($productos) > 0) {
                $detailQuery = "INSERT INTO detalle_ventas_productos (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
                               VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)";
                
                $detailStmt = $this->conn->prepare($detailQuery);
                
                foreach ($productos as $item) {
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $id_producto_actual = intval($item['id_producto']);
                    $cantidad_actual = intval($item['cantidad']);
                    $precio_actual = floatval($item['precio']);
                    
                    $detailStmt->bindValue(':id_venta', $id_venta, PDO::PARAM_INT);
                    $detailStmt->bindValue(':id_producto', $id_producto_actual, PDO::PARAM_INT);
                    $detailStmt->bindValue(':cantidad', $cantidad_actual, PDO::PARAM_INT);
                    $detailStmt->bindValue(':precio_unitario', $precio_actual);
                    $detailStmt->bindValue(':subtotal', $subtotal);
                    $detailStmt->execute();
                    
                    // Descontar stock
                    if (!$this->descontarStock($id_producto_actual, $cantidad_actual)) {
                        throw new Exception('Error al descontar stock del producto: ' . $id_producto_actual . '. Verifica que haya stock suficiente.');
                    }
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'id_venta' => $id_venta];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>

