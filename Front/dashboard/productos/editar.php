<?php
require_once '../auth.php';
require_once '../../conexion.php';

$error = null;
$success = false;

// Get product ID
$id_product = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_product <= 0) {
    header('Location: productos.php');
    exit;
}

// Check success message
if (isset($_GET['success'])) {
    $success = true;
}

// Fetch existing product
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_product WHERE id_product = ?");
    $stmt->execute([$id_product]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: productos.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al cargar producto: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $preparation_time = trim($_POST['preparation_time'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $allergens = trim($_POST['allergens'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $stock_status = $_POST['stock_status'] ?? 'available';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        $display_order = (int)($_POST['display_order'] ?? 0);

        // Validate required fields
        if (empty($name)) {
            throw new Exception('El nombre del producto es obligatorio');
        }
        if ($price <= 0) {
            throw new Exception('El precio debe ser mayor a 0');
        }

        // Handle image upload
        $main_image = $product['main_image']; // Default to existing
        $new_image_uploaded = false;

        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB

            $file_type = $_FILES['main_image']['type'];
            $file_size = $_FILES['main_image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Formato de imagen no permitido. Use JPG, PNG o WEBP');
            }

            if ($file_size > $max_size) {
                throw new Exception('La imagen es demasiado grande. Máximo 5MB');
            }

            // Generate unique filename
            $extension = pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('product_') . '.' . $extension;
            $upload_path = '../../uploads/productos/' . $filename;

            if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_path)) {
                throw new Exception('Error al subir la imagen');
            }

            $main_image = $filename; // Update with new filename
            $new_image_uploaded = true;
        }

        // Update product
        $stmt = $pdo->prepare("
            UPDATE tbl_product SET
                name = ?, short_description = ?, description = ?, price = ?, category_id = ?,
                preparation_time = ?, ingredients = ?, allergens = ?, status = ?, stock_status = ?,
                is_featured = ?, is_new = ?, display_order = ?, main_image = ?
            WHERE id_product = ?
        ");

        $stmt->execute([
            $name, $short_description, $description, $price, $category_id,
            $preparation_time, $ingredients, $allergens, $status, $stock_status,
            $is_featured, $is_new, $display_order, $main_image, $id_product
        ]);

        // If updated successfully and new image uploaded, delete old one if exists
        if ($new_image_uploaded && !empty($product['main_image'])) {
            $old_image_path = '../../uploads/productos/' . $product['main_image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }

        $pdo->commit();
        
        // Refresh product data
        $product['name'] = $name;
        // ... (Usually redirect to show success)
        header("Location: editar.php?id=$id_product&success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();

        // Delete newly uploaded image if error
        if (isset($new_image_uploaded) && $new_image_uploaded && file_exists('../../uploads/productos/' . $main_image)) {
            unlink('../../uploads/productos/' . $main_image);
        }
    }
}

// Get categories for dropdown
try {
    $categories_stmt = $pdo->query("SELECT * FROM tbl_category ORDER BY display_order ASC");
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Mai Shop</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="productos.css">
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '..'; include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="form-header">
                <div>
                    <h1><i class="fas fa-edit"></i> Editar Producto</h1>
                    <p>Modificando: <strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i>
                    ¡Producto actualizado exitosamente!
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="editar.php?id=<?php echo $id_product; ?>" enctype="multipart/form-data" class="product-form" id="productForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </h2>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="name">Nombre del Producto <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required
                                placeholder="Ej: Torta de Chocolate Premium"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="category_id">Categoría <span class="required">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id_category']; ?>"
                                        <?php echo ((isset($_POST['category_id']) && $_POST['category_id'] == $cat['id_category']) || (!isset($_POST['category_id']) && $product['category_id'] == $cat['id_category'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Precio <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <span class="input-icon">$</span>
                                <input type="number" id="price" name="price" class="form-control" required min="0"
                                    step="100" placeholder="50000"
                                    value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price']); ?>">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="short_description">Descripción Corta</label>
                            <input type="text" id="short_description" name="short_description" class="form-control"
                                maxlength="500" placeholder="Breve descripción para la tarjeta del producto"
                                value="<?php echo htmlspecialchars($_POST['short_description'] ?? $product['short_description']); ?>">
                            <small class="form-hint">Máximo 500 caracteres</small>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Descripción Completa</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                placeholder="Descripción detallada del producto"><?php echo htmlspecialchars($_POST['description'] ?? $product['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-image"></i>
                        Imagen del Producto
                    </h2>

                    <div class="image-upload-container">
                        <div class="current-image" style="margin-bottom: 15px; text-align: center;">
                             <?php if (!empty($product['main_image'])): ?>
                                <img src="../../uploads/productos/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                     alt="Imagen actual" 
                                     style="max-width: 200px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">Imagen actual</p>
                            <?php else: ?>
                                <div style="width: 100px; height: 100px; background: #eee; border-radius: 8px; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #aaa;">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                                <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">Sin imagen</p>
                            <?php endif; ?>
                        </div>

                        <div class="image-preview" id="imagePreview">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Haz clic para cambiar la imagen</p>
                            <small>JPG, PNG o WEBP (máx. 5MB)</small>
                        </div>
                        <input type="file" id="main_image" name="main_image" accept="image/jpeg,image/png,image/webp"
                            class="image-input">
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-list-ul"></i>
                        Detalles Adicionales
                    </h2>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="preparation_time">Tiempo de Preparación</label>
                            <input type="text" id="preparation_time" name="preparation_time" class="form-control"
                                placeholder="Ej: 2 días, 24 horas"
                                value="<?php echo htmlspecialchars($_POST['preparation_time'] ?? $product['preparation_time']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" <?php echo ((isset($_POST['status']) && $_POST['status'] === 'active') || (!isset($_POST['status']) && $product['status'] === 'active')) ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactive" <?php echo ((isset($_POST['status']) && $_POST['status'] === 'inactive') || (!isset($_POST['status']) && $product['status'] === 'inactive')) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="stock_status">Disponibilidad</label>
                            <select id="stock_status" name="stock_status" class="form-control">
                                <option value="available" <?php echo ((isset($_POST['stock_status']) && $_POST['stock_status'] === 'available') || (!isset($_POST['stock_status']) && $product['stock_status'] === 'available')) ? 'selected' : ''; ?>>Disponible</option>
                                <option value="out_of_stock" <?php echo ((isset($_POST['stock_status']) && $_POST['stock_status'] === 'out_of_stock') || (!isset($_POST['stock_status']) && $product['stock_status'] === 'out_of_stock')) ? 'selected' : ''; ?>>Agotado</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Orden de Visualización</label>
                            <input type="number" id="display_order" name="display_order" class="form-control" min="0"
                                value="<?php echo htmlspecialchars($_POST['display_order'] ?? $product['display_order']); ?>">
                            <small class="form-hint">Menor número aparece primero</small>
                        </div>

                        <div class="form-group full-width">
                            <label for="ingredients">Ingredientes Principales</label>
                            <textarea id="ingredients" name="ingredients" class="form-control" rows="2"
                                placeholder="Ej: Harina, huevos, chocolate, mantequilla, azúcar"><?php echo htmlspecialchars($_POST['ingredients'] ?? $product['ingredients']); ?></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="allergens">Alérgenos</label>
                            <textarea id="allergens" name="allergens" class="form-control" rows="2"
                                placeholder="Ej: Gluten, huevo, lácteos, frutos secos"><?php echo htmlspecialchars($_POST['allergens'] ?? $product['allergens']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Product Features -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        Características
                    </h2>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured" value="1"
                                <?php echo ((isset($_POST['is_featured']) && $_POST['is_featured']) || (!isset($_POST) && $product['is_featured'])) ? 'checked' : ''; ?>>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                <strong>Producto Destacado</strong>
                                <small>Se mostrará prominentemente en el catálogo</small>
                            </span>
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_new" value="1"
                                <?php echo ((isset($_POST['is_new']) && $_POST['is_new']) || (!isset($_POST) && $product['is_new'])) ? 'checked' : ''; ?>>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-text">
                                <strong>Producto Nuevo</strong>
                                <small>Se mostrará con la etiqueta "Nuevo"</small>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="productos.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <!-- Delete Button inside Edit -->
                     <button type="button" class="btn-cancel" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; margin-left: auto;" onclick="confirmDelete(<?php echo $product['id_product']; ?>)">
                        <i class="fas fa-trash"></i> Eliminar Producto
                    </button>
                </div>
            </form>
        </main>
    </div>
    
     <!-- Confirmation Modal (using existing delete logic from productos.js?) -->
    <!-- We might need to include productos.js or inline script -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Confirmar Acción</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage">¿Estás seguro de eliminar este producto?</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal cancel" id="modalCancel">Cancelar</button>
                <button class="btn-modal confirm" id="modalConfirm">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="../dashboard.js"></script>
    <script src="productos.js"></script>
    <script>
        // Image Preview
        const imageInput = document.getElementById('main_image');
        const imagePreview = document.getElementById('imagePreview');

        imagePreview.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    imagePreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });

        // Basic Delete Logic for Edit Page
        function confirmDelete(id) {
             const modal = document.getElementById('confirmModal');
             const confirmBtn = document.getElementById('modalConfirm');
             const cancelBtn = document.getElementById('modalCancel');
             const closeBtn = document.getElementById('modalClose');
             
             modal.classList.add('active');
             
             const handleDelete = () => {
                fetch('eliminar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        window.location.href = 'productos.php';
                    } else {
                        alert(data.message);
                    }
                });
                modal.classList.remove('active');
                confirmBtn.removeEventListener('click', handleDelete);
             };

             confirmBtn.onclick = handleDelete;
             
             const closeModal = () => {
                 modal.classList.remove('active');
                 confirmBtn.removeEventListener('click', handleDelete);
             };
             
             cancelBtn.onclick = closeModal;
             closeBtn.onclick = closeModal;
        }
    </script>
</body>

</html>
