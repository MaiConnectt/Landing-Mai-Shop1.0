# Organización del Backend - Mai Shop

## ✅ Estructura Creada

Se organizó exitosamente la carpeta `Back/scripts/` con la siguiente estructura:

```
Back/
├── scripts/
│   ├── README.md                    # Documentación completa
│   ├── INDEX.md                     # Índice de archivos
│   │
│   ├── schema/                      # ✅ Estructura de BD
│   │   ├── 01_schema_principal.sql  # Schema principal
│   │   ├── 02_productos.sql         # Módulo de productos
│   │   └── 03_pedidos.sql           # Sistema de pedidos
│   │
│   ├── vistas/                      # 📊 Vistas (vacío - por organizar)
│   │
│   ├── funciones/                   # 🔧 Funciones (vacío - por crear)
│   │
│   ├── triggers/                    # ⚡ Triggers (vacío - por crear)
│   │
│   ├── inserts/                     # 📝 Datos iniciales (vacío - por organizar)
│   │
│   └── migraciones/                 # 🔄 Migraciones
│       └── 2026-02-06_add_seller_commissions.sql
│
└── BD/                              # 📁 Archivos originales (mantener como backup)
    ├── MaiConnect.sql
    └── DB.sql
```

---

## 📋 Archivos Organizados

### Schema (3 archivos)
1. **01_schema_principal.sql** (15.6 KB)
   - Tablas de referencia
   - Tablas principales
   - Vistas básicas
   - Datos iniciales

2. **02_productos.sql** (7.8 KB)
   - Categorías de productos
   - Productos y variantes
   - Imágenes de productos
   - Datos de ejemplo

3. **03_pedidos.sql** (5.2 KB)
   - Sistema de pedidos
   - Detalles de pedidos
   - Relaciones

### Migraciones (1 archivo)
1. **2026-02-06_add_seller_commissions.sql** (4.7 KB)
   - Sistema de comisiones
   - Campos de vendedor
   - Vista de comisiones

---

## 📚 Documentación

### README.md
- Descripción de cada carpeta
- Orden de ejecución recomendado
- Buenas prácticas
- Comandos de conexión

### INDEX.md
- Índice detallado de todos los archivos
- Descripción de contenido
- Orden de ejecución
- Notas importantes

---

## 🎯 Próximos Pasos Sugeridos

### 1. Organizar Vistas
Extraer las vistas del schema principal y crear archivos individuales en `vistas/`:
- `vw_seller_commissions.sql`
- `vw_order_totals.sql`
- `vw_client_info.sql`

### 2. Organizar Inserts
Separar los datos de inserción en archivos específicos en `inserts/`:
- `datos_referencia.sql` (roles, estados, métodos de pago)
- `datos_productos.sql` (productos de ejemplo)
- `datos_prueba.sql` (usuarios y pedidos de prueba)

### 3. Crear Funciones
Documentar y crear funciones útiles en `funciones/`:
- Cálculos de comisiones
- Validaciones de negocio
- Utilidades

### 4. Crear Triggers
Implementar triggers en `triggers/`:
- Actualización automática de timestamps
- Validaciones
- Auditoría

---

## ✅ Beneficios de esta Organización

1. **Claridad**: Fácil encontrar scripts específicos
2. **Mantenibilidad**: Cambios organizados por tipo
3. **Versionamiento**: Migraciones con fechas
4. **Documentación**: README e INDEX claros
5. **Escalabilidad**: Estructura preparada para crecer

---

## 🔗 Comandos Útiles

### Ejecutar todo el schema desde cero
```bash
cd "Back/scripts"
psql -h 10.5.213.111 -U mdavid -d db_evolution -f schema/01_schema_principal.sql
psql -h 10.5.213.111 -U mdavid -d db_evolution -f schema/02_productos.sql
psql -h 10.5.213.111 -U mdavid -d db_evolution -f schema/03_pedidos.sql
```

### Ejecutar migración específica
```bash
psql -h 10.5.213.111 -U mdavid -d db_evolution -f migraciones/2026-02-06_add_seller_commissions.sql
```

---

## 📝 Notas

- Los archivos originales en `Back/BD/` se mantienen como backup
- Los archivos en `Back/` raíz (create_*.sql, migration_*.sql) pueden eliminarse si se desea
- La numeración en `schema/` indica el orden de ejecución
- Las migraciones usan formato de fecha para versionamiento
