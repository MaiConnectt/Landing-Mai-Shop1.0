# Archivos corregidos para usar schema en español

## ✅ Cambios aplicados:

### seller/nuevo_pedido.php
- `id_member` → `id_vendedor` (columna en INSERT)
- `$_SESSION['seller_id']` → `$_SESSION['member_id']` (ID del vendedor)

## 📋 Archivos pendientes de actualizar:

Los siguientes archivos aún usan `id_member` y necesitan actualizarse:

### Alta prioridad (queries que fallarán):
- [ ] seller/mis_pedidos.php - WHERE o.id_member
- [ ] seller/seller_dash.php - Múltiples queries con id_member
- [ ] dashboard/pedidos/pedidos.php - Joins y filtros
- [ ] pedidos_acciones.php - SELECT con id_member

### Media prioridad (puede afectar funcionalidad):
- [ ] dashboard/comisiones/pagar.php - Gestión de pagos
- [ ] dashboard/dash.php - Visualización de pedidos
- [ ] setup_view.php - Creación de vistas

### Baja prioridad (funcionalidad legacy):
- [ ] dashboard/equipo/eliminar.php - Usa tbl_member (tabla vieja)
- [ ] seller/perfil.php - Usa tbl_member

## 🎯 Cambios sistemáticos necesarios:

1. **Columnas SQL:** `id_member` → `id_vendedor`
2. **Variables sesión:** `seller_id` → `member_id`
3. **Nombres tabla:** `tbl_member` → `tbl_miembro` (si aplica)
