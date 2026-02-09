# Sistema de Gestión de Pedidos y Notificaciones

## ✅ Implementación Completada

### 📊 Base de Datos

#### Migraciones Creadas:
- ✅ `2026-02-07_fix_order_schema.sql` - Corrige schema de tbl_order
  - Agrega campos `delivery_date` y `notes`
  - Renombra `id_member` a `seller_id`
  - Agrega índices para performance

#### Schemas Nuevos:
- ✅ `04_notificaciones.sql` - Tabla de notificaciones
  - Soporta múltiples tipos de notificaciones
  - Sistema de lectura/no lectura
  - Índices optimizados

#### Funciones:
- ✅ `fn_notify_admin_new_order.sql` - Función trigger
  - Crea notificación automática para admin
  - Se ejecuta cuando vendedor crea pedido

#### Triggers:
- ✅ `trg_notify_new_order.sql` - Trigger automático
  - Se dispara AFTER INSERT en tbl_order
  - Solo cuando seller_id NO es NULL

---

### 🎨 Frontend

#### APIs Creadas:
- ✅ `Front/dashboard/notifications.php` - Obtener notificaciones
- ✅ `Front/dashboard/mark_notification_read.php` - Marcar como leída

#### Páginas Actualizadas:
- ✅ `Front/dashboard/pedidos/pedidos.php`
  - Query actualizada para usar `tbl_client` y `vw_order_totals`
  - Muestra nombre del vendedor en cada pedido
  - Nuevo filtro por vendedor
  - Columna "Vendedor" en tabla

---

## 🚀 Cómo Ejecutar la Migración

### Opción 1: Script PHP (Recomendado)
```bash
php run_order_notifications_migration.php
```

### Opción 2: Manual (psql)
```bash
# 1. Migración de tbl_order
psql -h 10.5.213.111 -U mdavid -d db_evolution -f Back/scripts/migraciones/2026-02-07_fix_order_schema.sql

# 2. Tabla de notificaciones
psql -h 10.5.213.111 -U mdavid -d db_evolution -f Back/scripts/schema/04_notificaciones.sql

# 3. Función
psql -h 10.5.213.111 -U mdavid -d db_evolution -f Back/scripts/funciones/fn_notify_admin_new_order.sql

# 4. Trigger
psql -h 10.5.213.111 -U mdavid -d db_evolution -f Back/scripts/triggers/trg_notify_new_order.sql
```

---

## 🧪 Cómo Probar

### 1. Ejecutar Migración
```bash
php run_order_notifications_migration.php
```

### 2. Crear Pedido como Vendedor
1. Ir a `http://localhost:3000/Front/login/login.php`
2. Login como vendedor (usuario@maishop.com / User@2026!)
3. Ir a "Nuevo Pedido"
4. Llenar formulario y crear pedido

### 3. Verificar en Dashboard de Admin
1. Login como admin (admin@maishop.com / Admin@2026!)
2. Ir a "Pedidos"
3. Verificar que aparece el pedido con nombre del vendedor
4. (Próximo paso: Ver notificación en campana)

---

## 📁 Archivos Creados

### Backend (Base de Datos):
```
Back/scripts/
├── migraciones/
│   └── 2026-02-07_fix_order_schema.sql
├── schema/
│   └── 04_notificaciones.sql
├── funciones/
│   └── fn_notify_admin_new_order.sql
└── triggers/
    └── trg_notify_new_order.sql
```

### Frontend (APIs):
```
Front/dashboard/
├── notifications.php
└── mark_notification_read.php
```

### Scripts de Migración:
```
run_order_notifications_migration.php
```

---

## 🔄 Próximos Pasos

### Pendientes:
- [ ] Agregar componente de notificaciones al dashboard de admin
- [ ] Implementar polling cada 30 segundos
- [ ] Agregar badge con contador de notificaciones
- [ ] Dropdown con lista de notificaciones
- [ ] Click en notificación → ir al pedido

---

## 📝 Notas Técnicas

### Campos Agregados a tbl_order:
- `delivery_date` (DATE) - Fecha de entrega
- `notes` (TEXT) - Notas adicionales
- `seller_id` (INTEGER) - ID del vendedor (renombrado de id_member)

### Tabla tbl_notification:
```sql
- id_notification (SERIAL PRIMARY KEY)
- user_id (INTEGER) - Usuario que recibe la notificación
- type (VARCHAR) - Tipo: 'new_order', 'order_update', etc.
- title (VARCHAR) - Título de la notificación
- message (TEXT) - Mensaje descriptivo
- related_id (INTEGER) - ID del pedido relacionado
- is_read (BOOLEAN) - Estado de lectura
- created_at (TIMESTAMP) - Fecha de creación
```

### Flujo de Notificación:
1. Vendedor crea pedido → INSERT en tbl_order
2. Trigger `trg_notify_new_order` se dispara
3. Función `notify_admin_new_order()` se ejecuta
4. Se crea registro en tbl_notification para admin
5. Admin ve notificación en dashboard (próximo paso)
