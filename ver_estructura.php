<?php
require 'config.php';
$result = $conn->query('DESCRIBE ejercicios');
echo "Estructura tabla ejercicios:\n\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>
