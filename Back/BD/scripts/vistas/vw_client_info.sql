-- =====================================================
-- Vista: vw_client_info
-- Descripción: Combina información de clientes y usuarios incluyendo email
-- =====================================================

CREATE OR REPLACE VIEW vw_client_info AS
SELECT 
    c.id_client,
    c.id_user,
    u.first_name,
    u.last_name,
    u.email,
    c.phone,
    c.address,
    u.role_id
FROM tbl_client c
INNER JOIN tbl_user u ON c.id_user = u.id_user;

-- Comentario
COMMENT ON VIEW vw_client_info IS 'Vista que combina información de clientes y usuarios incluyendo email';
