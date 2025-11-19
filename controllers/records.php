<?php
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../models/record.php';
require_once __DIR__ . '/../models/sale.php';

class RecordsController {
    private $recordModel;
    private $saleModel;

    public function __construct() {
        $this->recordModel = new Record();
        $this->saleModel = new Sale();
    }

    public function getSaleDetail($saleId) {
        requireLogin();

        $sale = $this->saleModel->getSaleById($saleId);
        if (!$sale) {
            return ['success' => false, 'message' => 'Venta no encontrada'];
        }

        return ['success' => true, 'data' => $sale];
    }

    public function updateSaleStatus($saleId, $status) {
        requireLogin();
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        $updated = $this->recordModel->updateSaleStatus($saleId, $status);
        if (!$updated) {
            return ['success' => false, 'message' => 'No se pudo actualizar la venta'];
        }

        return ['success' => true, 'message' => 'Venta actualizada correctamente'];
    }
}

$controller = new RecordsController();
$action = $_GET['action'] ?? null;

if ($action) {
    header('Content-Type: application/json; charset=utf-8');
}

switch ($action) {
    case 'sale_detail':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $saleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        echo json_encode($controller->getSaleDetail($saleId));
        exit;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) {
            $payload = $_POST;
        }

        $saleId = isset($payload['sale_id']) ? (int) $payload['sale_id'] : 0;
        $status = $payload['status'] ?? '';

        echo json_encode($controller->updateSaleStatus($saleId, $status));
        exit;

    default:
        if ($action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
        }
}
?>

