# Guía Rápida: Resetear Base de Datos

## 🔄 Problema Actual

Intentaste ejecutar `MaiConnect.sql` pero falla porque hay tablas de productos con dependencias que bloquean el DROP CASCADE.

## ✅ Solución: 3 Pasos

### 1. Limpiar Base de Datos Completamente
```bash
php clean_database.php
```
Esto elimina TODAS las tablas, vistas y funciones.

### 2. Ejecutar MaiConnect.sql
```bash
# Opción A: Con psql (si lo tienes)
psql -h 10.5.213.111 -U mdavid -d db_evolution -f "Back/BD/MaiConnect.sql"

# Opción B: Desde pgAdmin
# - Abre pgAdmin
# - Conecta a db_evolution
# - Tools → Query Tool
# - File → Open → Selecciona MaiConnect.sql
# - Ejecuta (F5)
```

### 3. Crear Productos
```bash
php setup_products.php
```

## 📝 Orden Correcto SIEMPRE

```
1. clean_database.php    ← Limpia todo
2. MaiConnect.sql        ← Base principal
3. setup_products.php    ← Productos
```

## ⚠️ Importante

- `clean_database.php` **ELIMINA TODO**
- Solo úsalo cuando quieras empezar desde cero
- Después de ejecutarlo, DEBES correr MaiConnect.sql

## 🐛 Si algo falla

### Error: "no se puede eliminar tabla X"
- Ejecuta `clean_database.php` de nuevo
- Asegúrate de no tener conexiones abiertas

### Error: "tabla ya existe" al ejecutar MaiConnect.sql
- Ejecuta `clean_database.php` primero

### Error: "vista vw_order_totals no existe"
- Ejecuta `fix_db_simple.php`
