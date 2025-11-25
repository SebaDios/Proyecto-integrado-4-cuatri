-- Script para agregar soporte de bebidas al punto de venta
-- Las bebidas se manejan como productos del inventario

-- 1. Crear tabla detalle_ventas_productos para registrar productos (bebidas) vendidos
CREATE TABLE IF NOT EXISTS detalle_ventas_productos (
    id_detalle_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_dvp_venta (id_venta),
    INDEX idx_dvp_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Agregar campo tipo_servicio a ventas si no existe
-- Nota: IF NOT EXISTS no está disponible en ALTER TABLE para MySQL, 
-- ejecutar manualmente si el campo ya existe dará error, pero se puede ignorar
ALTER TABLE ventas 
ADD COLUMN tipo_servicio ENUM('Comer aquí', 'Para llevar') DEFAULT 'Comer aquí' AFTER metodo_pago;

