<?php
/**
 * Mai Shop - Shared Modals Component
 * This file should be included in the footer of dashboard/seller pages
 */
?>

<!-- Global Confirmation Modal -->
<div class="modal-overlay" id="maiModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="maiModalTitle">Confirmar Acción</h3>
            <button class="modal-close" id="maiModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <p id="maiModalMessage">¿Estás seguro de realizar esta acción?</p>

            <!-- Prompt Container (Hidden by default) -->
            <div id="maiModalPromptContainer" class="modal-prompt-container" style="display: none;">
                <label for="maiModalInput" class="modal-prompt-label" id="maiModalInputLabel">Motivo / Notas:</label>
                <textarea id="maiModalInput" class="modal-prompt-input" rows="3"
                    placeholder="Escribe aquí..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal cancel" id="maiModalCancel">Cancelar</button>
            <button class="btn-modal confirm" id="maiModalConfirm">Confirmar</button>
        </div>
    </div>
</div>