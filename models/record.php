<?php
require_once __DIR__ . '/../config/database.php';

class Record {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener ventas dentro de un rango de fechas con filtros opcionales.
     */
    public function getSales($startDate, $endDate, array $filters = []) {
        $query = "SELECT 
                    v.id_venta,
                    v.fecha_venta,
                    v.nombre_cliente,
                    v.total,
                    v.metodo_pago,
                    v.estado,
                    u.nombre_completo AS usuario,
                    u.rol AS rol_usuario
                  FROM ventas v
                  INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE DATE(v.fecha_venta) BETWEEN :start AND :end";

        $params = [
            ':start' => $startDate,
            ':end'   => $endDate,
        ];

        if (!empty($filters['metodo_pago'])) {
            $query .= " AND v.metodo_pago = :metodo_pago";
            $params[':metodo_pago'] = $filters['metodo_pago'];
        }

        if (!empty($filters['estado'])) {
            $query .= " AND v.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (v.nombre_cliente LIKE :search OR u.nombre_completo LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';

            if (ctype_digit($filters['search'])) {
                $query .= " OR v.id_venta = :search_exact";
                $params[':search_exact'] = (int) $filters['search'];
            }

            $query .= ")";
        }

        $query .= " ORDER BY v.fecha_venta DESC";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $key === ':search_exact' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Resumen del corte de caja diario (solo ventas completadas).
     */
    public function getCashCutSummary($date) {
        $query = "SELECT 
                    COUNT(*) AS total_transacciones,
                    COALESCE(SUM(total), 0) AS monto_total,
                    COALESCE(SUM(CASE WHEN metodo_pago = 'Efectivo' THEN total END), 0) AS total_efectivo,
                    COALESCE(SUM(CASE WHEN metodo_pago = 'Tarjeta' THEN total END), 0) AS total_tarjeta,
                    COALESCE(SUM(CASE WHEN metodo_pago NOT IN ('Efectivo', 'Tarjeta') THEN total END), 0) AS total_otro
                  FROM ventas
                  WHERE DATE(fecha_venta) = :fecha AND estado = 'Completada'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':fecha', $date);
        $stmt->execute();
        $summary = $stmt->fetch();
        if (!$summary) {
            $summary = [
                'total_transacciones' => 0,
                'monto_total' => 0,
                'total_efectivo' => 0,
                'total_tarjeta' => 0,
                'total_otro' => 0,
            ];
        }

        $abonoPromedio = $summary['total_transacciones'] > 0
            ? (float) $summary['monto_total'] / (int) $summary['total_transacciones']
            : 0;

        $summary['ticket_promedio'] = $abonoPromedio;
        return $summary;
    }

    /**
     * Obtener movimientos de inventario en un rango de fechas.
     */
    public function getInventoryMovements($startDate, $endDate, $type = null) {
        $query = "SELECT 
                    m.id_movimiento,
                    m.tipo_movimiento,
                    m.cantidad,
                    m.motivo,
                    m.fecha_movimiento,
                    p.nombre AS producto,
                    u.nombre_completo AS usuario
                  FROM movimientos_inventario m
                  INNER JOIN productos p ON m.id_producto = p.id_producto
                  INNER JOIN usuarios u ON m.id_usuario = u.id_usuario
                  WHERE DATE(m.fecha_movimiento) BETWEEN :start AND :end";

        $params = [
            ':start' => $startDate,
            ':end'   => $endDate,
        ];

        if (!empty($type) && in_array($type, ['Entrada', 'Salida', 'Ajuste'])) {
            $query .= " AND m.tipo_movimiento = :tipo";
            $params[':tipo'] = $type;
        }

        $query .= " ORDER BY m.fecha_movimiento DESC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actualizar el estado de una venta específica.
     */
    public function updateSaleStatus($saleId, $newStatus) {
        $allowed = ['Pendiente', 'Completada', 'Cancelada'];
        if (!in_array($newStatus, $allowed)) {
            return false;
        }

        $query = "UPDATE ventas SET estado = :estado WHERE id_venta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':estado', $newStatus);
        $stmt->bindValue(':id', $saleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualizar el método de pago de una venta específica.
     */
    public function updatePaymentMethod($saleId, $newPaymentMethod) {
        $allowed = ['Efectivo', 'Tarjeta', 'Transferencia'];
        if (!in_array($newPaymentMethod, $allowed)) {
            return false;
        }

        $query = "UPDATE ventas SET metodo_pago = :metodo_pago WHERE id_venta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':metodo_pago', $newPaymentMethod);
        $stmt->bindValue(':id', $saleId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>

