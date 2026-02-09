-- =====================================================
-- Vista: vw_payment_proof_details
-- Descripción: Vista detallada de comprobantes de pago con información relacionada
-- =====================================================

CREATE OR REPLACE VIEW vw_payment_proof_details AS
SELECT 
    pp.id_payment_proof,
    pp.id_order,
    o.id_client,
    o.id_member,
    pp.payment_method,
    pm.method_name,
    pp.proof_image_path,
    pp.amount,
    pp.uploaded_at,
    pp.status,
    pp.reviewed_by,
    CONCAT(reviewer.first_name, ' ', reviewer.last_name) AS reviewer_name,
    pp.reviewed_at,
    pp.notes
FROM tbl_payment_proof pp
INNER JOIN tbl_order o ON pp.id_order = o.id_order
INNER JOIN tbl_payment_method pm ON pp.payment_method = pm.id_payment_method
LEFT JOIN tbl_user reviewer ON pp.reviewed_by = reviewer.id_user;

-- Comentario
COMMENT ON VIEW vw_payment_proof_details IS 'Vista detallada de comprobantes de pago con información relacionada';
