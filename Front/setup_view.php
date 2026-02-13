<?php
require_once 'conexion.php';

try {
    $pdo->exec("DROP VIEW IF EXISTS vw_comisiones_vendedor CASCADE;");
    $pdo->exec("DROP VIEW IF EXISTS vw_comisiones_pendientes_vendedor CASCADE;");

    $sql1 = "
    CREATE VIEW vw_comisiones_vendedor AS
    SELECT 
        m.id_miembro,
        u.nombre,
        u.apellido,
        u.email,
        m.universidad,
        m.telefono,
        m.porcentaje_comision,
        m.status,
        m.fecha_contratacion,
        COUNT(o.id_pedido) as total_pedidos,
        COALESCE(SUM(ot.total), 0) as total_ventas,
        COALESCE(SUM(CASE WHEN o.estado = 2 THEN o.monto_comision ELSE 0 END), 0) as total_comisiones_ganadas,
        COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NOT NULL THEN o.monto_comision ELSE 0 END), 0) as total_pagado,
        COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NULL THEN o.monto_comision ELSE 0 END), 0) as saldo_pendiente
    FROM tbl_miembro m
    JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
    LEFT JOIN tbl_pedido o ON m.id_miembro = o.id_member
    LEFT JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
    GROUP BY 
        m.id_miembro, 
        u.nombre, 
        u.apellido, 
        u.email, 
        m.universidad, 
        m.telefono, 
        m.porcentaje_comision, 
        m.status, 
        m.fecha_contratacion;
    ";
    $pdo->exec($sql1);
    echo "Vista vw_comisiones_vendedor creada.\n";

    $sql2 = "
    CREATE OR REPLACE VIEW vw_comisiones_pendientes_vendedor AS
    SELECT 
        m.id_miembro,
        u.nombre,
        u.apellido,
        m.porcentaje_comision,
        COUNT(o.id_pedido) as pending_order_count,
        COALESCE(SUM(o.monto_comision), 0) as pending_amount
    FROM tbl_miembro m
    JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
    JOIN tbl_pedido o ON m.id_miembro = o.id_member
    WHERE o.estado = 2 AND o.id_pago_comision IS NULL
    GROUP BY m.id_miembro, u.nombre, u.apellido, m.porcentaje_comision;
    ";
    $pdo->exec($sql2);
    echo "Vista vw_comisiones_pendientes_vendedor creada.\n";

    echo "Todas las vistas creadas exitosamente.";
} catch (PDOException $e) {
    echo "Error al crear las vistas: " . $e->getMessage();
}
?>