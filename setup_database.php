<?php
// Script para crear automáticamente la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calculadora_calorias";

// Paso 1: Conectar sin seleccionar base de datos
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "✅ Conexión a MySQL exitosa<br>";

// Paso 2: Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Base de datos '$dbname' verificada/creada<br>";
} else {
    die("❌ Error al crear base de datos: " . $conn->error);
}

// Paso 3: Seleccionar la base de datos
$conn->select_db($dbname);

// Paso 4: Crear tabla planes_nutricionales
$sql = "CREATE TABLE IF NOT EXISTS planes_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'planes_nutricionales' verificada/creada<br>";
} else {
    die("❌ Error al crear tabla: " . $conn->error);
}

// Paso 5: Crear tabla usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'usuarios' verificada/creada<br>";
} else {
    die("❌ Error al crear tabla: " . $conn->error);
}

// Paso 6: Crear tabla peso_diario
$sql = "CREATE TABLE IF NOT EXISTS peso_diario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_session VARCHAR(255) NOT NULL,
    plan_id INT,
    peso DECIMAL(5,2) NOT NULL,
    fecha DATE NOT NULL,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES planes_nutricionales(id) ON DELETE SET NULL,
    UNIQUE KEY unique_session_fecha (usuario_session, fecha),
    INDEX idx_session_fecha (usuario_session, fecha),
    INDEX idx_plan_fecha (plan_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'peso_diario' verificada/creada<br>";
} else {
    die("❌ Error al crear tabla: " . $conn->error);
}

echo "<br><h2>✅ Base de datos configurada correctamente</h2>";
echo "<p>Ahora puedes usar la aplicación normalmente.</p>";
echo "<p><a href='index.php'>← Volver a la calculadora</a></p>";

$conn->close();
?>
