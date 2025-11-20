<?php
require_once '../../inc/session.php';
requireLogin();

require_once '../../models/record.php';

$recordsModel = new Record();
$isAdmin = isAdmin();
$today = date('Y-m-d');

function sanitizeDateParam($value, $fallback) {
    if (empty($value)) {
        return $fallback;
    }
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date ? $date->format('Y-m-d') : $fallback;
}

$startDate = sanitizeDateParam($_GET['start_date'] ?? null, $today);
$endDate = sanitizeDateParam($_GET['end_date'] ?? null, $today);

if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

$cashDate = sanitizeDateParam($_GET['cash_date'] ?? null, $today);

$validPayments = ['Efectivo', 'Tarjeta'];
$validStatuses = ['Completada', 'Cancelada'];
$validMovements = ['Entrada', 'Salida', 'Ajuste'];

$filterPayment = in_array($_GET['metodo_pago'] ?? '', $validPayments) ? $_GET['metodo_pago'] : null;
$filterStatus = in_array($_GET['estado'] ?? '', $validStatuses) ? $_GET['estado'] : null;
$filterMovement = in_array($_GET['movement_type'] ?? '', $validMovements) ? $_GET['movement_type'] : null;
$filterSearch = trim($_GET['search'] ?? '');

$salesFilters = array_filter([
    'metodo_pago' => $filterPayment,
    'estado' => $filterStatus,
    'search' => $filterSearch,
], fn($value) => $value !== null && $value !== '');

$sales = $recordsModel->getSales($startDate, $endDate, $salesFilters);
$cashSummary = $recordsModel->getCashCutSummary($cashDate);
$inventoryMovements = $recordsModel->getInventoryMovements($startDate, $endDate, $filterMovement);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Registros - Antojitos ALKASE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-6 space-y-8">
        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-orange-500 font-semibold">Panel de Control</p>
                <h1 class="text-3xl font-bold text-slate-900">Módulo de Registros</h1>
                
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm text-slate-500">Usuario</p>
                    <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                    <p class="text-xs uppercase tracking-wide text-slate-400"><?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
                </div>
                <a href="../dashboard.php" class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-500">
                    ← Regresar
                </a>
            </div>
        </header>

        <?php if (!$isAdmin): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
                Modo lectura: solo los administradores pueden modificar registros desde este módulo.
            </div>
        <?php endif; ?>

        <!-- Corte de caja -->
        <section class="bg-white rounded-2xl shadow-sm p-6 space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Corte de caja del día</h2>
                    <p class="text-slate-500 text-sm">Totales basados en ventas completadas</p>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                    <input type="hidden" name="metodo_pago" value="<?php echo htmlspecialchars($filterPayment ?? ''); ?>">
                    <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filterStatus ?? ''); ?>">
                    <input type="hidden" name="movement_type" value="<?php echo htmlspecialchars($filterMovement ?? ''); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($filterSearch); ?>">
                    <label class="text-sm font-medium text-slate-600">Fecha
                        <input type="date" name="cash_date" value="<?php echo htmlspecialchars($cashDate); ?>" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </label>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
                        Actualizar
                    </button>
                </form>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Monto total</p>
                    <p class="text-2xl font-bold text-slate-900">$<?php echo number_format($cashSummary['monto_total'], 2); ?></p>
                </div>
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Transacciones</p>
                    <p class="text-2xl font-bold text-slate-900"><?php echo (int) $cashSummary['total_transacciones']; ?></p>
                </div>
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Ticket promedio</p>
                    <p class="text-2xl font-bold text-slate-900">$<?php echo number_format($cashSummary['ticket_promedio'], 2); ?></p>
                </div>
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">pagos con Efectivo y Tarjeta</p>
                    <p class="text-sm text-slate-700">Efectivo: <span class="font-semibold">$<?php echo number_format($cashSummary['total_efectivo'], 2); ?></span></p>
                    <p class="text-sm text-slate-700">Tarjeta: <span class="font-semibold">$<?php echo number_format($cashSummary['total_tarjeta'], 2); ?></span></p>
                </div>
            </div>
        </section>

        <!-- Filtros -->
        <section class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Filtros de ventas</h2>
                </div>
                <a href="reports.php" class="text-sm text-orange-500 hover:text-orange-600 font-semibold">Limpiar filtros</a>
            </div>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <label class="text-sm font-medium text-slate-600">Desde
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="text-sm font-medium text-slate-600">Hasta
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </label>
                <label class="text-sm font-medium text-slate-600">Método de pago
                    <select name="metodo_pago" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Todos</option>
                        <?php foreach ($validPayments as $payment): ?>
                            <option value="<?php echo $payment; ?>" <?php echo $filterPayment === $payment ? 'selected' : ''; ?>>
                                <?php echo $payment; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="text-sm font-medium text-slate-600">Estado de la venta
                    <select name="estado" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Todos</option>
                        <?php foreach ($validStatuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo $filterStatus === $status ? 'selected' : ''; ?>>
                                <?php echo $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <input type="hidden" name="cash_date" value="<?php echo htmlspecialchars($cashDate); ?>">
                <div class="md:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                        Aplicar filtros
                    </button>
                </div>
            </form>
        </section>

        <!-- Ventas -->
        <section class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Ventas registradas</h2>
                    <p class="text-sm text-slate-500"><?php echo count($sales); ?> resultados</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-3 px-2">Folio</th>
                            <th class="py-3 px-2">Fecha</th>
                            <th class="py-3 px-2">Cliente</th>
                            <th class="py-3 px-2">Atendió</th>
                            <th class="py-3 px-2">Pago</th>
                            <th class="py-3 px-2">Total</th>
                            <th class="py-3 px-2">Estado</th>
                            <th class="py-3 px-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">No se encontraron ventas para los filtros aplicados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-2 font-semibold text-slate-800">#<?php echo $sale['id_venta']; ?></td>
                                    <td class="py-3 px-2 text-slate-600"><?php echo date('d/m/Y H:i', strtotime($sale['fecha_venta'])); ?></td>
                                    <td class="py-3 px-2"><?php echo htmlspecialchars($sale['nombre_cliente'] ?? 'N/D'); ?></td>
                                    <td class="py-3 px-2"><?php echo htmlspecialchars($sale['usuario']); ?></td>
                                    <td class="py-3 px-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                            <?php echo $sale['metodo_pago'] === 'Efectivo' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                                            <?php echo htmlspecialchars($sale['metodo_pago']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 font-semibold text-slate-900">$<?php echo number_format($sale['total'], 2); ?></td>
                                    <td class="py-3 px-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                            <?php echo $sale['estado'] === 'Completada' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                            <?php echo htmlspecialchars($sale['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-2">
                                        <div class="flex flex-col gap-2 items-center">
                                            <button data-sale="<?php echo $sale['id_venta']; ?>" class="btn-detail text-sm font-semibold text-orange-600 hover:text-orange-500">
                                                Ver detalle
                                            </button>
                                            <?php if ($isAdmin): ?>
                                                <div class="flex items-center gap-2">
                                                    <select class="status-select border border-slate-200 rounded-lg px-2 py-1 text-xs" data-sale="<?php echo $sale['id_venta']; ?>">
                                                        <?php foreach ($validStatuses as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php echo $sale['estado'] === $status ? 'selected' : ''; ?>>
                                                                <?php echo $status; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn-status text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg px-3 py-1" data-sale="<?php echo $sale['id_venta']; ?>">
                                                        Actualizar
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[11px] text-slate-400">Sin permisos</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <!-- Modal Detalle de venta -->
    <div id="sale-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-xs uppercase text-slate-400">Detalle de la venta</p>
                    <h3 id="modal-title" class="text-lg font-semibold text-slate-900"></h3>
                </div>
                <button id="modal-close" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-4 space-y-4" id="modal-content">
                <p class="text-sm text-slate-500">Cargando información...</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-right">
                <button id="modal-close-footer" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:text-slate-900">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="hidden fixed bottom-6 right-6 px-4 py-2 rounded-lg shadow-lg text-white text-sm font-semibold"></div>

    <script>
        const detailButtons = document.querySelectorAll('.btn-detail');
        const statusButtons = document.querySelectorAll('.btn-status');
        const modal = document.getElementById('sale-modal');
        const modalClose = document.getElementById('modal-close');
        const modalCloseFooter = document.getElementById('modal-close-footer');
        const modalContent = document.getElementById('modal-content');
        const modalTitle = document.getElementById('modal-title');
        const toast = document.getElementById('toast');

        detailButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const saleId = button.dataset.sale;
                await openSaleDetail(saleId);
            });
        });

        statusButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const saleId = button.dataset.sale;
                const select = document.querySelector(`.status-select[data-sale="${saleId}"]`);
                if (!select) return;
                await updateSaleStatus(saleId, select.value);
            });
        });

        [modalClose, modalCloseFooter].forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        });

        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = `fixed bottom-6 right-6 px-4 py-2 rounded-lg shadow-lg text-white text-sm font-semibold ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`;
            toast.classList.remove('hidden');

            setTimeout(() => {
                toast.classList.add('hidden');
            }, 2500);
        }

        async function openSaleDetail(id) {
            modal.classList.remove('hidden');
            modalContent.innerHTML = '<p class="text-sm text-slate-500">Cargando información...</p>';
            modalTitle.textContent = `Venta #${id}`;

            try {
                const response = await fetch(`../../controllers/records.php?action=sale_detail&id=${id}`);
                const result = await response.json();

                if (!result.success) {
                    modalContent.innerHTML = `<p class="text-sm text-rose-500">${result.message || 'No se pudo cargar la venta.'}</p>`;
                    return;
                }

                const sale = result.data;
                const items = sale.detalles || [];

                const detailHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Cliente</p>
                            <p class="font-semibold text-slate-900">${sale.nombre_cliente || 'Sin registrar'}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Atendió</p>
                            <p class="font-semibold text-slate-900">${sale.usuario}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Fecha</p>
                            <p class="font-semibold text-slate-900">${new Date(sale.fecha_venta).toLocaleString()}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Método de pago</p>
                            <p class="font-semibold text-slate-900">${sale.metodo_pago}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-slate-500 text-sm mb-2">Productos vendidos</p>
                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                    <tr>
                                        <th class="py-2 px-3 text-left">Platillo</th>
                                        <th class="py-2 px-3 text-center">Cant.</th>
                                        <th class="py-2 px-3 text-right">Precio</th>
                                        <th class="py-2 px-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.length === 0 ? `
                                        <tr><td colspan="4" class="text-center py-4 text-slate-400">Sin detalles registrados.</td></tr>
                                    ` : items.map(item => `
                                        <tr class="border-t border-slate-100">
                                            <td class="py-2 px-3">${item.nombre_platillo || item.nombre}</td>
                                            <td class="py-2 px-3 text-center">${item.cantidad}</td>
                                            <td class="py-2 px-3 text-right">$${Number(item.precio_unitario).toFixed(2)}</td>
                                            <td class="py-2 px-3 text-right">$${Number(item.subtotal).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right mt-4 text-lg font-semibold text-slate-900">
                            Total: $${Number(sale.total).toFixed(2)}
                        </div>
                    </div>
                `;

                modalContent.innerHTML = detailHtml;
            } catch (error) {
                modalContent.innerHTML = '<p class="text-sm text-rose-500">Error al consultar la venta.</p>';
            }
        }

        async function updateSaleStatus(id, status) {
            try {
                const response = await fetch('../../controllers/records.php?action=update_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sale_id: id, status })
                });

                const result = await response.json();
                if (!result.success) {
                    showToast(result.message || 'No se pudo actualizar.', 'error');
                    return;
                }

                showToast('Venta actualizada correctamente');
                setTimeout(() => window.location.reload(), 800);
            } catch (error) {
                showToast('Error de comunicación con el servidor', 'error');
            }
        }
    </script>
</body>
</html>

