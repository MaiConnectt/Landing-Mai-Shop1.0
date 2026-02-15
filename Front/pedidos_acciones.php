<?php
// Central handler for Pedro actions
session_start();
require_once 'conexion.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['id_rol'] ?? $_SESSION['role_id']; // Support both keys
$action = $_POST['action'] ?? '';
$id_pedido = (int) ($_POST['id_pedido'] ?? 0);

if (!$id_pedido) {
    die("ID de pedido no proporcionado");
}

try {
    $pdo->beginTransaction();

    // Get current state
    $stmt = $pdo->prepare("SELECT estado, estado_pago, id_vendedor FROM tbl_pedido WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Pedido no encontrado");
    }

    $estado_anterior = $order['estado'];
    $pago_anterior = $order['estado_pago'];
    $notas = $_POST['notas'] ?? '';

    $estado_nuevo = $estado_anterior;
    $pago_nuevo = $pago_anterior;
    $accion_label = '';

    // Action Logic
    switch ($action) {
        case 'subir_pago':
            if ($role_id != 2)
                throw new Exception("Solo los vendedores pueden subir pagos");
            // Check if seller owns the order
            if ($order['id_vendedor'] != $_SESSION['member_id'])
                throw new Exception("No tienes permiso sobre este pedido");

            if ($pago_anterior != 0 && $pago_anterior != 3)
                throw new Exception("El pago ya está en proceso o aprobado");

            // Handle File
            if (!isset($_FILES['comprobante'])) {
                // Check if POST is also empty (potential post_max_size issue)
                if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
                    throw new Exception("El archivo es demasiado grande (excede post_max_size)");
                }
                throw new Exception("No se recibió el archivo del comprobante");
            }

            if ($_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
                $err_code = $_FILES['comprobante']['error'];
                $msg = "Error desconocido al subir";
                switch ($err_code) {
                    case 1:
                        $msg = "El archivo excede upload_max_filesize en php.ini";
                        break;
                    case 2:
                        $msg = "El archivo excede MAX_FILE_SIZE en el formulario";
                        break;
                    case 3:
                        $msg = "Envío parcial del archivo";
                        break;
                    case 4:
                        $msg = "No se subió ningún archivo";
                        break;
                    case 6:
                        $msg = "Falta carpeta temporal en el servidor";
                        break;
                    case 7:
                        $msg = "Error al escribir el archivo en el disco";
                        break;
                }
                throw new Exception("Error de PHP ($err_code): " . $msg);
            }

            $upload_dir = __DIR__ . '/uploads/orders/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);

            $ext = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
            $filename = 'proof_' . $id_pedido . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $upload_dir . $filename)) {
                $ruta = 'uploads/orders/' . $filename;
                $monto = $_POST['monto'] ?? 0;
                $id_miembro = $_SESSION['seller_id'];

                // Deactivate old proofs if any
                $pdo->prepare("UPDATE tbl_comprobante_pago SET estado = 2 WHERE id_pedido = ?")->execute([$id_pedido]);

                // Insert new proof
                $ins_proof = $pdo->prepare("INSERT INTO tbl_comprobante_pago (id_pedido, ruta_imagen, monto, id_miembro, payment_method, estado, fecha_subida) VALUES (?, ?, ?, ?, 1, 0, NOW())");
                $ins_proof->execute([$id_pedido, $ruta, $monto, $id_miembro]);

                $_SESSION['success'] = "Comprobante de pago subido correctamente. El administrador lo revisará pronto.";
                $pago_nuevo = 1; // Comprobante subido
                $accion_label = 'SUBIR_COMPROBANTE';
            } else {
                throw new Exception("No se pudo mover el archivo");
            }
            break;

        case 'completar_pedido':
            if ($role_id != 2)
                throw new Exception("Solo los vendedores pueden completar pedidos");
            if ($estado_anterior != 1)
                throw new Exception("Solo se pueden completar pedidos en producción");

            $_SESSION['success'] = "¡Pedido #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . " marcado como completado!";
            $estado_nuevo = 2; // Completado
            $accion_label = 'MARCAR_COMPLETADO';
            break;

        case 'aprobar_pago':
            if ($role_id != 1)
                throw new Exception("Solo el administrador puede aprobar pagos");
            if ($pago_anterior != 1)
                throw new Exception("No hay un comprobante pendiente de validar");

            $_SESSION['success'] = "Pago del pedido #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . " aprobado exitosamente.";
            $pago_nuevo = 2; // Pago aprobado
            $accion_label = 'APROBAR_PAGO';
            // Update comprobante table too (Removing reviewed_by as it doesn't exist in tbl_comprobante_pago)
            $pdo->prepare("UPDATE tbl_comprobante_pago SET estado = 1 WHERE id_pedido = ? AND estado = 0")->execute([$id_pedido]);
            break;

        case 'rechazar_pago':
            if ($role_id != 1)
                throw new Exception("Solo el administrador puede rechazar pagos");
            if (empty($notas))
                throw new Exception("Debes indicar el motivo del rechazo");

            $_SESSION['success'] = "Pago del pedido #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . " rechazado.";
            $pago_nuevo = 3; // Pago rechazado
            $accion_label = 'RECHAZAR_PAGO';
            // Update comprobante table too (Using 'notas' if it exists, otherwise just state change)
            $pdo->prepare("UPDATE tbl_comprobante_pago SET estado = 2 WHERE id_pedido = ? AND estado = 0")->execute([$id_pedido]);
            break;

        case 'mandar_produccion':
            if ($role_id != 1)
                throw new Exception("Solo el administrador puede mandar a producción");
            if ($estado_anterior != 0)
                throw new Exception("El pedido ya no está pendiente");
            if ($pago_anterior != 2)
                throw new Exception("No se puede mandar a producción sin pago aprobado");

            $_SESSION['success'] = "Pedido #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . " enviado a producción.";
            $estado_nuevo = 1; // En Producción
            $accion_label = 'MANDAR_PRODUCCION';
            break;

        case 'cancelar_pedido':
            if ($role_id != 1)
                throw new Exception("Solo el administrador puede cancelar pedidos");
            if (empty($notas))
                throw new Exception("Debes indicar el motivo de la cancelación");

            $_SESSION['success'] = "Pedido #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . " cancelado correctamente.";
            $estado_nuevo = 3; // Cancelado
            $accion_label = 'CANCELAR_PEDIDO';
            break;

        default:
            throw new Exception("Acción no reconocida");
    }

    // Update Order
    $update = $pdo->prepare("UPDATE tbl_pedido SET estado = ?, estado_pago = ? WHERE id_pedido = ?");
    $update->execute([$estado_nuevo, $pago_nuevo, $id_pedido]);

    // Log History
    $log = $pdo->prepare("INSERT INTO tbl_historial_pedido (id_pedido, cambiado_por, accion, estado_anterior, estado_nuevo, pago_anterior, pago_nuevo, motivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $log->execute([$id_pedido, $user_id, $accion_label, $estado_anterior, $estado_nuevo, $pago_anterior, $pago_nuevo, $notas]);

    $pdo->commit();

    // Redirect or respond
    $redirect = ($role_id == 1) ? "dashboard/pedidos/ver.php?id=$id_pedido" : "seller/mis_pedidos.php";
    header("Location: $redirect");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();

    $_SESSION['error'] = $e->getMessage();

    // Determine redirect
    if (isset($_POST['id_pedido'])) {
        $id = (int) $_POST['id_pedido'];
        $redirect = ($role_id == 1) ? "dashboard/pedidos/ver.php?id=$id" : "seller/mis_pedidos.php";
    } else {
        $redirect = ($role_id == 1) ? "dashboard/dash.php" : "seller/seller_dash.php";
    }

    header("Location: $redirect");
    exit;
}
