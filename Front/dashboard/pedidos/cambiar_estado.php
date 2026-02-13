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
$id_pedido = isset($data['id_pedido']) ? (int) $data['id_pedido'] : (isset($data['order_id']) ? (int) $data['order_id'] : 0);
$estado_nuevo = isset($data['estado']) ? (int) $data['estado'] : (isset($data['status']) ? (int) $data['status'] : -1);

// Validar datos
if ($id_pedido <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

if (!in_array($estado_nuevo, [0, 1, 2, 3])) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener estado anterior para el historial
    $stmt_old = $pdo->prepare("SELECT estado, estado_pago FROM tbl_pedido WHERE id_pedido = ?");
    $stmt_old->execute([$id_pedido]);
    $old_data = $stmt_old->fetch();

    if (!$old_data) {
        throw new Exception("Pedido no encontrado");
    }

    $estado_anterior = $old_data['estado'];
    $pago_actual = $old_data['estado_pago'];

    // Actualizar pedido
    $stmt = $pdo->prepare("UPDATE tbl_pedido SET estado = ? WHERE id_pedido = ?");
    $stmt->execute([$estado_nuevo, $id_pedido]);

    // Registrar historial
    $log = $pdo->prepare("INSERT INTO tbl_historial_pedido (id_pedido, cambiado_por, accion, estado_anterior, estado_nuevo, pago_anterior, pago_nuevo, motivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $log->execute([
        $id_pedido,
        $_SESSION['user_id'],
        'CAMBIO_ESTADO_ADMIN',
        $estado_anterior,
        $estado_nuevo,
        $pago_actual,
        $pago_actual,
        'Cambio de estado desde el panel de administración'
    ]);

    $pdo->commit();

    $status_names = [
        0 => 'Pendiente',
        1 => 'En Proceso',
        2 => 'Completado',
        3 => 'Cancelado'
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado a: ' . $status_names[$estado_nuevo]
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
