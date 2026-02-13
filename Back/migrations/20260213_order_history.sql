-- Create Order History Table
CREATE TABLE IF NOT EXISTS tbl_historial_pedido (
    id_historial SERIAL PRIMARY KEY,
    id_pedido INTEGER NOT NULL,
    id_usuario INTEGER NOT NULL,
    accion VARCHAR(50) NOT NULL,
    estado_anterior SMALLINT,
    estado_nuevo SMALLINT,
    pago_anterior SMALLINT,
    pago_nuevo SMALLINT,
    notas TEXT,
    fecha TIMESTAMP DEFAULT NOW(),
    CONSTRAINT fk_historial_pedido FOREIGN KEY (id_pedido) REFERENCES tbl_pedido(id_pedido),
    CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) REFERENCES tbl_usuario(id_usuario)
);

-- Ensure payment_status is correctly named in tbl_pedido (it was estado_pago in my inspection)
-- The user mentioned "payment_status" in the request, but my inspection showed "estado_pago".
-- I will stick to "estado_pago" to avoid massive renaming, or rename it if requested.
-- Looking at the user request: "Agregar campo en tbl_order ... Agregar: payment_status INT NOT NULL DEFAULT 0"
-- But my inspection showed: "--- tbl_pedido --- ... estado_pago (smallint)"
-- I'll keep estado_pago but I'll add an alias if needed. Actually, I'll just use the existing estado_pago.

COMMENT ON TABLE tbl_historial_pedido IS 'Registro de cambios de estado y acciones sobre los pedidos';
