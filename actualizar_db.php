<?php
// Script para actualizar la base de datos con los nuevos campos
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calculadora_calorias";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "<h2>Actualizando base de datos...</h2>";

// Agregar campos nombre y apellidos a planes_nutricionales
$sql = "ALTER TABLE planes_nutricionales
        ADD COLUMN nombre VARCHAR(100) NOT NULL DEFAULT 'Usuario' AFTER fecha_calculo,
        ADD COLUMN apellidos VARCHAR(100) NOT NULL DEFAULT 'Anónimo' AFTER nombre";

if ($conn->query($sql) === TRUE) {
    echo "✅ Campos nombre y apellidos agregados a planes_nutricionales<br>";
} else {
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "⚠️ Los campos ya existen en planes_nutricionales<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

// Actualizar tabla peso_diario
echo "<br><h3>Actualizando tabla peso_diario...</h3>";

// Eliminar la clave única vieja si existe
$sql = "ALTER TABLE peso_diario DROP INDEX unique_session_fecha";
if ($conn->query($sql) === TRUE) {
    echo "✅ Índice antiguo eliminado<br>";
} else {
    echo "⚠️ " . $conn->error . "<br>";
}

// Agregar campos nombre y apellidos
$sql = "ALTER TABLE peso_diario
        ADD COLUMN nombre VARCHAR(100) NOT NULL DEFAULT 'Usuario' AFTER id,
        ADD COLUMN apellidos VARCHAR(100) NOT NULL DEFAULT 'Anónimo' AFTER nombre";

if ($conn->query($sql) === TRUE) {
    echo "✅ Campos agregados a peso_diario<br>";
} else {
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "⚠️ Los campos ya existen en peso_diario<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

// Crear nuevo índice único
$sql = "ALTER TABLE peso_diario
        ADD UNIQUE KEY unique_usuario_fecha (nombre, apellidos, fecha)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Nuevo índice único creado<br>";
} else {
    if (strpos($conn->error, "Duplicate key name") !== false) {
        echo "⚠️ El índice ya existe<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

echo "<br><h2>✅ Base de datos actualizada correctamente</h2>";
echo "<p><a href='index.php'>← Volver a la calculadora</a></p>";

$conn->close();
?>
