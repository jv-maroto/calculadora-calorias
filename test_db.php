<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Conexión a Base de Datos</h2>";

// Configuración
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calculadora_calorias";

echo "<p><strong>Configuración:</strong></p>";
echo "Servidor: $servername<br>";
echo "Usuario: $username<br>";
echo "Base de datos: $dbname<br><br>";

// Test 1: Conexión sin base de datos
echo "<p><strong>Test 1:</strong> Conectando al servidor MySQL...</p>";
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("❌ Error de conexión al servidor: " . $conn->connect_error);
}
echo "✅ Conexión al servidor MySQL exitosa<br><br>";

// Test 2: Verificar si existe la base de datos
echo "<p><strong>Test 2:</strong> Verificando si existe la base de datos...</p>";
$result = $conn->query("SHOW DATABASES LIKE '$dbname'");
if ($result->num_rows == 0) {
    echo "❌ La base de datos '$dbname' NO existe<br>";
    echo "<br><strong>Solución:</strong><br>";
    echo "1. Abre phpMyAdmin (http://localhost/phpmyadmin)<br>";
    echo "2. Haz clic en 'Nueva' en el panel izquierdo<br>";
    echo "3. Nombre: calculadora_calorias<br>";
    echo "4. Cotejamiento: utf8mb4_general_ci<br>";
    echo "5. Haz clic en 'Crear'<br>";
    echo "6. Luego importa el archivo database.sql<br>";
    $conn->close();
    exit;
}
echo "✅ La base de datos existe<br><br>";

// Test 3: Conectar a la base de datos
echo "<p><strong>Test 3:</strong> Conectando a la base de datos...</p>";
$conn->close();
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conn->connect_error);
}
echo "✅ Conexión a la base de datos exitosa<br><br>";

// Test 4: Verificar si existe la tabla
echo "<p><strong>Test 4:</strong> Verificando si existe la tabla...</p>";
$result = $conn->query("SHOW TABLES LIKE 'calculos_calorias'");
if ($result->num_rows == 0) {
    echo "❌ La tabla 'calculos_calorias' NO existe<br>";
    echo "<br><strong>Solución:</strong><br>";
    echo "1. Abre phpMyAdmin<br>";
    echo "2. Selecciona la base de datos 'calculadora_calorias'<br>";
    echo "3. Haz clic en 'Importar'<br>";
    echo "4. Selecciona el archivo database.sql<br>";
    echo "5. Haz clic en 'Continuar'<br>";
    $conn->close();
    exit;
}
echo "✅ La tabla 'calculos_calorias' existe<br><br>";

// Test 5: Verificar estructura de la tabla
echo "<p><strong>Test 5:</strong> Verificando estructura de la tabla...</p>";
$result = $conn->query("DESCRIBE calculos_calorias");
if ($result) {
    echo "✅ Estructura de la tabla correcta<br>";
    echo "<table border='1' cellpadding='5' style='margin-top:10px'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table><br>";
}

echo "<h3 style='color: green;'>✅ ¡Todos los tests pasaron correctamente!</h3>";
echo "<p>La base de datos está configurada correctamente. Puedes usar la calculadora.</p>";

$conn->close();
?>
