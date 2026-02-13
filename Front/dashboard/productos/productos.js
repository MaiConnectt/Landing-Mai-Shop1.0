// =====================================================
// Mai Shop - Products Module JavaScript
// =====================================================

document.addEventListener('DOMContentLoaded', function () {
    // Delete Product Confirmation
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;

            MaiModal.confirm({
                title: 'Eliminar Producto',
                message: `¿Estás seguro de que deseas eliminar "${productName}"? Esta acción no se puede deshacer.`,
                confirmText: 'Eliminar',
                onConfirm: () => {
                    deleteProduct(productId);
                }
            });
        });
    });

    // Search Debouncing
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }
});

// Delete Product
function deleteProduct(productId) {
    // Show loading in modal
    MaiModal.showLoading('Eliminando...');

    // Send delete request
    fetch('eliminar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${productId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaiModal.close();
                showNotification('Producto eliminado exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MaiModal.alert({
                    title: 'Error',
                    message: data.message || 'Error al eliminar producto',
                    type: 'danger'
                });
                MaiModal.hideLoading('Eliminar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            MaiModal.alert({
                title: 'Error de Red',
                message: 'No se pudo comunicar con el servidor.',
                type: 'danger'
            });
            MaiModal.hideLoading('Eliminar');
        });
}

// Show Notification (Toast-style for success messages)
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    const icon = type === 'success' ? 'fa-check-circle' :
        type === 'error' ? 'fa-exclamation-circle' :
            'fa-info-circle';

    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}
