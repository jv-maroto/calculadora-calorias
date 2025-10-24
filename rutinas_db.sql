-- Tabla de rutinas (plantillas de entrenamiento)
CREATE TABLE IF NOT EXISTS rutinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de días de entrenamiento
CREATE TABLE IF NOT EXISTS dias_entrenamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rutina_id INT NOT NULL,
    nombre VARCHAR(50) NOT NULL, -- Lunes, Martes, etc.
    dia_semana INT NOT NULL, -- 1=Lunes, 2=Martes, ..., 7=Domingo
    tipo VARCHAR(50), -- PUSH, PULL, LEGS, TORSO, etc.
    es_descanso BOOLEAN DEFAULT FALSE,
    orden INT DEFAULT 0,
    FOREIGN KEY (rutina_id) REFERENCES rutinas(id) ON DELETE CASCADE,
    INDEX idx_rutina_dia (rutina_id, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de ejercicios (plantilla de ejercicios en cada día)
CREATE TABLE IF NOT EXISTS ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_id INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    tipo_equipo VARCHAR(50), -- Barbell, Dumbbell, Machine, Cable, etc.
    musculo_principal VARCHAR(100), -- pecho, espalda, hombros, etc.
    musculo_secundario VARCHAR(100),
    imagen_url VARCHAR(255),
    video_url VARCHAR(255),
    notas TEXT,
    orden INT DEFAULT 0,
    sets_objetivo INT DEFAULT 3,
    reps_objetivo VARCHAR(20) DEFAULT '8-12', -- puede ser "8-12" o "5" o "AMRAP"
    FOREIGN KEY (dia_id) REFERENCES dias_entrenamiento(id) ON DELETE CASCADE,
    INDEX idx_dia_orden (dia_id, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de registros de entrenamiento (lo que realmente hiciste)
CREATE TABLE IF NOT EXISTS registros_entrenamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    ejercicio_id INT NOT NULL,
    fecha DATE NOT NULL,
    set_numero INT NOT NULL, -- 1, 2, 3, etc.
    peso DECIMAL(6,2) NOT NULL, -- kg
    reps INT NOT NULL,
    rpe DECIMAL(3,1), -- Rate of Perceived Exertion (1-10)
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registro (nombre, apellidos, ejercicio_id, fecha, set_numero),
    INDEX idx_usuario_fecha (nombre, apellidos, fecha),
    INDEX idx_ejercicio_fecha (ejercicio_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar rutina PPL por defecto
INSERT INTO rutinas (nombre, descripcion, activa) VALUES
('PUSH PULL LEGS - 5 DÍAS', 'Rutina de 5 días con descanso Miércoles y Domingo', TRUE);

-- Obtener el ID de la rutina recién insertada
SET @rutina_id = LAST_INSERT_ID();

-- Insertar días de entrenamiento
INSERT INTO dias_entrenamiento (rutina_id, nombre, dia_semana, tipo, es_descanso, orden) VALUES
(@rutina_id, 'LUNES - PUSH', 1, 'PUSH', FALSE, 1),
(@rutina_id, 'MARTES - PULL', 2, 'PULL', FALSE, 2),
(@rutina_id, 'MIÉRCOLES', 3, 'DESCANSO', TRUE, 3),
(@rutina_id, 'JUEVES - LEGS', 4, 'LEGS', FALSE, 4),
(@rutina_id, 'VIERNES - TORSO', 5, 'TORSO', FALSE, 5),
(@rutina_id, 'SÁBADO - PIERNA B', 6, 'LEGS', FALSE, 6),
(@rutina_id, 'DOMINGO', 7, 'DESCANSO', TRUE, 7);

-- Obtener IDs de días
SET @lunes_id = (SELECT id FROM dias_entrenamiento WHERE rutina_id = @rutina_id AND dia_semana = 1);
SET @martes_id = (SELECT id FROM dias_entrenamiento WHERE rutina_id = @rutina_id AND dia_semana = 2);
SET @jueves_id = (SELECT id FROM dias_entrenamiento WHERE rutina_id = @rutina_id AND dia_semana = 4);
SET @viernes_id = (SELECT id FROM dias_entrenamiento WHERE rutina_id = @rutina_id AND dia_semana = 5);
SET @sabado_id = (SELECT id FROM dias_entrenamiento WHERE rutina_id = @rutina_id AND dia_semana = 6);

-- LUNES - PUSH
INSERT INTO ejercicios (dia_id, nombre, tipo_equipo, musculo_principal, orden, sets_objetivo, reps_objetivo) VALUES
(@lunes_id, 'Press de Banca', 'Barbell', 'Pecho', 1, 3, '8-12'),
(@lunes_id, 'Press Supino', 'Dumbbell', 'Pecho superior', 2, 3, '8-12'),
(@lunes_id, 'Fondos', 'Bodyweight', 'Pecho/Tríceps', 3, 3, '8-12'),
(@lunes_id, 'Arnold Press', 'Dumbbell', 'Hombros', 4, 3, '8-12'),
(@lunes_id, 'Lateral Raise', 'Machine', 'Deltoides lateral', 5, 3, '12-15'),
(@lunes_id, 'Tríceps Overhead Extension', 'Dumbbell', 'Tríceps', 6, 3, '10-12'),
(@lunes_id, 'Tricep Pushdown', 'Cable', 'Tríceps', 7, 3, '10-12');

-- MARTES - PULL
INSERT INTO ejercicios (dia_id, nombre, tipo_equipo, musculo_principal, orden, sets_objetivo, reps_objetivo) VALUES
(@martes_id, 'Chin Up/Dominadas', 'Assisted', 'Espalda/Bíceps', 1, 3, '8-12'),
(@martes_id, 'Lat Pulldown', 'Cable', 'Dorsal', 2, 3, '8-12'),
(@martes_id, 'Seated Row', 'Cable', 'Espalda media', 3, 3, '8-12'),
(@martes_id, 'T Bar Row', 'Barbell', 'Espalda grosor', 4, 3, '8-12'),
(@martes_id, 'Incline Curl', 'Dumbbell', 'Bíceps', 5, 3, '10-12'),
(@martes_id, 'Hammer Curl', 'Cable', 'Bíceps', 6, 3, '10-12'),
(@martes_id, 'Reverse Fly', 'Machine', 'Deltoides posterior', 7, 3, '12-15'),
(@martes_id, 'Knee Raise', 'Captain\'s Chair', 'Abdomen', 8, 3, '12-20');

-- JUEVES - LEGS
INSERT INTO ejercicios (dia_id, nombre, tipo_equipo, musculo_principal, orden, sets_objetivo, reps_objetivo) VALUES
(@jueves_id, 'Leg Press', 'Machine', 'Cuádriceps', 1, 3, '10-15'),
(@jueves_id, 'Hack Squat', 'Machine', 'Cuádriceps', 2, 3, '10-12'),
(@jueves_id, 'Leg Extension', 'Machine', 'Cuádriceps', 3, 3, '12-15'),
(@jueves_id, 'Hip Thrust', 'Barbell', 'Glúteos', 4, 3, '10-15'),
(@jueves_id, 'Seated Leg Curl', 'Machine', 'Femoral', 5, 3, '10-12'),
(@jueves_id, 'Seated Calf Raise', 'Machine', 'Gemelos', 6, 3, '12-20');

-- VIERNES - TORSO
INSERT INTO ejercicios (dia_id, nombre, tipo_equipo, musculo_principal, orden, sets_objetivo, reps_objetivo) VALUES
(@viernes_id, 'Deadlift', 'Barbell', 'Espalda completa', 1, 3, '5'),
(@viernes_id, 'Strict Military Press', 'Barbell', 'Hombros', 2, 3, '6-8'),
(@viernes_id, 'Bent Over Row', 'Barbell', 'Espalda media', 3, 3, '8-10'),
(@viernes_id, 'Lateral Raise', 'Machine', 'Deltoides lateral', 4, 3, '12-15'),
(@viernes_id, 'Bicep Curl', 'Dumbbell', 'Bíceps', 5, 3, '10-12'),
(@viernes_id, 'Preacher Curl', 'Machine', 'Bíceps', 6, 3, '10-12'),
(@viernes_id, 'Shrug', 'Dumbbell', 'Trapecio superior', 7, 3, '12-15');

-- SÁBADO - PIERNA B
INSERT INTO ejercicios (dia_id, nombre, tipo_equipo, musculo_principal, orden, sets_objetivo, reps_objetivo) VALUES
(@sabado_id, 'Romanian Deadlift', 'Dumbbell', 'Femoral', 1, 3, '4'),
(@sabado_id, 'Bulgarian Split Squat', 'Dumbbell', 'Cuádriceps', 2, 3, '8-10'),
(@sabado_id, 'Seated Leg Curl', 'Machine', 'Femoral', 3, 3, '10-12'),
(@sabado_id, 'Leg Extension', 'Machine', 'Cuádriceps', 4, 3, '12-15'),
(@sabado_id, 'Seated Calf Raise', 'Machine', 'Gemelos', 5, 3, '12-20'),
(@sabado_id, 'Knee Raise', 'Captain\'s Chair', 'Abdomen', 6, 3, '12-20');
