<?php
require_once '../config/database.php';

class User {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // CREATE - Crear nuevo usuario
    public function create($username, $password, $full_name, $email, $role) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol) 
                      VALUES (:username, :password, :fullname, :email, :role)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':fullname', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // READ - Obtener todos los usuarios
    public function getAll() {
        $query = "SELECT id_usuario, nombre_usuario, nombre_completo, email, rol, 
                         fecha_creacion, activo 
                  FROM usuarios 
                  ORDER BY fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // READ - Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT id_usuario, nombre_usuario, nombre_completo, email, rol, activo 
                  FROM usuarios 
                  WHERE id_usuario = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // UPDATE - Actualizar usuario
    public function update($id, $username, $full_name, $email, $role, $activo) {
        try {
            $query = "UPDATE usuarios 
                      SET nombre_usuario = :username, 
                          nombre_completo = :fullname, 
                          email = :email, 
                          rol = :role,
                          activo = :activo
                      WHERE id_usuario = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':fullname', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':activo', $activo);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // UPDATE - Cambiar contraseña
    public function updatePassword($id, $new_password) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE usuarios SET password_hash = :password WHERE id_usuario = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':password', $password_hash);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // DELETE - Eliminar usuario (desactivar)
    public function delete($id) {
        try {
            // No se elimina físicamente, solo se desactiva
            $query = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Verificar si un usuario existe
    public function usernameExists($username, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE nombre_usuario = :username";
        
        if ($exclude_id) {
            $query .= " AND id_usuario != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}
?>
