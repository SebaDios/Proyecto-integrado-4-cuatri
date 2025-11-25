<?php
require_once '../../inc/session.php';
requireLogin();

require_once '../../models/sale.php';

$saleModel = new Sale();
$platillos = $saleModel->getPlatillos();
$bebidas = $saleModel->getBebidas();

// Agrupar platillos por categoría
$categorias = [];
foreach ($platillos as $platillo) {
    $categoria = $platillo['categoria_platillo'];
    if (!isset($categorias[$categoria])) {
        $categorias[$categoria] = [];
    }
    $categorias[$categoria][] = $platillo;
}

// Agrupar bebidas por categoría
$categoriasBebidas = [];
foreach ($bebidas as $bebida) {
    $categoria = $bebida['categoria'];
    if (!isset($categoriasBebidas[$categoria])) {
        $categoriasBebidas[$categoria] = [];
    }
    $categoriasBebidas[$categoria][] = $bebida;
}

$user_name = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - Antojitos Alkace</title>
    
    <!-- CSS Principal del Sistema -->
    <link rel="stylesheet" href="../../assets/css.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Asegurar que el header tenga prioridad sobre Tailwind */
        .main-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            background: linear-gradient(135deg, #fdab25 0%, #907952 100%) !important;
            color: #6c5336 !important;
            padding: 1rem 2rem !important;
            box-shadow: 0 2px 4px rgba(108, 83, 54, 0.2) !important;
            min-height: 70px !important;
            width: 100% !important;
            position: relative !important;
        }
        
        .main-header .header-left {
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
        }
        
        .main-header .logo {
            width: 60px !important;
            height: 60px !important;
            object-fit: contain !important;
            border-radius: 50% !important;
            background: #efebe0 !important;
            padding: 5px !important;
            flex-shrink: 0 !important;
        }
        
        .main-header .company-name {
            font-size: 1.5rem !important;
            color: #6c5336 !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }
        
        .main-header .header-right {
            display: flex !important;
            align-items: center !important;
            gap: 1.5rem !important;
        }
        
        /* Estilos para botones del sistema */
        .btn-primary, .btn-secondary {
            display: inline-block !important;
            padding: 0.5rem 1rem !important;
            border: none !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            cursor: pointer !important;
            font-size: 0.9rem !important;
            transition: all 0.3s !important;
            font-weight: 500 !important;
        }
        
        .btn-primary {
            background: #fdab25 !important;
            color: #6c5336 !important;
        }
        
        .btn-primary:hover {
            background: #e09915 !important;
        }
        
        .btn-secondary {
            background: #907952 !important;
            color: #efebe0 !important;
        }
        
        .btn-secondary:hover {
            background: #7a6545 !important;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .menu-item {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .menu-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .quantity-btn {
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
        }

        .quantity-btn:active {
            transform: scale(0.95);
        }

        .order-card {
            transition: all 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .toast.success {
            background-color: #10b981;
        }

        .toast.error {
            background-color: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="min-h-full">
    <?php include_once '../../inc/header.php'; ?>
    
    <div style="padding: 1rem 2rem; background: #efebe0; border-bottom: 1px solid #907952; display: flex; gap: 1rem; align-items: center;">
        <a href="reports.php" class="btn-primary">Ver Registros</a>
        <a href="../dashboard.php" class="btn-secondary">← Volver al Dashboard</a>
    </div>
    
    <div id="app" class="min-h-full bg-gradient-to-br from-orange-50 to-red-50 p-6">

        <!-- Main Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Cliente, Método de Pago y Tipo de Servicio -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Nueva Orden</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Nombre del Cliente</label>
                            <input id="customer-name" type="text" 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Método de Pago</label>
                            <select id="payment-method" 
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Tipo de Servicio</label>
                            <select id="tipo-servicio" 
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none">
                                <option value="Comer aquí">Comer aquí</option>
                                <option value="Para llevar">Para llevar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Menu -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Menú</h2>
                    <div id="menu-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php 
                        $emojiMap = [
                            'Tacos' => '',
                            'Sopes' => '',
                            'Enchiladas' => '',
                            'Chorreadas' => '',
                            'Gringas' => '',
                            'Flautas' => '',
                            'Quesadillas' => '',
                            'Combos' => ''
                        ];
                        
                        foreach ($platillos as $platillo): 
                            $emoji = $emojiMap[$platillo['categoria_platillo']] ?? '🍽️';
                        ?>
                            <div class="menu-item bg-white border-2 rounded-xl p-4 border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-3xl"><?php echo $emoji; ?></span>
                                        <div>
                                            <h3 class="item-name font-bold text-lg text-gray-800"><?php echo htmlspecialchars($platillo['nombre_platillo']); ?></h3>
                                            <p class="item-price font-bold text-orange-500">$<?php echo number_format($platillo['precio_platillo'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center gap-3">
                                    <button 
                                        class="quantity-btn minus-btn w-10 h-10 rounded-lg text-white font-bold text-xl flex items-center justify-center bg-red-500 hover:bg-red-600"
                                        onclick="updateQuantity(<?php echo $platillo['id_platillo']; ?>, -1)"
                                    >-</button>
                                    <div class="quantity-display w-16 h-10 rounded-lg flex items-center justify-center font-bold text-lg bg-yellow-50 text-gray-800">
                                        <span id="qty-<?php echo $platillo['id_platillo']; ?>">0</span>
                                    </div>
                                    <button 
                                        class="quantity-btn plus-btn w-10 h-10 rounded-lg text-white font-bold text-xl flex items-center justify-center bg-orange-500 hover:bg-orange-600"
                                        onclick="updateQuantity(<?php echo $platillo['id_platillo']; ?>, 1)"
                                    >+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Bebidas -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Bebidas</h2>
                    <div id="bebidas-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php 
                        $emojiBebidas = [
                            'Refrescos' => '',
                            'Jugos' => '',
                            'Aguas' => ''
                        ];
                        
                        foreach ($bebidas as $bebida): 
                            $emoji = $emojiBebidas[$bebida['categoria']] ?? '🥤';
                            $stockBajo = $bebida['stock_actual'] <= $bebida['stock_minimo'];
                            $sinStock = $bebida['stock_actual'] <= 0;
                        ?>
                            <div class="menu-item bg-white border-2 rounded-xl p-4 <?php echo $sinStock ? 'border-red-300 opacity-60' : ($stockBajo ? 'border-yellow-300' : 'border-gray-200'); ?>">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-3xl"><?php echo $emoji; ?></span>
                                        <div>
                                            <h3 class="item-name font-bold text-lg text-gray-800"><?php echo htmlspecialchars($bebida['nombre']); ?></h3>
                                            <p class="item-price font-bold text-orange-500">$<?php echo number_format($bebida['precio_venta'], 2); ?></p>
                                            <p class="text-xs <?php echo $sinStock ? 'text-red-600 font-bold' : ($stockBajo ? 'text-yellow-600' : 'text-gray-500'); ?>">
                                                Stock: <?php echo $bebida['stock_actual']; ?> 
                                                <?php if ($stockBajo && !$sinStock): ?>
                                                    <span class="font-bold">⚠️ Bajo</span>
                                                <?php elseif ($sinStock): ?>
                                                    <span class="font-bold">❌ Agotado</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center gap-3">
                                    <button 
                                        class="quantity-btn minus-btn w-10 h-10 rounded-lg text-white font-bold text-xl flex items-center justify-center bg-red-500 hover:bg-red-600 <?php echo $sinStock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        onclick="updateBebidaQuantity(<?php echo $bebida['id_producto']; ?>, -1)"
                                        <?php echo $sinStock ? 'disabled' : ''; ?>
                                    >-</button>
                                    <div class="quantity-display w-16 h-10 rounded-lg flex items-center justify-center font-bold text-lg bg-yellow-50 text-gray-800">
                                        <span id="bebida-qty-<?php echo $bebida['id_producto']; ?>">0</span>
                                    </div>
                                    <button 
                                        class="quantity-btn plus-btn w-10 h-10 rounded-lg text-white font-bold text-xl flex items-center justify-center bg-orange-500 hover:bg-orange-600 <?php echo $sinStock ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        onclick="updateBebidaQuantity(<?php echo $bebida['id_producto']; ?>, 1)"
                                        <?php echo $sinStock ? 'disabled' : ''; ?>
                                    >+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <button id="submit-order-btn" class="w-full py-4 rounded-xl font-bold text-lg text-white bg-orange-500 hover:bg-orange-600 transition">
                        Registrar Orden
                    </button>
                </div>

            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Current Order -->
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Orden Actual</h2>
                    <div id="current-order" class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                        <p class="text-gray-500 text-center py-4">No hay productos seleccionados</p>
                    </div>
                    <div class="border-t-2 pt-4">
                        <div class="flex justify-between items-center text-xl font-bold">
                            <span class="text-gray-800">Total:</span>
                            <span id="order-total" class="text-orange-500">$0.00</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Datos de platillos desde PHP
        const platillos = <?php echo json_encode($platillos); ?>;
        
        // Datos de bebidas desde PHP
        const bebidas = <?php echo json_encode($bebidas); ?>;
        
        // Estado de la orden actual
        let currentOrder = {};
        let currentBebidasOrder = {};
        
        // Función para actualizar cantidad de platillos
        function updateQuantity(platilloId, change) {
            const platillo = platillos.find(p => p.id_platillo == platilloId);
            if (!platillo) return;
            
            if (!currentOrder[platilloId]) {
                currentOrder[platilloId] = {
                    id_platillo: platillo.id_platillo,
                    nombre: platillo.nombre_platillo,
                    precio: parseFloat(platillo.precio_platillo),
                    cantidad: 0
                };
            }
            
            currentOrder[platilloId].cantidad += change;
            
            if (currentOrder[platilloId].cantidad <= 0) {
                delete currentOrder[platilloId];
            }
            
            document.getElementById(`qty-${platilloId}`).textContent = currentOrder[platilloId]?.cantidad || 0;
            updateCurrentOrderDisplay();
        }
        
        // Función para actualizar cantidad de bebidas con validación de stock
        async function updateBebidaQuantity(productoId, change) {
            const bebida = bebidas.find(b => b.id_producto == productoId);
            if (!bebida) return;
            
            const currentQty = currentBebidasOrder[productoId]?.cantidad || 0;
            const newQty = currentQty + change;
            
            // Validar que no sea negativo
            if (newQty < 0) return;
            
            // Validar stock antes de incrementar
            if (change > 0) {
                const stockCheck = await checkStock(productoId, newQty);
                if (!stockCheck.available) {
                    showMessage(`Stock insuficiente para ${bebida.nombre}. Disponible: ${stockCheck.stock_disponible}`, 'error');
                    return;
                }
            }
            
            if (!currentBebidasOrder[productoId]) {
                currentBebidasOrder[productoId] = {
                    id_producto: bebida.id_producto,
                    nombre: bebida.nombre,
                    precio: parseFloat(bebida.precio_venta),
                    cantidad: 0
                };
            }
            
            currentBebidasOrder[productoId].cantidad = newQty;
            
            if (currentBebidasOrder[productoId].cantidad <= 0) {
                delete currentBebidasOrder[productoId];
            }
            
            document.getElementById(`bebida-qty-${productoId}`).textContent = currentBebidasOrder[productoId]?.cantidad || 0;
            updateCurrentOrderDisplay();
        }
        
        // Función para verificar stock de un producto
        async function checkStock(productoId, cantidad) {
            try {
                const response = await fetch('../../controllers/sales.php?action=check_stock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_producto: productoId,
                        cantidad: cantidad
                    })
                });
                
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error al verificar stock:', error);
                return { available: false, message: 'Error al verificar stock' };
            }
        }
        
        // Actualizar visualización de la orden actual
        function updateCurrentOrderDisplay() {
            const container = document.getElementById('current-order');
            const orderItems = Object.values(currentOrder);
            const bebidasItems = Object.values(currentBebidasOrder);
            const allItems = [...orderItems, ...bebidasItems];
            
            if (allItems.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No hay productos seleccionados</p>';
            } else {
                let html = '';
                
                // Mostrar platillos
                if (orderItems.length > 0) {
                    html += orderItems.map(item => `
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">${item.cantidad}x ${item.nombre}</span>
                            <span class="font-bold text-gray-800">$${(item.precio * item.cantidad).toFixed(2)}</span>
                        </div>
                    `).join('');
                }
                
                // Mostrar bebidas
                if (bebidasItems.length > 0) {
                    html += bebidasItems.map(item => `
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">${item.cantidad}x ${item.nombre} 🥤</span>
                            <span class="font-bold text-gray-800">$${(item.precio * item.cantidad).toFixed(2)}</span>
                        </div>
                    `).join('');
                }
                
                container.innerHTML = html;
            }
            
            updateOrderTotal();
        }
        
        // Actualizar total
        function updateOrderTotal() {
            const totalPlatillos = Object.values(currentOrder).reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const totalBebidas = Object.values(currentBebidasOrder).reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const total = totalPlatillos + totalBebidas;
            const totalElement = document.getElementById('order-total');
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
        
        // Enviar orden
        async function submitOrder() {
            const customerName = document.getElementById('customer-name').value.trim();
            const paymentMethod = document.getElementById('payment-method').value;
            const tipoServicio = document.getElementById('tipo-servicio').value;
            const orderItems = Object.values(currentOrder);
            const bebidasItems = Object.values(currentBebidasOrder);
            
            if (!customerName) {
                showMessage('Por favor ingresa el nombre del cliente', 'error');
                return;
            }
            
            if (orderItems.length === 0 && bebidasItems.length === 0) {
                showMessage('Por favor selecciona al menos un platillo o bebida', 'error');
                return;
            }
            
            // Validar stock de todas las bebidas antes de enviar
            for (const bebida of bebidasItems) {
                const stockCheck = await checkStock(bebida.id_producto, bebida.cantidad);
                if (!stockCheck.available) {
                    showMessage(`Stock insuficiente para ${bebida.nombre}. ${stockCheck.message}`, 'error');
                    return;
                }
            }
            
            const submitBtn = document.getElementById('submit-order-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registrando...';
            
            const orderData = {
                nombre_cliente: customerName,
                metodo_pago: paymentMethod,
                tipo_servicio: tipoServicio,
                platillos: orderItems.map(item => ({
                    id_platillo: item.id_platillo,
                    cantidad: item.cantidad
                })),
                productos: bebidasItems.map(item => ({
                    id_producto: item.id_producto,
                    cantidad: item.cantidad
                }))
            };
            
            try {
                const response = await fetch('../../controllers/sales.php?action=create_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('¡Orden registrada exitosamente!', 'success');
                    // Limpiar orden
                    currentOrder = {};
                    currentBebidasOrder = {};
                    document.getElementById('customer-name').value = '';
                    platillos.forEach(platillo => {
                        const qtyElement = document.getElementById(`qty-${platillo.id_platillo}`);
                        if (qtyElement) qtyElement.textContent = '0';
                    });
                    bebidas.forEach(bebida => {
                        const qtyElement = document.getElementById(`bebida-qty-${bebida.id_producto}`);
                        if (qtyElement) qtyElement.textContent = '0';
                    });
                    updateCurrentOrderDisplay();
                } else {
                    showMessage(result.message || 'Error al registrar la orden', 'error');
                }
            } catch (error) {
                showMessage('Error al conectar con el servidor', 'error');
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Registrar Orden';
            }
        }
        
        // Mostrar mensaje
        function showMessage(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Event listener para el botón de enviar
        document.getElementById('submit-order-btn').addEventListener('click', submitOrder);
        
        // Permitir Enter en el campo de nombre del cliente
        document.getElementById('customer-name').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitOrder();
            }
        });
    </script>
</body>
</html>

