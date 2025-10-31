-- Tabla para registro de medidas corporales
CREATE TABLE IF NOT EXISTS medidas_corporales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    peso DECIMAL(5,2),

    -- Circunferencias (cm)
    cuello DECIMAL(5,2),
    hombros DECIMAL(5,2),
    pecho DECIMAL(5,2),
    brazo_derecho DECIMAL(5,2),
    brazo_izquierdo DECIMAL(5,2),
    antebrazo_derecho DECIMAL(5,2),
    antebrazo_izquierdo DECIMAL(5,2),
    cintura DECIMAL(5,2),
    cadera DECIMAL(5,2),
    muslo_derecho DECIMAL(5,2),
    muslo_izquierdo DECIMAL(5,2),
    pantorrilla_derecha DECIMAL(5,2),
    pantorrilla_izquierda DECIMAL(5,2),

    -- Pliegues cutáneos (mm)
    pliegue_triceps DECIMAL(5,2),
    pliegue_subescapular DECIMAL(5,2),
    pliegue_suprailiaco DECIMAL(5,2),
    pliegue_abdominal DECIMAL(5,2),
    pliegue_muslo DECIMAL(5,2),
    pliegue_pectoral DECIMAL(5,2),
    pliegue_axilar DECIMAL(5,2),

    -- Cálculos
    porcentaje_grasa DECIMAL(5,2),
    masa_muscular DECIMAL(5,2),
    masa_grasa DECIMAL(5,2),

    -- Notas
    notas TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unico_usuario_fecha (usuario_id, fecha),
    INDEX idx_usuario_fecha (usuario_id, fecha DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
