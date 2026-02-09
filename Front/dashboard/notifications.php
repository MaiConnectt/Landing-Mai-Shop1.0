<?php
require_once 'auth.php';
require_once '../conexion.php';

header('Content-Type: application/json');

try {
    // Obtener notificaciones no leídas del usuario
    $query = "
        SELECT 
            id_notification,
            type,
            title,
            message,
            related_id,
            created_at,
            CASE 
                WHEN created_at > NOW() - INTERVAL '1 hour' THEN 'Hace ' || EXTRACT(MINUTE FROM NOW() - created_at)::INTEGER || ' minutos'
                WHEN created_at > NOW() - INTERVAL '1 day' THEN 'Hace ' || EXTRACT(HOUR FROM NOW() - created_at)::INTEGER || ' horas'
                ELSE TO_CHAR(created_at, 'DD/MM/YYYY HH24:MI')
            END as time_ago
        FROM tbl_notification
        WHERE user_id = ? AND is_read = FALSE
        ORDER BY created_at DESC
        LIMIT 20
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al cargar notificaciones'
    ]);
}
