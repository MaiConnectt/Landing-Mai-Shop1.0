// Team Module JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Delete buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const sellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            MaiModal.confirm({
                title: 'Eliminar Vendedor',
                message: `¿Estás seguro de que deseas eliminar a ${sellerName}? Esta acción no se puede deshacer.`,
                confirmText: 'Eliminar',
                onConfirm: () => {
                    deleteSeller(sellerId);
                }
            });
        });
    });

    function deleteSeller(sellerId) {
        // Show loading state
        MaiModal.showLoading('Eliminando...');

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
                    MaiModal.alert({
                        title: 'Error',
                        message: 'Error al eliminar vendedor: ' + (data.message || 'Error desconocido'),
                        type: 'danger'
                    });
                    MaiModal.hideLoading('Eliminar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                MaiModal.alert({
                    title: 'Error',
                    message: 'Error al eliminar vendedor. Por favor, intenta de nuevo.',
                    type: 'danger'
                });
                MaiModal.hideLoading('Eliminar');
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
        if (e.key === 'Escape') {
            MaiModal.close();
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
