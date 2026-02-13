<?php
require_once 'seller_auth.php';

// Filtros
$status_filter = isset($_GET['status']) ? (int) $_GET['status'] : -1;
$records_per_page = 15;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Construir consulta
$where_clause = "WHERE o.id_member = ?";
$params = [$_SESSION['seller_id']];

if ($status_filter >= 0) {
    $where_clause .= " AND o.estado = ?";
    $params[] = $status_filter;
}

// Total de pedidos
try {
    $count_query = "SELECT COUNT(*) as total FROM tbl_pedido o $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
}

// Obtener pedidos
try {
    $query = "
        SELECT 
            o.id_pedido,
            o.fecha_creacion,
            o.estado,
            o.estado_pago,
            ot.total,
            o.telefono_contacto,
            o.monto_comision as commission
        FROM tbl_pedido o
        INNER JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
        $where_clause
        ORDER BY o.fecha_creacion DESC
        LIMIT $records_per_page OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge pending">Pendiente</span>';
        case 1:
            return '<span class="badge processing">En Proceso</span>';
        case 2:
            return '<span class="badge completed">Completado</span>';
        case 3:
            return '<span class="badge error">Cancelado</span>';
        default:
            return '<span class="badge">Desconocido</span>';
    }
}

function getPaymentBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge" style="background: #eee; color: #666;">Sin Pago</span>';
        case 1:
            return '<span class="badge processing">Por Validar</span>';
        case 2:
            return '<span class="badge completed">Aprobado</span>';
        case 3:
            return '<span class="badge error">Rechazado</span>';
        default:
            return '<span class="badge">Desconocido</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Mai Shop</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Mis Pedidos</h1>
                <p>Historial de ventas realizadas</p>
                
                <?php
                $success_msg = $_SESSION['success'] ?? null;
                $error_msg = $_SESSION['error'] ?? null;
                unset($_SESSION['success'], $_SESSION['error']);
                ?>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success" style="margin-top: 1rem; padding: 1rem 1.5rem; border-radius: 12px; background: #e6f9f0; color: #22543d; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-error" style="margin-top: 1rem; padding: 1rem 1.5rem; border-radius: 12px; background: #ffe6e6; color: #c53030; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Filtrar por Estado</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="?status=-1"
                            class="btn <?php echo $status_filter === -1 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Todos</a>
                        <a href="?status=0"
                            class="btn <?php echo $status_filter === 0 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Pendiente</a>
                        <a href="?status=1"
                            class="btn <?php echo $status_filter === 1 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">En Proceso</a>
                        <a href="?status=2"
                            class="btn <?php echo $status_filter === 2 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Completado</a>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No se encontraron pedidos</p>
                        <a href="nuevo_pedido.php" class="btn btn-primary" style="margin-top: 1rem;"><i
                                class="fas fa-plus"></i> Crear Pedido</a>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Contacto</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Comisión</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 600;">#
                                        <?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['telefono_contacto'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($order['fecha_creacion'])); ?>
                                    </td>
                                    <td style="font-weight: 600;">$
                                        <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                    </td>
                                    <td style="color: var(--success); font-weight: 600;">$
                                        <?php echo number_format($order['commission'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($order['estado']); ?>
                                    </td>
                                    <td>
                                        <?php echo getPaymentBadge($order['estado_pago']); ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <a href="https://wa.me/57<?php echo preg_replace('/[^0-9]/', '', $order['telefono_contacto']); ?>"
                                                target="_blank" class="btn btn-secondary"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #25D366; color: white; border: none;"
                                                title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>

                                            <?php if ($order['estado_pago'] == 0 || $order['estado_pago'] == 3): ?>
                                                <button
                                                    onclick="openUploadModal(<?php echo $order['id_pedido']; ?>, <?php echo $order['total']; ?>)"
                                                    class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                                    title="Subir Pago">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($order['estado'] == 1): ?>
                                                <button onclick="markAsCompleted(<?php echo $order['id_pedido']; ?>)"
                                                    class="btn btn-primary"
                                                    style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #20ba5a;"
                                                    title="Completar">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($total_pages > 1): ?>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn btn-secondary"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>
                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn <?php echo $i === $current_page ? 'btn-primary' : 'btn-secondary'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn btn-secondary"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <!-- Modal Subir Comprobante -->
    <div id="uploadModal" class="modal"
        style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div
            style="background:white; margin:10% auto; padding:2rem; width:90%; max-width:500px; border-radius:16px; position:relative;">
            <span onclick="closeModal()"
                style="position:absolute; right:1.5rem; top:1rem; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 style="margin-bottom:1rem; font-family: 'Playfair Display', serif;">Subir Comprobante</h2>
            <form action="../pedidos_acciones.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="subir_pago">
                <input type="hidden" name="id_pedido" id="modal_id_pedido">
                <div style="margin-bottom:1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Total del Pedido:</label>
                    <input type="text" id="modal_total_display" class="form-input" readonly value="$0">
                    <input type="hidden" name="monto" id="modal_total_val">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Seleccionar Imagen:</label>
                    <input type="file" name="comprobante" class="form-input" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fas fa-cloud-upload-alt"></i> Confirmar Envío
                </button>
            </form>
        </div>
    </div>

    <script src="seller.js"></script>
    <script>
        const uploadForm = document.querySelector('#uploadModal form');
        const fileInput = uploadForm.querySelector('input[name="comprobante"]');
        const MAX_SIZE_MB = 2;

        function openUploadModal(id, total) {
            document.getElementById('modal_id_pedido').value = id;
            document.getElementById('modal_total_display').value = '$' + total.toLocaleString('es-CO');
            document.getElementById('modal_total_val').value = total;
            document.getElementById('uploadModal').style.display = 'block';
            fileInput.value = ''; // Reset file input
        }

        uploadForm.addEventListener('submit', function(e) {
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                if (fileSize > MAX_SIZE_MB) {
                    e.preventDefault();
                    MaiModal.alert({
                        title: 'Archivo Demasiado Grande',
                        message: `El comprobante no debe pesar más de ${MAX_SIZE_MB}MB. Por favor, reduce el tamaño de la imagen o toma una captura de pantalla más liviana.`,
                        type: 'danger'
                    });
                }
            }
        });

        function closeModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function markAsCompleted(id) {
            MaiModal.confirm({
                title: 'Finalizar Pedido',
                message: '¿Estás seguro de marcar el pedido #' + id + ' como completado? Esta acción finalizará la comisión.',
                onConfirm: () => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../pedidos_acciones.php';

                    const inputAction = document.createElement('input');
                    inputAction.type = 'hidden';
                    inputAction.name = 'action';
                    inputAction.value = 'completar_pedido';
                    form.appendChild(inputAction);

                    const inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id_pedido';
                    inputId.value = id;
                    form.appendChild(inputId);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target == document.getElementById('uploadModal')) {
                closeModal();
            }
        }
    </script>
</body>

</html>