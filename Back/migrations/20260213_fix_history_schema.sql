-- Fix History Table Schema
DO $$ 
BEGIN 
    -- Rename columns if they match the user's preference
    -- (The inspection already showed these names, but just in case)
    
    -- Add missing columns for the new flow
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='tbl_historial_pedido' AND column_name='accion') THEN
        ALTER TABLE tbl_historial_pedido ADD COLUMN accion VARCHAR(50);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='tbl_historial_pedido' AND column_name='pago_anterior') THEN
        ALTER TABLE tbl_historial_pedido ADD COLUMN pago_anterior SMALLINT;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='tbl_historial_pedido' AND column_name='pago_nuevo') THEN
        ALTER TABLE tbl_historial_pedido ADD COLUMN pago_nuevo SMALLINT;
    END IF;

    -- If the inspection showed 'motivo' instead of 'notas', we use 'motivo' in the code.
    -- But we can add 'notas' as an alias or just stick to one.
    -- The user suggested 'cambiado_por' or 'changed_by'.
END $$;
