# Índice de Scripts SQL - Mai Shop

## 📂 Schema (Estructura de Base de Datos)

### 01_schema_principal.sql
- **Descripción**: Schema principal de la base de datos
- **Contiene**:
  - Tablas de referencia (roles, estados, métodos de pago)
  - Tablas principales (usuarios, clientes, miembros)
  - Tablas de relación (pedidos, detalles)
  - Vistas principales
  - Datos iniciales de referencia

### 02_productos.sql
- **Descripción**: Módulo de productos
- **Contiene**:
  - `tbl_category` - Categorías de productos
  - `tbl_product` - Productos principales
  - `tbl_product_image` - Galería de imágenes
  - `tbl_product_variant` - Variantes (tamaños, sabores)
  - Datos de ejemplo de productos

### 03_pedidos.sql
- **Descripción**: Sistema de pedidos
- **Contiene**:
  - `tbl_order` - Pedidos principales
  - `tbl_order_detail` - Detalles de pedidos
  - Vistas relacionadas con pedidos

---

## 🔄 Migraciones

### 2026-02-06_add_seller_commissions.sql
- **Descripción**: Sistema de comisiones para vendedores
- **Cambios**:
  - Agrega campos a `tbl_member`: `commission_percentage`, `university`, `status`, `phone`
  - Agrega `seller_id` a `tbl_order`
  - Agrega `team_member_id` a `tbl_payment_proof`
  - Crea vista `vw_seller_commissions`
- **Fecha**: 2026-02-06

---

## 📊 Vistas

### vw_order_totals.sql
- **Descripción**: Cálculo automático de totales de pedidos
- **Uso**: Obtener el total de cada pedido sumando los detalles
- **Campos**: id_order, id_client, id_member, created_at, status, total

### vw_client_info.sql
- **Descripción**: Información completa de clientes
- **Uso**: Vista consolidada de datos de clientes con información de usuario
- **Campos**: id_client, id_user, first_name, last_name, email, phone, address, role_id

### vw_member_info.sql
- **Descripción**: Información completa de miembros del equipo
- **Uso**: Vista consolidada de datos de miembros con información de usuario
- **Campos**: id_member, id_user, first_name, last_name, email, commission, hire_date, role_id

### vw_payment_proof_details.sql
- **Descripción**: Detalles de comprobantes de pago
- **Uso**: Vista completa de comprobantes con información relacionada
- **Campos**: id_payment_proof, id_order, payment_method, method_name, amount, status, reviewer_name, etc.

### vw_seller_commissions.sql
- **Descripción**: Cálculo automático de comisiones de vendedores
- **Ubicación**: Creada en migración `2026-02-06_add_seller_commissions.sql`
- **Campos**:
  - Información del vendedor
  - Total de pedidos
  - Ventas totales
  - Comisiones ganadas
  - Total pagado
  - Balance pendiente

---

## 🔧 Funciones

### fn_update_timestamp.sql
- **Descripción**: Actualiza automáticamente el campo updated_at
- **Uso**: Utilizada en triggers para mantener timestamps actualizados
- **Retorna**: TRIGGER
- **Lenguaje**: plpgsql

---

## ⚡ Triggers

### trg_product_updated_at.sql
- **Descripción**: Actualiza updated_at en tbl_product
- **Tabla**: tbl_product
- **Evento**: BEFORE UPDATE
- **Función**: update_timestamp()

### trg_user_updated_at.sql
- **Descripción**: Actualiza updated_at en tbl_user
- **Tabla**: tbl_user
- **Evento**: BEFORE UPDATE
- **Función**: update_timestamp()

---

## 📝 Inserts

### datos_referencia.sql
- **Descripción**: Datos de referencia del sistema
- **Contiene**:
  - **Roles** (3): Administrador, Miembro, Cliente
  - **Estados** (14): Para pedidos, solicitudes, citas, comprobantes
  - **Métodos de pago** (5): Efectivo, Transferencia, Tarjeta, Nequi, Daviplata
  - **Tipos de catálogo** (5): Tortas, Galletas, Postres, Panes, Especiales
- **Nota**: Usa `ON CONFLICT DO NOTHING` para ser idempotente

### datos_usuarios.sql
- **Descripción**: Usuarios iniciales del sistema
- **Contiene**:
  - **Administrador**: admin@maishop.com / Admin@2026!
  - **Usuario Demo**: usuario@maishop.com / User@2026!
  - Registro de miembro para usuario demo
- **Nota**: Usa `ON CONFLICT DO NOTHING` para ser idempotente

---

## 📋 Orden de Ejecución Recomendado

### Para una instalación limpia:

```bash
# 1. Schema principal
psql -h 10.5.213.111 -U mdavid -d db_evolution -f scripts/schema/01_schema_principal.sql

# 2. Módulo de productos
psql -h 10.5.213.111 -U mdavid -d db_evolution -f scripts/schema/02_productos.sql

# 3. Módulo de pedidos
psql -h 10.5.213.111 -U mdavid -d db_evolution -f scripts/schema/03_pedidos.sql

# 4. Migraciones (si aplica)
psql -h 10.5.213.111 -U mdavid -d db_evolution -f scripts/migraciones/2026-02-06_add_seller_commissions.sql
```

---

## ⚠️ Notas Importantes

- Los archivos en `schema/` están numerados para indicar el orden de ejecución
- Las migraciones usan formato de fecha `YYYY-MM-DD_descripcion.sql`
- Siempre hacer backup antes de ejecutar migraciones
- Probar primero en ambiente de desarrollo

---

## 🔗 Conexión

```bash
psql -h 10.5.213.111 -U mdavid -d db_evolution
```

**Contraseña**: 3205560180m
