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
$id_pedido = isset($data['id_pedido']) ? (int) $data['id_pedido'] : (isset($data['order_id']) ? (int) $data['order_id'] : 0);

// Validar datos
if ($id_pedido <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // Eliminar registros relacionados primero
    $stmt_hist = $pdo->prepare("DELETE FROM tbl_historial_pedido WHERE id_pedido = ?");
    $stmt_hist->execute([$id_pedido]);

    $stmt_proof = $pdo->prepare("DELETE FROM tbl_comprobante_pago WHERE id_pedido = ?");
    $stmt_proof->execute([$id_pedido]);

    // Eliminar detalles del pedido (foreign key)
    $stmt_det = $pdo->prepare("DELETE FROM tbl_detalle_pedido WHERE id_pedido = ?");
    $stmt_det->execute([$id_pedido]);

    // Eliminar el pedido
    $stmt = $pdo->prepare("DELETE FROM tbl_pedido WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);

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
    if ($pdo->inTransaction())
        $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
