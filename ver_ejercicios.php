<?php
require_once 'config.php';

$result = $conn->query('SELECT id, nombre, grupo_muscular, sets_recomendados, reps_recomendadas FROM ejercicios ORDER BY id');

echo "Lista de ejercicios:\n\n";
while($row = $result->fetch_assoc()) {
    echo $row['id'] . ' - ' . $row['nombre'];
    echo ' | Grupo: ' . ($row['grupo_muscular'] ?? 'NULL');
    echo ' | Sets: ' . ($row['sets_recomendados'] ?? 'NULL');
    echo ' | Reps: ' . ($row['reps_recomendadas'] ?? 'NULL');
    echo "\n";
}

$conn->close();
?>
