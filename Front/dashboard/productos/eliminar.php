<?php
require_once '../auth.php';
require_once '../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$product_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get product image to delete file
    $stmt = $pdo->prepare("SELECT main_image FROM tbl_product WHERE id_product = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }

    // Delete product (CASCADE will delete related images and variants)
    $stmt = $pdo->prepare("DELETE FROM tbl_product WHERE id_product = ?");
    $stmt->execute([$product_id]);

    // Delete image file if exists
    if (!empty($product['main_image'])) {
        $image_path = "../../uploads/productos/" . $product['main_image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()]);
}
