<?php
// Seller Authentication Middleware
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Verificar que el usuario sea un miembro del equipo (role_id = 2)
if ($_SESSION['role_id'] != 2) {
    // Si es admin u otro rol, redirigir al dashboard correspondiente
    if ($_SESSION['role_id'] == 1) {
        header('Location: ../dashboard/dash.php');
    } else {
        header('Location: ../login/login.php');
    }
    exit;
}

// Obtener información del vendedor
require_once '../conexion.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.id_member,
            m.commission_percentage,
            m.hire_date,
            u.first_name,
            u.last_name,
            u.email
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
        WHERE u.id_user = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);
    $seller = $stmt->fetch();

    if (!$seller) {
        // El vendedor no existe
        session_destroy();
        header('Location: ../login/login.php?error=no_seller');
        exit;
    }

    // Guardar información del vendedor en la sesión
    $_SESSION['seller_id'] = $seller['id_member'];
    $_SESSION['seller_name'] = $seller['first_name'] . ' ' . $seller['last_name'];
    $_SESSION['commission_percentage'] = $seller['commission_percentage'];

} catch (PDOException $e) {
    error_log("Error en seller_auth: " . $e->getMessage());
    header('Location: ../login/login.php?error=db');
    exit;
}
