// Team Module JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalClose = document.getElementById('modalClose');
    const modalCancel = document.getElementById('modalCancel');
    const modalConfirm = document.getElementById('modalConfirm');

    let currentAction = null;
    let currentSellerId = null;

    // Delete buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            currentSellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            modalTitle.textContent = 'Eliminar Vendedor';
            modalMessage.textContent = `¿Estás seguro de que deseas eliminar a ${sellerName}? Esta acción no se puede deshacer.`;

            currentAction = 'delete';
            modal.classList.add('active');
        });
    });

    // Modal close handlers
    modalClose.addEventListener('click', closeModal);
    modalCancel.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Modal confirm handler
    modalConfirm.addEventListener('click', function () {
        if (currentAction === 'delete' && currentSellerId) {
            deleteSeller(currentSellerId);
        }
    });

    function closeModal() {
        modal.classList.remove('active');
        currentAction = null;
        currentSellerId = null;
    }

    function deleteSeller(sellerId) {
        // Show loading state
        modalConfirm.disabled = true;
        modalConfirm.textContent = 'Eliminando...';

        // Send delete request
        fetch('eliminar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${sellerId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show updated list
                    window.location.reload();
                } else {
                    alert('Error al eliminar vendedor: ' + (data.message || 'Error desconocido'));
                    modalConfirm.disabled = false;
                    modalConfirm.textContent = 'Confirmar';
                    closeModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar vendedor. Por favor, intenta de nuevo.');
                modalConfirm.disabled = false;
                modalConfirm.textContent = 'Confirmar';
                closeModal();
            });
    }

    // Search functionality
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

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // ESC to close modal
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }

        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    // Animate cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    entry.target.style.transition = 'all 0.5s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);

                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const sellerCards = document.querySelectorAll('.seller-card');
    sellerCards.forEach(card => {
        observer.observe(card);
    });
});
