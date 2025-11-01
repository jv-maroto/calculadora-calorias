-- Agregar columnas nombre y apellidos a medidas_corporales
ALTER TABLE medidas_corporales
ADD COLUMN IF NOT EXISTS nombre VARCHAR(100) AFTER id,
ADD COLUMN IF NOT EXISTS apellidos VARCHAR(100) AFTER nombre;

-- Hacer que usuario_id sea opcional (para compatibilidad)
ALTER TABLE medidas_corporales
MODIFY COLUMN usuario_id INT NULL;

-- Crear índice compuesto para nombre y apellidos
CREATE INDEX IF NOT EXISTS idx_nombre_apellidos_fecha ON medidas_corporales(nombre, apellidos, fecha DESC);

-- Actualizar la clave única para usar nombre y apellidos
ALTER TABLE medidas_corporales
DROP INDEX IF EXISTS unico_usuario_fecha;

ALTER TABLE medidas_corporales
ADD UNIQUE INDEX unico_nombre_apellidos_fecha (nombre, apellidos, fecha);
