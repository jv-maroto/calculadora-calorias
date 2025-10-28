<?php
session_start();
header('Content-Type: application/json');

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

// Conexión a base de datos
require_once 'connection.php';

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

$ejercicio_id = isset($data['ejercicio_id']) ? intval($data['ejercicio_id']) : 0;
$fecha = isset($data['fecha']) ? $data['fecha'] : date('Y-m-d');
$sets = isset($data['sets']) ? $data['sets'] : [];

if (!$ejercicio_id || empty($sets)) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Comenzar transacción
    $conn->begin_transaction();

    // Eliminar registros anteriores de este ejercicio en esta fecha para este usuario
    $sql_delete = "DELETE FROM registros_entrenamiento
                   WHERE ejercicio_id = ? AND fecha = ? AND nombre = ? AND apellidos = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("isss", $ejercicio_id, $fecha, $nombre, $apellidos);
    $stmt->execute();
    $stmt->close();

    // Insertar nuevos sets
    $sql_insert = "INSERT INTO registros_entrenamiento
                   (ejercicio_id, nombre, apellidos, fecha, set_numero, peso, reps, rpe)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);

    foreach ($sets as $set) {
        $peso = floatval($set['peso']);
        $reps = intval($set['reps']);
        $rpe = isset($set['rpe']) && $set['rpe'] !== '' ? floatval($set['rpe']) : null;
        $set_numero = intval($set['set_numero']);

        $stmt->bind_param("isssiidi",
            $ejercicio_id,
            $nombre,
            $apellidos,
            $fecha,
            $set_numero,
            $peso,
            $reps,
            $rpe
        );
        $stmt->execute();
    }

    $stmt->close();

    // Confirmar transacción
    $conn->commit();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Entrenamiento guardado correctamente']);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->close();

    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}
?>
