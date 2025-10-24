<?php
// Script para inicializar las tablas de rutinas en la base de datos
require_once 'config.php';

echo "Configurando tablas de rutinas...\n\n";

// Leer el archivo SQL
$sql_file = file_get_contents('rutinas_db.sql');

if (!$sql_file) {
    die("❌ Error: No se pudo leer el archivo rutinas_db.sql\n");
}

// Ejecutar múltiples queries
$queries = explode(';', $sql_file);
$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;

    try {
        if ($conn->query($query)) {
            $success_count++;
        } else {
            $error_count++;
            echo "⚠️ Advertencia: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "⚠️ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "✅ Queries ejecutadas exitosamente: $success_count\n";
echo "⚠️ Errores/Advertencias: $error_count\n\n";

// Verificar que se hayan creado las tablas
$tables = ['rutinas', 'dias_entrenamiento', 'ejercicios', 'registros_entrenamiento'];
$all_ok = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Tabla '$table' creada correctamente\n";
    } else {
        echo "❌ Tabla '$table' NO existe\n";
        $all_ok = false;
    }
}

// Verificar datos insertados
echo "\n--- Verificación de datos ---\n";
$result = $conn->query("SELECT COUNT(*) as total FROM rutinas");
$row = $result->fetch_assoc();
echo "📋 Rutinas: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM dias_entrenamiento");
$row = $result->fetch_assoc();
echo "📅 Días: " . $row['total'] . "\n";

$result = $conn->query("SELECT COUNT(*) as total FROM ejercicios");
$row = $result->fetch_assoc();
echo "💪 Ejercicios: " . $row['total'] . "\n";

$conn->close();

if ($all_ok) {
    echo "\n✅ ¡Sistema de rutinas configurado correctamente!\n";
    echo "Puedes acceder a: http://localhost/Calculator/rutinas.php\n";
} else {
    echo "\n❌ Hubo problemas al configurar el sistema\n";
}
?>
