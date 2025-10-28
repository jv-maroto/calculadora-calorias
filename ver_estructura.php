<?php
require_once 'connection.php';

echo "<h2>Estructura de dias_entrenamiento:</h2>";
$result = $conn->query("DESCRIBE dias_entrenamiento");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

echo "<h2>Datos en dias_entrenamiento:</h2>";
$result = $conn->query("SELECT * FROM dias_entrenamiento");
echo "<table border='1'><tr>";
$first = true;
while ($row = $result->fetch_assoc()) {
    if ($first) {
        foreach (array_keys($row) as $col) {
            echo "<th>$col</th>";
        }
        echo "</tr>";
        $first = false;
    }
    echo "<tr>";
    foreach ($row as $val) {
        echo "<td>$val</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<h2>Estructura de registros_entrenamiento:</h2>";
$result = $conn->query("DESCRIBE registros_entrenamiento");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

$conn->close();
?>
