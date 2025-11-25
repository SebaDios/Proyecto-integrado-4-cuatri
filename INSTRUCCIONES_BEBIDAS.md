# Instrucciones para la Funcionalidad de Bebidas en el Punto de Venta

## Resumen de la Implementación

Se ha agregado la funcionalidad de bebidas al módulo de punto de venta. Las bebidas se manejan como productos del inventario y se integran completamente con el sistema de control de stock.

## Características Implementadas

1. **Bebidas en el Punto de Venta**: Se pueden agregar bebidas (refrescos, jugos, aguas) a las órdenes
2. **Control de Stock**: Las bebidas se descuentan automáticamente del inventario al vender
3. **Alertas de Stock Bajo**: El sistema muestra alertas visuales cuando el stock está bajo o agotado
4. **Validación en Tiempo Real**: Se valida el stock antes de agregar bebidas a la orden
5. **Integración con Inventario**: Las bebidas están conectadas al módulo de inventario para generar alertas automáticas

## Pasos para Configurar

### 1. Ejecutar el Script SQL

Ejecuta el script SQL para crear la tabla necesaria:

```sql
-- Ejecutar: sql/add_bebidas_support.sql
```

Este script crea:
- La tabla `detalle_ventas_productos` para registrar bebidas vendidas
- El campo `tipo_servicio` en la tabla `ventas` (si no existe)

### 2. Agregar Bebidas al Inventario

Las bebidas deben agregarse como productos en el módulo de inventario con las siguientes categorías:

- **Refrescos**: Para refrescos como Coca Cola, Fanta, etc.
- **Jugos**: Para jugos como Boing de mango, guayaba, etc.
- **Aguas**: Para aguas embotelladas como Agua Ciel 600ml, Agua Ciel 1.5L, etc.

**Ejemplo de productos a crear:**

1. **Refrescos:**
   - Coca Cola 600ml
   - Fanta Naranja 600ml
   - Sprite 600ml

2. **Jugos:**
   - Boing Mango 500ml
   - Boing Guayaba 500ml
   - Boing Fresa 500ml

3. **Aguas:**
   - Agua Ciel 600ml
   - Agua Ciel 1.5L

**Importante**: 
- Marcar `es_perecedero = 0` (FALSE) ya que las bebidas embotelladas no son perecederas
- Establecer un `stock_minimo` apropiado (ej: 10 unidades)
- Configurar el `precio_venta` correcto

### 3. Usar la Funcionalidad en el Punto de Venta

1. Accede al módulo de Punto de Venta
2. Verás una nueva sección "Bebidas" debajo del menú de platillos
3. Cada bebida muestra:
   - Precio de venta
   - Stock disponible
   - Indicador visual:
     - **Normal**: Borde gris (stock suficiente)
     - **Bajo**: Borde amarillo con ⚠️ (stock <= stock_minimo)
     - **Agotado**: Borde rojo con ❌ (stock = 0, botones deshabilitados)

4. Para agregar bebidas a una orden:
   - Usa los botones + y - para ajustar la cantidad
   - El sistema validará automáticamente el stock disponible
   - Si intentas agregar más de lo disponible, verás una alerta

5. Al registrar la orden:
   - Las bebidas se agregan a la orden junto con los platillos
   - El stock se descuenta automáticamente
   - El módulo de inventario generará alertas si el stock queda bajo

## Funcionalidades Técnicas

### Validación de Stock

- **En tiempo real**: Al intentar incrementar la cantidad de una bebida, se verifica el stock disponible
- **Al enviar la orden**: Se valida nuevamente antes de procesar la venta
- **Mensajes claros**: Si no hay stock suficiente, se muestra un mensaje con la cantidad disponible

### Descuento Automático de Stock

- Al registrar una venta con bebidas, el stock se descuenta automáticamente
- Se actualiza el campo `stock_actual` en la tabla `productos`
- Se registra la fecha de última actualización

### Alertas de Inventario

- El módulo de inventario detectará automáticamente cuando el stock de bebidas esté bajo
- Las alertas aparecen cuando `stock_actual <= stock_minimo`
- Los productos con stock bajo se muestran en la sección de alertas del inventario

## Estructura de Base de Datos

### Nueva Tabla: `detalle_ventas_productos`

```sql
CREATE TABLE detalle_ventas_productos (
    id_detalle_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);
```

## Notas Importantes

1. **Categorías de Bebidas**: Solo los productos con categoría "Refrescos", "Jugos" o "Aguas" aparecerán en el punto de venta
2. **Productos Inactivos**: Los productos con `activo = 0` no aparecerán en el punto de venta
3. **Compatibilidad**: El sistema mantiene compatibilidad con órdenes que solo contienen platillos
4. **Transacciones**: Todas las operaciones de venta se realizan en transacciones para garantizar la integridad de los datos

## Solución de Problemas

### Las bebidas no aparecen en el punto de venta

- Verifica que los productos tengan la categoría correcta: "Refrescos", "Jugos" o "Aguas"
- Verifica que los productos estén activos (`activo = 1`)
- Verifica que el script SQL se haya ejecutado correctamente

### Error al registrar una orden con bebidas

- Verifica que haya suficiente stock disponible
- Verifica que la tabla `detalle_ventas_productos` exista
- Revisa los logs de error del servidor

### El stock no se descuenta

- Verifica que la transacción se haya completado correctamente
- Revisa que no haya errores en la base de datos
- Verifica los permisos de la base de datos

## Próximos Pasos Recomendados

1. Agregar las bebidas al inventario con las categorías correctas
2. Establecer stocks iniciales y stocks mínimos apropiados
3. Probar la funcionalidad con una orden de prueba
4. Verificar que las alertas de inventario funcionen correctamente

