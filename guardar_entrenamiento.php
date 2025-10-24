<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

// Leer datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar datos requeridos
if (!isset($data['ejercicio_id']) || !isset($data['fecha']) || !isset($data['sets'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

$ejercicio_id = intval($data['ejercicio_id']);
$fecha = $data['fecha'];
$sets = $data['sets'];

// Validar fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de fecha inválido']);
    exit;
}

// Conexión a base de datos
require_once 'config.php';

// Iniciar transacción
$conn->begin_transaction();

try {
    // Eliminar registros existentes para este ejercicio en esta fecha
    $sql_delete = "DELETE FROM registros_entrenamiento
                   WHERE ejercicio_id = ? AND nombre = ? AND apellidos = ? AND fecha = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("isss", $ejercicio_id, $nombre, $apellidos, $fecha);
    $stmt->execute();
    $stmt->close();

    // Insertar nuevos registros
    $sql_insert = "INSERT INTO registros_entrenamiento
                   (nombre, apellidos, ejercicio_id, fecha, set_numero, peso, reps, rpe)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);

    foreach ($sets as $set) {
        $peso = floatval($set['peso']);
        $reps = intval($set['reps']);
        $set_numero = intval($set['set_numero']);
        $rpe = isset($set['rpe']) ? floatval($set['rpe']) : null;

        $stmt->bind_param("ssissiid",
            $nombre,
            $apellidos,
            $ejercicio_id,
            $fecha,
            $set_numero,
            $peso,
            $reps,
            $rpe
        );
        $stmt->execute();
    }

    $stmt->close();

    // Commit transacción
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Entrenamiento guardado correctamente',
        'sets_guardados' => count($sets)
    ]);

} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
