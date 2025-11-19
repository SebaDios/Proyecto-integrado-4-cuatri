-- Script para extender la estructura de la base de datos para el módulo de punto de venta
-- Este script mantiene intactas las tablas de productos/inventario y agrega soporte para platillos del menú
-- IMPORTANTE: Ejecutar primero el script platillos.sql si la tabla platillos no existe

-- 1. Agregar campo nombre_cliente a la tabla ventas
-- Nota: Si el campo ya existe, esta línea dará error. En ese caso, omítela.
ALTER TABLE ventas 
ADD COLUMN nombre_cliente VARCHAR(100) NULL AFTER id_usuario;

-- 2. Crear tabla detalle_ventas_platillos para registrar platillos vendidos
CREATE TABLE IF NOT EXISTS detalle_ventas_platillos (
    id_detalle_platillo INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_platillo INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_platillo) REFERENCES platillos(id_platillo),
    INDEX idx_dvp_venta (id_venta),
    INDEX idx_dvp_platillo (id_platillo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

