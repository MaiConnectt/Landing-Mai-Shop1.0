<?php
require_once 'auth.php';
require_once '../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $notification_id = $_POST['notification_id'] ?? null;

    if ($notification_id === 'all') {
        // Marcar todas como leídas
        $query = "UPDATE tbl_notification SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    } else {
        // Marcar una específica como leída
        $query = "UPDATE tbl_notification SET is_read = TRUE WHERE id_notification = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$notification_id, $_SESSION['user_id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al actualizar notificación'
    ]);
}
