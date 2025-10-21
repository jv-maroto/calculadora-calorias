<?php
// Script para agregar campos avanzados a la base de datos
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

echo "<h2>Agregando campos avanzados...</h2>";

// Agregar años entrenando
$sql = "ALTER TABLE planes_nutricionales ADD COLUMN anos_entrenando VARCHAR(20) DEFAULT NULL AFTER altura";
if ($conn->query($sql) === TRUE) {
    echo "✅ Campo anos_entrenando agregado<br>";
} else {
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "⚠️ El campo anos_entrenando ya existe<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

// Agregar historial de dietas
$sql = "ALTER TABLE planes_nutricionales ADD COLUMN historial_dietas VARCHAR(20) DEFAULT NULL AFTER anos_entrenando";
if ($conn->query($sql) === TRUE) {
    echo "✅ Campo historial_dietas agregado<br>";
} else {
    if (strpos($conn->error, "Duplicate column name") !== false) {
        echo "⚠️ El campo historial_dietas ya existe<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

echo "<br><h2>✅ Campos agregados correctamente</h2>";
echo "<p><a href='index.php'>← Volver a la calculadora</a></p>";

$conn->close();
?>
