-- Script para agregar el campo tipo_servicio a la tabla ventas
-- Este campo permite registrar si la comida es "Para llevar" o "Comer aquí"

ALTER TABLE ventas 
ADD COLUMN tipo_servicio ENUM('Para llevar', 'Comer aquí') NULL DEFAULT NULL 
AFTER metodo_pago;

