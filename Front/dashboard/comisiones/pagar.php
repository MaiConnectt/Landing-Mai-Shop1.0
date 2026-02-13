<?php
session_start();
require_once '../../conexion.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../login/login.php');
    exit;
}

$id_member = $_GET['id_member'] ?? null;
if (!$id_member) {
    header('Location: index.php');
    exit;
}

// Fetch Member Info
$stmt = $pdo->prepare("
    SELECT m.*, u.nombre, u.apellido, u.email 
    FROM tbl_miembro m 
    JOIN tbl_usuario u ON m.id_usuario = u.id_usuario 
    WHERE m.id_miembro = ?
");
$stmt->execute([$id_member]);
$member = $stmt->fetch();

if (!$member) {
    die("Vendedor no encontrado.");
}

// Fetch Pending Orders
$stmt_orders = $pdo->prepare("
    SELECT 
        o.id_pedido, o.fecha_creacion, o.estado,
        ot.total as order_total,
        o.monto_comision
    FROM tbl_pedido o
    JOIN tbl_miembro m ON o.id_member = m.id_miembro
    JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
    WHERE o.id_member = ? 
    AND o.estado = 2 
    AND (o.id_pago_comision IS NULL OR o.id_pago_comision = 0)
    ORDER BY o.fecha_creacion ASC
");
$stmt_orders->execute([$id_member]);
$orders = $stmt_orders->fetchAll();

$total_commission = 0;
foreach ($orders as $o) {
    $total_commission += $o['monto_comision'];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $notes = $_POST['notes'] ?? '';
        $payment_amount = $total_commission;
        $proof_path = null;

        // 1. Upload Proof
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/comisiones/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $filename = 'comm_' . time() . '_' . uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $filename)) {
                $proof_path = 'uploads/comisiones/' . $filename;
            } else {
                throw new Exception("Error al subir comprobante.");
            }
        }

        // 2. Insert Payment Record
        $payment_method = $_POST['payment_method'] ?? 1;
        
        // status 2 = paid/approved
        $stmt_pay = $pdo->prepare("
            INSERT INTO tbl_comprobante_pago (id_miembro, monto, ruta_imagen, estado, notas, fecha_subida, payment_method)
            VALUES (?, ?, ?, 2, ?, NOW(), ?)
            RETURNING id_comprobante_pago
        ");
        $stmt_pay->execute([$id_member, $payment_amount, $proof_path, $notes, $payment_method]);
        $payout_id = $stmt_pay->fetchColumn();

        // 3. Update Orders
        if ($payout_id) {
            $order_ids = array_column($orders, 'id_pedido');
            if (!empty($order_ids)) {
                $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
                $sql_update = "UPDATE tbl_pedido SET id_pago_comision = ? WHERE id_pedido IN ($placeholders)";
                $params = array_merge([$payout_id], $order_ids);
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute($params);
            }
        }

        $pdo->commit();
        header("Location: recibo.php?id=" . $payout_id);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagar Comisiones - Mai Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="comisiones.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Removed -->

        <main class="main-content" style="margin-left: 0; width: 100%;">
            <div class="dashboard-header">
                <div class="header-left">
                    <a href="index.php" class="btn btn-secondary" style="margin-bottom: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; background: #e2e8f0; color: #4a5568;">
                        <i class="fas fa-arrow-left"></i> Volver a Comisiones
                    </a>
                    <h1>Registrar Pago de Comisiones</h1>
                    <p>Vendedor: <strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong></p>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div style="background: #FEB2B2; color: #C53030; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="content-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
                
                <!-- Orders List -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Pedidos a Pagar (<?php echo count($orders); ?>)</h2>
                    </div>
                    <?php if (empty($orders)): ?>
                        <p>No hay pedidos pendientes para este vendedor.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Pedido #</th>
                                    <th>Total Venta</th>
                                    <th>Comisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($o['created_at'])); ?></td>
                                        <td>#<?php echo str_pad($o['id_order'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td>$<?php echo number_format($o['order_total'], 0, ',', '.'); ?></td>
                                        <td>
                                            <strong>$<?php echo number_format($o['commission_amount'], 0, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Payment Form -->
                <div>
                    <form method="POST" enctype="multipart/form-data" class="content-card">
                        <h2 class="card-title" style="margin-bottom: 1.5rem;">Resumen del Pago</h2>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; color: var(--gray-600); margin-bottom: 0.5rem;">Total a Pagar</label>
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                                $<?php echo number_format($total_commission, 0, ',', '.'); ?>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--gray-500);">
                                Tasa de comisión: <?php echo $member['commission_percentage']; ?>%
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Método de Pago</label>
                            <select name="payment_method" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 8px; background: white;">
                                <option value="1">Transferencia Bancaria</option>
                                <option value="2">Nequi</option>
                                <option value="3">Daviplata</option>
                                <option value="4">Efectivo</option>
                                <option value="5">Otro</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">
                                Comprobante de Transferencia *
                            </label>
                            <input type="file" name="payment_proof" required accept="image/*" 
                                style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 8px;">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Notas (Opcional)</label>
                            <textarea name="notes" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 8px;"></textarea>
                        </div>

                        <button type="submit" class="pay-btn" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 1rem;" 
                            <?php echo empty($orders) ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-circle"></i> Confirmar Pago
                        </button>
                    </form>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
