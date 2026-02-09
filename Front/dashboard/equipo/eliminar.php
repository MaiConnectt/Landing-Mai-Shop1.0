<?php
require_once '../auth.php';
require_once '../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$seller_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($seller_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de vendedor inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar que el vendedor existe
    $check_stmt = $pdo->prepare("SELECT id_member FROM tbl_member WHERE id_member = ?");
    $check_stmt->execute([$seller_id]);

    if (!$check_stmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Vendedor no encontrado']);
        exit;
    }

    // En lugar de eliminar, marcar como inactivo
    $stmt = $pdo->prepare("UPDATE tbl_member SET status = 'inactive' WHERE id_member = ?");
    $stmt->execute([$seller_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Vendedor desactivado exitosamente'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar vendedor: ' . $e->getMessage()
    ]);
}
