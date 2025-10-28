<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Test de Conexión y Guardado</h3>";

// Test 1: Conexión
try {
    require_once 'connection.php';
    echo "✅ Conexión a base de datos OK<br>";
} catch (Exception $e) {
    echo "❌ Error conexión: " . $e->getMessage() . "<br>";
    die();
}

// Test 2: Verificar tabla existe
$result = $conn->query("SHOW TABLES LIKE 'planes_nutricionales'");
if ($result->num_rows > 0) {
    echo "✅ Tabla 'planes_nutricionales' existe<br>";
} else {
    echo "❌ Tabla 'planes_nutricionales' NO existe<br>";
    echo "Ejecuta database.sql primero<br>";
    die();
}

// Test 3: Ver estructura de tabla
echo "<h4>Estructura de la tabla:</h4>";
$result = $conn->query("DESCRIBE planes_nutricionales");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 4: Intentar inserción de prueba simple
echo "<h4>Test de inserción:</h4>";
try {
    $stmt = $conn->prepare("INSERT INTO planes_nutricionales
        (edad, sexo, peso, altura, tipo_trabajo, objetivo, proteina_gramos, grasa_gramos, carbohidratos_gramos, tmb, tdee, calorias_plan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $edad = 25;
    $sexo = 'hombre';
    $peso = 75.5;
    $altura = 175;
    $tipo_trabajo = 'sedentario';
    $objetivo = 'mantenimiento';
    $proteina = 150;
    $grasa = 70;
    $carbos = 200;
    $tmb = 1800.0;
    $tdee = 2200.0;
    $calorias_plan = 2200.0;

    $stmt->bind_param("isdissiiidd",
        $edad, $sexo, $peso, $altura, $tipo_trabajo, $objetivo,
        $proteina, $grasa, $carbos, $tmb, $tdee, $calorias_plan
    );

    if ($stmt->execute()) {
        echo "✅ Inserción de prueba OK (ID: " . $conn->insert_id . ")<br>";
        // Eliminar registro de prueba
        $conn->query("DELETE FROM planes_nutricionales WHERE id = " . $conn->insert_id);
        echo "✅ Test completado y limpiado<br>";
    } else {
        echo "❌ Error en inserción: " . $stmt->error . "<br>";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h4>Conclusión:</h4>";
echo "Si ves todos los ✅, el problema está en el JavaScript o en el formato de datos enviados.<br>";
echo "Revisa la consola del navegador (F12) para ver errores de JavaScript.";

$conn->close();
?>
