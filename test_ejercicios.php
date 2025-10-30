<?php
include 'connection.php';

echo "<h2>Verificando tabla de ejercicios</h2>";

// Verificar si la tabla existe
$tableCheck = $conn->query("SHOW TABLES LIKE 'ejercicios'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color:red;'>ERROR: La tabla 'ejercicios' NO existe</p>";
    exit;
}

echo "<p style='color:green;'>✓ La tabla 'ejercicios' existe</p>";

// Contar ejercicios
$count = $conn->query("SELECT COUNT(*) as total FROM ejercicios")->fetch_assoc();
echo "<p>Total de ejercicios: <strong>{$count['total']}</strong></p>";

// Mostrar primeros 20 ejercicios
echo "<h3>Ejercicios en la base de datos:</h3>";
$result = $conn->query("SELECT nombre, grupo_muscular, tipo_ejercicio FROM ejercicios ORDER BY grupo_muscular LIMIT 20");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>Nombre</th><th>Grupo Muscular</th><th>Tipo</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td><strong style='color:blue;'>" . htmlspecialchars($row['grupo_muscular']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['tipo_ejercicio']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Mostrar grupos musculares únicos
    echo "<h3>Grupos musculares únicos en tu base de datos:</h3>";
    $grupos = $conn->query("SELECT DISTINCT grupo_muscular FROM ejercicios ORDER BY grupo_muscular");
    echo "<ul style='color:green; font-weight:bold;'>";
    while ($g = $grupos->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($g['grupo_muscular']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>Error al consultar ejercicios: " . ($conn->error ?: 'No hay datos') . "</p>";
}

// Probar la API
echo "<h3>Probando API:</h3>";
echo "<pre>";
$apiUrl = "http://localhost/Calculator/api_ejercicios.php?action=obtener_todos";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Respuesta API:\n";
echo htmlspecialchars($response);
echo "</pre>";

$conn->close();
?>
