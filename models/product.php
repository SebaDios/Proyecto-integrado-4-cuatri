<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // CREATE - Crear nuevo producto
    public function create($nombre, $descripcion, $categoria, $precio_venta, $precio_compra, $stock_actual, $stock_minimo, $es_perecedero, $fecha_vencimiento) {
        try {
            $query = "INSERT INTO productos (nombre, descripcion, categoria, precio_venta, precio_compra, 
                      stock_actual, stock_minimo, es_perecedero, fecha_vencimiento) 
                      VALUES (:nombre, :descripcion, :categoria, :precio_venta, :precio_compra, 
                      :stock_actual, :stock_minimo, :es_perecedero, :fecha_vencimiento)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':precio_venta', $precio_venta);
            $stmt->bindParam(':precio_compra', $precio_compra);
            $stmt->bindParam(':stock_actual', $stock_actual);
            $stmt->bindParam(':stock_minimo', $stock_minimo);
            $stmt->bindParam(':es_perecedero', $es_perecedero);
            $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // READ - Obtener todos los productos
    public function getAll() {
        $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                         stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, 
                         ultima_actualizacion, activo 
                  FROM productos 
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // READ - Contar total de productos
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM productos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    // READ - Obtener productos paginados
    public function getPaginated($page = 1, $perPage = 25) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                         stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, 
                         ultima_actualizacion, activo 
                  FROM productos 
                  ORDER BY nombre ASC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // READ - Obtener productos activos
    public function getActive() {
        $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                         stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, 
                         ultima_actualizacion 
                  FROM productos 
                  WHERE activo = 1 
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // READ - Obtener producto por ID
    public function getById($id) {
        $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                         stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, activo 
                  FROM productos 
                  WHERE id_producto = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // UPDATE - Actualizar producto
    public function update($id, $nombre, $descripcion, $categoria, $precio_venta, $precio_compra, 
                          $stock_actual, $stock_minimo, $es_perecedero, $fecha_vencimiento, $activo) {
        try {
            $query = "UPDATE productos 
                      SET nombre = :nombre, 
                          descripcion = :descripcion, 
                          categoria = :categoria, 
                          precio_venta = :precio_venta, 
                          precio_compra = :precio_compra, 
                          stock_actual = :stock_actual, 
                          stock_minimo = :stock_minimo, 
                          es_perecedero = :es_perecedero, 
                          fecha_vencimiento = :fecha_vencimiento,
                          activo = :activo
                      WHERE id_producto = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':precio_venta', $precio_venta);
            $stmt->bindParam(':precio_compra', $precio_compra);
            $stmt->bindParam(':stock_actual', $stock_actual);
            $stmt->bindParam(':stock_minimo', $stock_minimo);
            $stmt->bindParam(':es_perecedero', $es_perecedero);
            $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $stmt->bindParam(':activo', $activo);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // DELETE - Eliminar producto (desactivar)
    public function delete($id) {
        try {
            // No se elimina físicamente, solo se desactiva
            $query = "UPDATE productos SET activo = 0 WHERE id_producto = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Obtener productos con stock bajo
    public function getLowStock() {
        $query = "SELECT id_producto, nombre, stock_actual, stock_minimo 
                  FROM productos 
                  WHERE stock_actual <= stock_minimo AND activo = 1 
                  ORDER BY stock_actual ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Obtener productos próximos a vencer (incluye vencidos)
    public function getExpiringSoon($days = 7) {
        $query = "SELECT id_producto, nombre, fecha_vencimiento, stock_actual 
                  FROM productos 
                  WHERE es_perecedero = 1 
                  AND fecha_vencimiento IS NOT NULL 
                  AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                  AND activo = 1 
                  ORDER BY fecha_vencimiento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Verificar si un producto existe por nombre
    public function nameExists($nombre, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM productos WHERE nombre = :nombre";
        
        if ($exclude_id) {
            $query .= " AND id_producto != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    // Obtener todas las categorías únicas
    public function getCategories() {
        $query = "SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Buscar productos por nombre, categoría o descripción
    public function search($searchTerm) {
        try {
            $searchTerm = '%' . $searchTerm . '%';
            
            $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                             stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, activo 
                      FROM productos 
                      WHERE (nombre LIKE :search OR descripcion LIKE :search OR categoria LIKE :search) 
                      AND activo = 1 
                      ORDER BY nombre ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchTerm);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Contar resultados de búsqueda
    public function getSearchCount($searchTerm) {
        try {
            $searchTerm = '%' . $searchTerm . '%';
            
            $query = "SELECT COUNT(*) as total 
                      FROM productos 
                      WHERE (nombre LIKE :search OR descripcion LIKE :search OR categoria LIKE :search) 
                      AND activo = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchTerm);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    // Buscar productos paginados
    public function searchPaginated($searchTerm, $page = 1, $perPage = 25) {
        try {
            $searchTerm = '%' . $searchTerm . '%';
            $offset = ($page - 1) * $perPage;
            
            $query = "SELECT id_producto, nombre, descripcion, categoria, precio_venta, precio_compra, 
                             stock_actual, stock_minimo, es_perecedero, fecha_vencimiento, activo 
                      FROM productos 
                      WHERE (nombre LIKE :search OR descripcion LIKE :search OR categoria LIKE :search) 
                      AND activo = 1 
                      ORDER BY nombre ASC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchTerm);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>