<?php
session_start();
require_once '../../conexion.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../login/login.php');
    exit;
}

$id_payout = $_GET['id'] ?? null;
if (!$id_payout) {
    die("ID de pago no especificado.");
}

// Fetch Payout Info
$stmt = $pdo->prepare("
    SELECT pp.*, u.nombre, u.apellido, u.email, m.universidad
    FROM tbl_comprobante_pago pp
    JOIN tbl_miembro m ON pp.id_miembro = m.id_miembro
    JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
    WHERE pp.id_comprobante_pago = ?
");
$stmt->execute([$id_payout]);
$payout = $stmt->fetch();

if (!$payout) {
    die("Pago no encontrado.");
}

// Fetch Orders covered by this payout
$stmt_orders = $pdo->prepare("
    SELECT o.id_pedido, o.fecha_creacion, ot.total
    FROM tbl_pedido o
    JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
    WHERE o.id_pago_comision = ?
    ORDER BY o.fecha_creacion ASC
");
$stmt_orders->execute([$id_payout]);
$orders = $stmt_orders->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #
        <?php echo $id_payout; ?>
    </title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #FF6B9D;
            margin: 0;
        }

        .subtitle {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-box h3 {
            font-size: 14px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 5px;
        }

        .info-box p {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .table-container {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            background: #f9f9f9;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-row td {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            border-bottom: none;
        }

        .proof-section {
            margin-top: 40px;
            text-align: center;
        }

        .proof-img {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
            padding: 5px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer;">Imprimir
            Recibo</button>
        <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #333;">Volver al Panel</a>
    </div>

    <div class="header">
        <!-- Assuming logo exists at ../img/mai.png relative to root? Path issue. Using text if logo fails -->
        <img src="../../img/mai.png" alt="Mai Shop" class="logo">
        <h1 class="title">Mai Shop</h1>
        <p class="subtitle">Comprobante de Pago de Comisiones</p>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Beneficiario</h3>
            <p>
                <?php echo htmlspecialchars($payout['nombre'] . ' ' . $payout['apellido']); ?>
            </p>
            <p style="font-size: 14px; color: #666;">
                <?php echo htmlspecialchars($payout['email'] ?? ''); ?>
            </p>
            <p style="font-size: 14px; color: #666;">
                <?php echo htmlspecialchars($payout['universidad'] ?? ''); ?>
            </p>
        </div>
        <div class="info-box" style="text-align: right;">
            <h3>Recibo N°</h3>
            <p>#
                <?php echo str_pad($payout['id_comprobante_pago'], 6, '0', STR_PAD_LEFT); ?>
            </p>
            <h3>Fecha de Pago</h3>
            <p>
                <?php echo date('d/m/Y H:i', strtotime($payout['fecha_subida'])); ?>
            </p>
        </div>
    </div>

    <div class="table-container">
        <h3>Detalle de Pedidos</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha Pedido</th>
                    <th>Pedido #</th>
                    <th style="text-align: right;">Valor Venta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <?php echo date('d/m/Y', strtotime($order['fecha_creacion'])); ?>
                        </td>
                        <td>#
                            <?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td style="text-align: right;">$
                            <?php echo number_format($order['total'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">Total Pagado (Comisión):</td>
                    <td style="text-align: right;">$
                        <?php echo number_format($payout['amount'], 0, ',', '.'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if ($payout['proof_image_path']): ?>
        <div class="proof-section">
            <h3>Comprobante de Transferencia</h3>
            <!-- Adjust path. DB stores 'uploads/comisiones/...'. Relative from here needs ../../../ -->
            <img src="../../../<?php echo htmlspecialchars($payout['proof_image_path']); ?>" alt="Comprobante"
                class="proof-img">
        </div>
    <?php endif; ?>

    <div class="footer">
        Este documento sirve como constancia del pago de comisiones realizado por Mai Shop.<br>
        Generado el
        <?php echo date('d/m/Y H:i', strtotime($payout['fecha_subida'])); ?>
    </div>
</body>

</html>