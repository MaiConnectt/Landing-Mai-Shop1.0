# Scripts de Base de Datos - Mai Shop

Esta carpeta contiene todos los scripts SQL organizados por tipo y propósito.

## 📁 Estructura de Carpetas

```
scripts/
├── schema/          # Definiciones de tablas y estructura de BD
├── vistas/          # Vistas (views) de base de datos
├── funciones/       # Funciones almacenadas
├── triggers/        # Triggers de base de datos
├── inserts/         # Scripts de inserción de datos
└── migraciones/     # Scripts de migración y actualizaciones
```

---

## 📋 Descripción de Carpetas

### `schema/`
Contiene los scripts de creación de tablas y estructura de la base de datos.

**Archivos:**
- Esquemas de tablas principales
- Definiciones de índices
- Constraints y relaciones

**Orden de ejecución:**
1. Tablas de referencia (roles, estados, etc.)
2. Tablas principales (usuarios, productos, etc.)
3. Tablas de relación (pedidos, detalles, etc.)

---

### `vistas/`
Scripts de creación de vistas (views) para consultas complejas.

**Ejemplos:**
- `vw_seller_commissions` - Cálculo de comisiones de vendedores
- `vw_order_totals` - Totales de pedidos
- `vw_client_info` - Información completa de clientes

---

### `funciones/`
Funciones almacenadas en PostgreSQL.

**Uso:**
- Cálculos complejos
- Lógica de negocio reutilizable
- Validaciones

---

### `triggers/`
Triggers para automatización de procesos.

**Ejemplos:**
- Actualización automática de timestamps
- Validaciones antes de insertar/actualizar
- Auditoría de cambios

---

### `inserts/`
Scripts de inserción de datos iniciales y de prueba.

**Tipos:**
- **Datos de referencia**: Roles, estados, métodos de pago
- **Datos de prueba**: Productos, usuarios, pedidos de ejemplo
- **Datos de producción**: Configuraciones iniciales

---

### `migraciones/`
Scripts de migración para actualizar la base de datos existente.

**Nomenclatura:**
```
YYYY-MM-DD_descripcion.sql
```

**Ejemplo:**
```
2026-02-06_add_seller_commissions.sql
```

**Contenido:**
- Cambios en estructura (ALTER TABLE)
- Nuevas columnas o tablas
- Actualizaciones de datos existentes

---

## 🚀 Orden de Ejecución Recomendado

### Instalación Inicial (Base de datos nueva)

1. **Schema** - Crear estructura
   ```bash
   psql -U usuario -d database -f schema/01_tablas_referencia.sql
   psql -U usuario -d database -f schema/02_tablas_principales.sql
   ```

2. **Vistas** - Crear vistas
   ```bash
   psql -U usuario -d database -f vistas/vw_seller_commissions.sql
   ```

3. **Funciones** - Crear funciones
   ```bash
   psql -U usuario -d database -f funciones/*.sql
   ```

4. **Triggers** - Crear triggers
   ```bash
   psql -U usuario -d database -f triggers/*.sql
   ```

5. **Inserts** - Insertar datos iniciales
   ```bash
   psql -U usuario -d database -f inserts/datos_referencia.sql
   psql -U usuario -d database -f inserts/datos_prueba.sql
   ```

### Actualización (Base de datos existente)

```bash
psql -U usuario -d database -f migraciones/2026-02-06_add_seller_commissions.sql
```

---

## 📝 Buenas Prácticas

### Al crear nuevos scripts:

1. **Usar transacciones**
   ```sql
   BEGIN;
   -- Tus cambios aquí
   COMMIT;
   ```

2. **Incluir rollback**
   ```sql
   -- Rollback (comentado)
   -- DROP TABLE IF EXISTS nueva_tabla;
   ```

3. **Documentar cambios**
   ```sql
   -- =====================================================
   -- Descripción: Agregar campo de comisiones
   -- Fecha: 2026-02-06
   -- Autor: Tu nombre
   -- =====================================================
   ```

4. **Hacer scripts idempotentes**
   ```sql
   DROP TABLE IF EXISTS tabla;
   CREATE TABLE tabla (...);
   ```

---

## ⚠️ Importante

- **Siempre hacer backup** antes de ejecutar migraciones
- **Probar en ambiente de desarrollo** primero
- **Documentar todos los cambios** en el script
- **Usar nombres descriptivos** para archivos

---

## 🔗 Conexión a la Base de Datos

```bash
# Desarrollo
psql -h 10.5.213.111 -U mdavid -d db_evolution

# Producción
# (Agregar credenciales de producción aquí)
```

---

## 📞 Contacto

Para dudas sobre la estructura de la base de datos, contactar al equipo de desarrollo.
