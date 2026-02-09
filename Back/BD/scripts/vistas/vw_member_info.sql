-- =====================================================
-- Vista: vw_member_info
-- Descripción: Combina información de miembros del equipo y usuarios
-- =====================================================

CREATE OR REPLACE VIEW vw_member_info AS
SELECT 
    m.id_member,
    m.id_user,
    u.first_name,
    u.last_name,
    u.email,
    m.commission,
    m.hire_date,
    u.role_id
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user;

-- Comentario
COMMENT ON VIEW vw_member_info IS 'Vista que combina información de miembros del equipo y usuarios';
