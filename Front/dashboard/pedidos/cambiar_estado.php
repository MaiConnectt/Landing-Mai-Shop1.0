<?php
/**
 * Endpoint para cambiar el estado de un pedido
 */
require_once '../auth.php';
require_once '../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? (int) $data['order_id'] : 0;
$new_status = isset($data['status']) ? (int) $data['status'] : -1;

// Validar datos
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

if (!in_array($new_status, [0, 1, 2])) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE tbl_order SET status = ? WHERE id_order = ?");
    $stmt->execute([$new_status, $order_id]);

    if ($stmt->rowCount() > 0) {
        $status_names = [
            0 => 'Pendiente',
            1 => 'En Proceso',
            2 => 'Completado'
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado a: ' . $status_names[$new_status]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el pedido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
