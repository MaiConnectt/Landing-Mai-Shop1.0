# Inserts - Mai Shop

Esta carpeta contiene los scripts SQL con datos iniciales (seed data) para la base de datos.

## 📁 Archivos

### `datos_referencia.sql`
Datos de referencia del sistema (roles, estados, métodos de pago, etc.)

**Contenido:**
- Roles (Admin, Miembro, Cliente)
- Estados de pedidos, citas, pagos
- Métodos de pago
- Tipos de catálogo

**Ejecutar:** Después de crear las tablas principales

### `datos_usuarios.sql`
Usuarios de ejemplo para pruebas

**Contenido:**
- Usuario administrador
- Usuario vendedor
- Clientes de ejemplo

**Ejecutar:** Después de `datos_referencia.sql`

### `datos_productos.sql` ⭐ **NUEVO**
Productos del catálogo

**Contenido:**
- 10 productos de ejemplo
- Tortas, cupcakes, cheesecakes, brownies, galletas, etc.
- Con precios y stock

**Ejecutar:** Después de crear `tbl_product`

## 🚀 Orden de Ejecución

```bash
# 1. Ejecutar schema principal
MaiConnect.sql

# 2. Ejecutar inserts (en orden)
psql ... -f "Back/BD/scripts/inserts/datos_referencia.sql"
psql ... -f "Back/BD/scripts/inserts/datos_usuarios.sql"
psql ... -f "Back/BD/scripts/inserts/datos_productos.sql"
```

## 💡 Uso con PHP

También puedes ejecutar los inserts usando PHP:

```bash
# Productos
php -r "require 'Front/conexion.php'; \$pdo->exec(file_get_contents('Back/BD/scripts/inserts/datos_productos.sql')); echo 'Productos insertados';"
```

## 📝 Notas

- Los archivos son **idempotentes** cuando sea posible
- Usa `INSERT ... ON CONFLICT DO NOTHING` cuando aplique
- Los IDs son explícitos para mantener consistencia
- Incluye comentarios descriptivos

## ✅ Verificación

Después de ejecutar los inserts, verifica:

```sql
-- Verificar productos
SELECT COUNT(*) FROM tbl_product;

-- Ver productos
SELECT id_product, product_name, price, stock FROM tbl_product;
```
