/**
 * MaiModal - Project-wide Modal Controller
 * Provides confirm, alert, and prompt functionality with a premium look.
 */
const MaiModal = {
    elements: {},
    callbacks: {
        onConfirm: null,
        onCancel: null
    },

    init() {
        this.elements = {
            overlay: document.getElementById('maiModal'),
            title: document.getElementById('maiModalTitle'),
            message: document.getElementById('maiModalMessage'),
            closeBtn: document.getElementById('maiModalClose'),
            cancelBtn: document.getElementById('maiModalCancel'),
            confirmBtn: document.getElementById('maiModalConfirm'),
            promptContainer: document.getElementById('maiModalPromptContainer'),
            promptLabel: document.getElementById('maiModalInputLabel'),
            promptInput: document.getElementById('maiModalInput')
        };

        if (this.elements.overlay) {
            this.elements.closeBtn?.addEventListener('click', () => this.close());
            this.elements.cancelBtn?.addEventListener('click', () => this.close());
            this.elements.overlay.addEventListener('click', (e) => {
                if (e.target === this.elements.overlay) this.close();
            });

            // ESC key to close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.elements.overlay.classList.contains('active')) {
                    this.close();
                }
            });

            this.elements.confirmBtn?.addEventListener('click', () => {
                if (this.callbacks.onConfirm) {
                    const value = this.elements.promptInput ? this.elements.promptInput.value : null;
                    this.callbacks.onConfirm(value);
                }
            });
        }
    },

    open(options = {}) {
        if (!this.elements.overlay) this.init();
        if (!this.elements.overlay) return;

        const {
            title = 'Confirmar Acción',
            message = '¿Estás seguro?',
            confirmText = 'Confirmar',
            cancelText = 'Cancelar',
            onConfirm = null,
            onCancel = null,
            type = 'primary', // primary, danger, success
            showPrompt = false,
            promptLabel = 'Motivo / Notas:',
            promptPlaceholder = 'Escribe aquí...',
            promptValue = ''
        } = options;

        this.elements.title.textContent = title;
        this.elements.message.textContent = message;
        this.elements.confirmBtn.textContent = confirmText;
        this.elements.cancelBtn.textContent = cancelText;

        // Handle type styles
        this.elements.confirmBtn.className = 'btn-modal confirm ' + type;

        // Handle prompt
        if (showPrompt && this.elements.promptContainer) {
            this.elements.promptContainer.style.display = 'block';
            this.elements.promptLabel.textContent = promptLabel;
            this.elements.promptInput.placeholder = promptPlaceholder;
            this.elements.promptInput.value = promptValue;
            setTimeout(() => this.elements.promptInput.focus(), 100);
        } else if (this.elements.promptContainer) {
            this.elements.promptContainer.style.display = 'none';
        }

        this.callbacks.onConfirm = onConfirm;
        this.callbacks.onCancel = onCancel;

        this.elements.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    confirm(options) {
        this.open({ ...options, showPrompt: false });
    },

    alert(options) {
        this.open({
            ...options,
            cancelText: '',
            showPrompt: false,
            onCancel: null
        });
        // Hide cancel button for alert
        if (this.elements.cancelBtn) this.elements.cancelBtn.style.display = 'none';
    },

    prompt(options) {
        this.open({ ...options, showPrompt: true });
    },

    close() {
        if (this.elements.overlay) {
            this.elements.overlay.classList.remove('active');
            document.body.style.overflow = '';
            if (this.elements.cancelBtn) this.elements.cancelBtn.style.display = 'block';
            if (this.callbacks.onCancel) this.callbacks.onCancel();
        }
    },

    showLoading(text = 'Cargando...') {
        if (this.elements.confirmBtn) {
            this.elements.confirmBtn.disabled = true;
            this.elements.cancelBtn.disabled = true;
            this.elements.confirmBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
        }
    },

    hideLoading(originalText = 'Confirmar') {
        if (this.elements.confirmBtn) {
            this.elements.confirmBtn.disabled = false;
            this.elements.cancelBtn.disabled = false;
            this.elements.confirmBtn.textContent = originalText;
        }
    }
};

// Auto-init on DOM load
document.addEventListener('DOMContentLoaded', () => MaiModal.init());
