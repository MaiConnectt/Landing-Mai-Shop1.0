# Análisis de MaiConnect.sql - Organización Sugerida

## 📊 Resumen del Archivo Actual

El archivo `MaiConnect.sql` contiene **TODO** en un solo archivo:
- Tablas de referencia
- Tablas principales
- Vistas (4 vistas)
- Funciones (1 función)
- Triggers (2 triggers)
- Índices
- Datos iniciales (seed data)

**Total: 395 líneas**

---

## ✂️ Qué Extraer a las Carpetas Organizadas

### 1. 📊 Vistas → `scripts/vistas/`

**Líneas 191-248** contienen 4 vistas que deberían extraerse:

#### vw_order_totals.sql (Líneas 191-201)
```sql
CREATE VIEW vw_order_totals AS
SELECT 
    o.id_order,
    o.id_client,
    o.id_member,
    o.created_at,
    o.status,
    COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
FROM tbl_order o
LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
GROUP BY o.id_order, o.id_client, o.id_member, o.created_at, o.status;
```

#### vw_client_info.sql (Líneas 203-214)
```sql
CREATE VIEW vw_client_info AS
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
```

#### vw_member_info.sql (Líneas 216-227)
```sql
CREATE VIEW vw_member_info AS
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
```

#### vw_payment_proof_details.sql (Líneas 229-248)
```sql
CREATE VIEW vw_payment_proof_details AS
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
```

---

### 2. 🔧 Funciones → `scripts/funciones/`

**Líneas 273-279** contienen 1 función:

#### fn_update_timestamp.sql
```sql
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

---

### 3. ⚡ Triggers → `scripts/triggers/`

**Líneas 281-287** contienen 2 triggers:

#### trg_product_updated_at.sql
```sql
CREATE TRIGGER trg_product_updated_at
BEFORE UPDATE ON tbl_product
FOR EACH ROW EXECUTE FUNCTION update_timestamp();
```

#### trg_user_updated_at.sql
```sql
CREATE TRIGGER trg_user_updated_at
BEFORE UPDATE ON tbl_user
FOR EACH ROW EXECUTE FUNCTION update_timestamp();
```

---

### 4. 📝 Inserts → `scripts/inserts/`

**Líneas 312-375** contienen datos iniciales que deberían separarse:

#### datos_referencia.sql (Líneas 312-355)
- Roles (3 roles)
- Estados (14 estados para diferentes entidades)
- Métodos de pago (5 métodos)
- Tipos de catálogo (5 tipos)

#### datos_usuarios.sql (Líneas 361-375)
- Usuario administrador
- Usuario demo (miembro)
- Registro de miembro

---

## ⚠️ IMPORTANTE: NO Modificar

**El archivo `MaiConnect.sql` debe mantenerse intacto** porque:
1. Ya está funcionando en producción
2. Es el schema completo y funcional
3. Sirve como backup y referencia

---

## ✅ Acción Recomendada

**EXTRAER (copiar) las secciones a archivos individuales** en las carpetas organizadas:

```
scripts/
├── vistas/
│   ├── vw_order_totals.sql
│   ├── vw_client_info.sql
│   ├── vw_member_info.sql
│   └── vw_payment_proof_details.sql
│
├── funciones/
│   └── fn_update_timestamp.sql
│
├── triggers/
│   ├── trg_product_updated_at.sql
│   └── trg_user_updated_at.sql
│
└── inserts/
    ├── datos_referencia.sql
    └── datos_usuarios.sql
```

---

## 🎯 Beneficios de Extraer

1. **Modularidad**: Cada componente en su propio archivo
2. **Reutilización**: Fácil aplicar solo las vistas o solo los triggers
3. **Mantenimiento**: Más fácil actualizar una vista específica
4. **Documentación**: Cada archivo puede tener su propia documentación
5. **Versionamiento**: Cambios más claros en Git

---

## 📋 Orden de Ejecución

Si se usan los archivos extraídos (en lugar del MaiConnect.sql completo):

```bash
# 1. Schema (tablas)
psql -f schema/01_schema_principal.sql

# 2. Funciones (antes de triggers)
psql -f funciones/fn_update_timestamp.sql

# 3. Triggers (después de funciones y tablas)
psql -f triggers/trg_product_updated_at.sql
psql -f triggers/trg_user_updated_at.sql

# 4. Vistas (después de tablas)
psql -f vistas/vw_order_totals.sql
psql -f vistas/vw_client_info.sql
psql -f vistas/vw_member_info.sql
psql -f vistas/vw_payment_proof_details.sql

# 5. Datos iniciales
psql -f inserts/datos_referencia.sql
psql -f inserts/datos_usuarios.sql
```

---

## 📝 Notas

- **MaiConnect.sql original**: Mantener como archivo monolítico funcional
- **Archivos extraídos**: Para desarrollo modular y mantenimiento
- **Ambos enfoques son válidos**: Usar el que mejor se adapte a cada situación
