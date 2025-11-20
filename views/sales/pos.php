<?php
require_once '../../inc/session.php';
requireLogin();

require_once '../../models/sale.php';

$saleModel = new Sale();
$platillos = $saleModel->getPlatillos();

// Agrupar platillos por categoría
$categorias = [];
foreach ($platillos as $platillo) {
    $categoria = $platillo['categoria_platillo'];
    if (!isset($categorias[$categoria])) {
        $categorias[$categoria] = [];
    }
    $categorias[$categoria][] = $platillo;
}

$user_name = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - Antojitos Alkace</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
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
    <div id="app" class="min-h-full bg-gradient-to-br from-orange-50 to-red-50 p-6">
        
        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-6">
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-8 border-orange-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-4xl font-bold mb-2 text-gray-800">Antojitos Alkase</h1>
                        <p class="text-lg text-gray-600">Sistema de Punto de Venta</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Usuario: <?php echo htmlspecialchars($user_name); ?></p>
                        <a href="../dashboard.php" class="text-orange-500 hover:text-orange-600 text-sm font-semibold">← Volver al Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Cliente y Método de Pago -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Nueva Orden</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        
        // Estado de la orden actual
        let currentOrder = {};
        
        // Función para actualizar cantidad
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
        
        // Actualizar visualización de la orden actual
        function updateCurrentOrderDisplay() {
            const container = document.getElementById('current-order');
            const orderItems = Object.values(currentOrder);
            
            if (orderItems.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No hay productos seleccionados</p>';
            } else {
                container.innerHTML = orderItems.map(item => `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-700">${item.cantidad}x ${item.nombre}</span>
                        <span class="font-bold text-gray-800">$${(item.precio * item.cantidad).toFixed(2)}</span>
                    </div>
                `).join('');
            }
            
            updateOrderTotal();
        }
        
        // Actualizar total
        function updateOrderTotal() {
            const total = Object.values(currentOrder).reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const totalElement = document.getElementById('order-total');
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
        
        // Enviar orden
        async function submitOrder() {
            const customerName = document.getElementById('customer-name').value.trim();
            const paymentMethod = document.getElementById('payment-method').value;
            const orderItems = Object.values(currentOrder);
            
            if (!customerName) {
                showMessage('Por favor ingresa el nombre del cliente', 'error');
                return;
            }
            
            if (orderItems.length === 0) {
                showMessage('Por favor selecciona al menos un platillo', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submit-order-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registrando...';
            
            const orderData = {
                nombre_cliente: customerName,
                metodo_pago: paymentMethod,
                items: orderItems.map(item => ({
                    id_platillo: item.id_platillo,
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
                    document.getElementById('customer-name').value = '';
                    platillos.forEach(platillo => {
                        const qtyElement = document.getElementById(`qty-${platillo.id_platillo}`);
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

