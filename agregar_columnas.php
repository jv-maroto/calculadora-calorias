<?php
require_once 'config.php';

echo "Añadiendo columnas faltantes a la tabla ejercicios...\n\n";

// Añadir grupo_muscular
$sql1 = "ALTER TABLE ejercicios ADD COLUMN grupo_muscular VARCHAR(100)";
try {
    $conn->query($sql1);
    echo "✅ Columna 'grupo_muscular' añadida\n";
} catch (Exception $e) {
    echo "⚠️ grupo_muscular: " . $e->getMessage() . "\n";
}

// Añadir sets_recomendados
$sql2 = "ALTER TABLE ejercicios ADD COLUMN sets_recomendados INT DEFAULT 3";
try {
    $conn->query($sql2);
    echo "✅ Columna 'sets_recomendados' añadida\n";
} catch (Exception $e) {
    echo "⚠️ sets_recomendados: " . $e->getMessage() . "\n";
}

// Añadir reps_recomendadas
$sql3 = "ALTER TABLE ejercicios ADD COLUMN reps_recomendadas VARCHAR(20) DEFAULT '8-12'";
try {
    $conn->query($sql3);
    echo "✅ Columna 'reps_recomendadas' añadida\n";
} catch (Exception $e) {
    echo "⚠️ reps_recomendadas: " . $e->getMessage() . "\n";
}

echo "\n¡Proceso completado!\n";

$conn->close();
?>
