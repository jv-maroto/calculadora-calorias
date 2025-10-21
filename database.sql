-- Base de datos para Calculadora de Calorías
CREATE DATABASE IF NOT EXISTS calculadora_calorias;
USE calculadora_calorias;

-- Tabla para almacenar los planes personalizados
CREATE TABLE IF NOT EXISTS planes_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Identificación del usuario
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,

    -- Datos personales
    edad INT NOT NULL,
    sexo ENUM('hombre', 'mujer') NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    altura INT NOT NULL,

    -- Actividad física
    dias_entreno INT DEFAULT 0,
    horas_gym DECIMAL(4,2) DEFAULT 0,
    dias_cardio INT DEFAULT 0,
    horas_cardio DECIMAL(4,2) DEFAULT 0,

    -- Estilo de vida
    tipo_trabajo ENUM('sedentario', 'activo') NOT NULL,
    horas_trabajo DECIMAL(4,2) DEFAULT 0,
    horas_sueno DECIMAL(4,2) DEFAULT 0,

    -- Objetivo
    objetivo ENUM('deficit', 'volumen', 'mantenimiento') NOT NULL,
    kg_objetivo DECIMAL(5,2) DEFAULT 0,
    velocidad VARCHAR(20) DEFAULT NULL,
    nivel_gym ENUM('principiante', 'intermedio', 'avanzado') DEFAULT NULL,

    -- Resultados calculados
    tmb DECIMAL(7,2) NOT NULL,
    tdee DECIMAL(7,2) NOT NULL,
    calorias_plan DECIMAL(7,2) NOT NULL,
    duracion_semanas INT DEFAULT NULL,
    duracion_meses INT DEFAULT NULL,

    -- Macronutrientes
    proteina_gramos INT NOT NULL,
    grasa_gramos INT NOT NULL,
    carbohidratos_gramos INT NOT NULL,

    -- Plan completo en JSON
    plan_json TEXT,

    INDEX idx_fecha (fecha_calculo),
    INDEX idx_objetivo (objetivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de usuarios (para seguimiento por sesión)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de seguimiento de peso diario
CREATE TABLE IF NOT EXISTS peso_diario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    plan_id INT,
    peso DECIMAL(5,2) NOT NULL,
    fecha DATE NOT NULL,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES planes_nutricionales(id) ON DELETE SET NULL,
    UNIQUE KEY unique_usuario_fecha (nombre, apellidos, fecha),
    INDEX idx_usuario_fecha (nombre, apellidos, fecha),
    INDEX idx_plan_fecha (plan_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
