-- Script para agregar campos de contacto a la tabla reservas
-- Ejecuta esto en phpMyAdmin o usa agregar_campos_reserva.php

ALTER TABLE reservas 
ADD COLUMN nombre_contacto VARCHAR(150) NULL AFTER usuario_id,
ADD COLUMN telefono VARCHAR(20) NULL AFTER nombre_contacto,
ADD COLUMN numero_identificacion VARCHAR(50) NULL AFTER telefono;

-- Agregar índices para búsquedas
CREATE INDEX idx_nombre_contacto ON reservas(nombre_contacto);
CREATE INDEX idx_telefono ON reservas(telefono);
CREATE INDEX idx_numero_identificacion ON reservas(numero_identificacion);

