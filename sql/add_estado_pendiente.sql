-- Script para agregar el estado "Pendiente" al ENUM de la tabla ventas
-- Esto permite que las órdenes se creen como "Pendiente" y luego se marquen como "Completada"

ALTER TABLE ventas 
MODIFY COLUMN estado ENUM('Pendiente', 'Completada', 'Cancelada') DEFAULT 'Pendiente';

