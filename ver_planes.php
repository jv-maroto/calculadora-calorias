<?php
require 'connection.php';

$result = $conn->query('SELECT id, nombre, apellidos, objetivo, fecha_calculo FROM planes_nutricionales ORDER BY id DESC LIMIT 10');

echo "<h3>Últimos planes guardados:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Usuario</th><th>Objetivo</th><th>Fecha</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) . "</td>";
    echo "<td><strong>" . $row['objetivo'] . "</strong></td>";
    echo "<td>" . $row['fecha_calculo'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
