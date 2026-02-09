/**
 * Mai Shop - Orders Module JavaScript
 * Handles order management interactions
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== STATUS CHANGE CONFIRMATION =====
    const statusSelects = document.querySelectorAll('.status-select');

    statusSelects.forEach(select => {
        const originalValue = select.value;

        select.addEventListener('change', function (e) {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            const orderNumber = this.dataset.orderNumber;

            showConfirmModal(
                'Cambiar Estado del Pedido',
                `¿Estás seguro de cambiar el estado del pedido ${orderNumber}?`,
                () => {
                    // Confirmed - submit the change
                    updateOrderStatus(orderId, newStatus, this);
                },
                () => {
                    // Cancelled - revert to original value
                    this.value = originalValue;
                }
            );
        });
    });

    // ===== DELETE CONFIRMATION =====
    const deleteButtons = document.querySelectorAll('.btn-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const orderId = this.dataset.orderId;
            const orderNumber = this.dataset.orderNumber;

            showConfirmModal(
                'Eliminar Pedido',
                `¿Estás seguro de eliminar el pedido ${orderNumber}? Esta acción no se puede deshacer.`,
                () => {
                    // Confirmed - delete order
                    deleteOrder(orderId);
                }
            );
        });
    });

    // ===== FILTER FORM =====
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            // Form will submit normally, this is just for validation
            this.submit();
        });
    }

    // ===== CLEAR FILTERS =====
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function () {
            window.location.href = 'pedidos.php';
        });
    }

    // ===== SEARCH INPUT DEBOUNCE =====
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit form after 500ms of no typing
                if (this.value.length >= 3 || this.value.length === 0) {
                    filterForm.submit();
                }
            }, 500);
        });
    }
});

// ===== MODAL FUNCTIONS =====
function showConfirmModal(title, message, onConfirm, onCancel) {
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('modalConfirm');
    const cancelBtn = document.getElementById('modalCancel');
    const closeBtn = document.getElementById('modalClose');

    modalTitle.textContent = title;
    modalMessage.textContent = message;

    // Show modal
    modal.classList.add('active');

    // Handle confirm
    confirmBtn.onclick = function () {
        modal.classList.remove('active');
        if (onConfirm) onConfirm();
    };

    // Handle cancel
    const handleCancel = function () {
        modal.classList.remove('active');
        if (onCancel) onCancel();
    };

    cancelBtn.onclick = handleCancel;
    closeBtn.onclick = handleCancel;

    // Close on overlay click
    modal.onclick = function (e) {
        if (e.target === modal) {
            handleCancel();
        }
    };
}

// ===== UPDATE ORDER STATUS =====
function updateOrderStatus(orderId, newStatus, selectElement) {
    fetch('cambiar_estado.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: orderId,
            status: newStatus
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                // Reload page after 1 second to update badge
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Error al actualizar el estado', 'error');
                // Revert select to original value
                selectElement.value = selectElement.dataset.originalValue || '0';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
            selectElement.value = selectElement.dataset.originalValue || '0';
        });
}

// ===== DELETE ORDER =====
function deleteOrder(orderId) {
    fetch('eliminar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload page after 1 second
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Error al eliminar el pedido', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
}

// ===== NOTIFICATION SYSTEM =====
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#20ba5a' : '#ff6b9d'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
