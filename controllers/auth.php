<?php
require_once BASE_PATH . '/config/database.php';

class AuthController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT id_usuario, nombre_usuario, password_hash, rol, nombre_completo 
                      FROM usuarios 
                      WHERE nombre_usuario = :username AND activo = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch();
                
                // Verificación segura de contraseña usando password_verify
                if (password_verify($password, $user['password_hash'])) {
                    
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id_usuario'];
                    $_SESSION['user_name'] = $user['nombre_usuario'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['full_name'] = $user['nombre_completo'];
                    
                    return ['success' => true, 'role' => $user['rol']];
                }
            }
            
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error en el sistema'];
        }
    }
    
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: ../views/login.php');
        exit();
    }
    
    public function register($username, $password, $full_name, $email, $role = 'Usuario') {
        try {
            // Hashear la contraseña de forma segura
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO usuarios (nombre_usuario, password_hash, nombre_completo, email, rol) 
                      VALUES (:username, :password, :fullname, :email, :role)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':fullname', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Usuario creado exitosamente'];
            }
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'El usuario ya existe'];
            }
            return ['success' => false, 'message' => 'Error al crear usuario'];
        }
    }
}
?>
