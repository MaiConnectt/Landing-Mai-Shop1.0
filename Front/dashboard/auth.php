<?php
// Authentication and session management for dashboard
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // User is not logged in, redirect to login page
    header('Location: ../login/login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../conexion.php';

// Fetch current user information from database
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $current_user = $user_data;
        // Ensure role is available (prefer session role string if available for text display)
        $current_user['role'] = $_SESSION['role'] ?? 'user';
    } else {
        // User not found in DB (integrity issue)
        session_unset();
        session_destroy();
        header('Location: ../login/login.php?error=user_not_found');
        exit;
    }
} catch (PDOException $e) {
    // Fallback if DB fails
    $current_user = [
        'id_usuario' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'] ?? 'user',
        'nombre' => 'Usuario',
        'apellido' => ''
    ];
}

// Optional: Check session timeout (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session has expired
    session_unset();
    session_destroy();
    header('Location: ../login/login.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>