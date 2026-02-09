<?php
/**
 * Endpoint para eliminar un pedido
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

// Validar datos
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // Eliminar detalles del pedido primero (foreign key)
    $stmt = $pdo->prepare("DELETE FROM tbl_order_detail WHERE id_order = ?");
    $stmt->execute([$order_id]);

    // Eliminar el pedido
    $stmt = $pdo->prepare("DELETE FROM tbl_order WHERE id_order = ?");
    $stmt->execute([$order_id]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Pedido eliminado correctamente'
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No se encontró el pedido']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
