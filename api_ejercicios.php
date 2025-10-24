<?php
// Configurar para devolver solo JSON
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Leer datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

require_once 'config.php';

$accion = $data['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            // Validar datos requeridos
            if (!isset($data['dia_id']) || !isset($data['nombre']) || !isset($data['orden'])) {
                throw new Exception('Faltan datos requeridos');
            }

            // Asegurar tipos correctos
            $dia_id = intval($data['dia_id']);
            $nombre = strval($data['nombre']);
            $orden = intval($data['orden']);
            $sets_recomendados = intval($data['sets_recomendados'] ?? 3);
            $reps_recomendadas = strval($data['reps_recomendadas'] ?? '8-12');
            $tipo_equipo = strval($data['tipo_equipo'] ?? '');
            $grupo_muscular = strval($data['grupo_muscular'] ?? '');
            $notas = strval($data['notas'] ?? '');

            $sql = "INSERT INTO ejercicios (dia_id, nombre, orden, sets_recomendados, sets_objetivo, reps_recomendadas, reps_objetivo, tipo_equipo, grupo_muscular, notas)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception('Error preparando query: ' . $conn->error);
            }

            $stmt->bind_param("isiissssss",
                $dia_id,
                $nombre,
                $orden,
                $sets_recomendados,
                $sets_recomendados,
                $reps_recomendadas,
                $reps_recomendadas,
                $tipo_equipo,
                $grupo_muscular,
                $notas
            );

            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando query: ' . $stmt->error);
            }

            echo json_encode([
                'success' => true,
                'accion' => 'crear',
                'ejercicio_id' => $conn->insert_id
            ]);
            break;

        case 'editar':
            // Validar datos requeridos
            if (!isset($data['ejercicio_id']) || !isset($data['nombre'])) {
                throw new Exception('Faltan datos requeridos');
            }

            // Asegurar tipos correctos
            $ejercicio_id = intval($data['ejercicio_id']);
            $nombre = strval($data['nombre']);
            $orden = intval($data['orden'] ?? 0);
            $sets_recomendados = intval($data['sets_recomendados'] ?? 3);
            $reps_recomendadas = strval($data['reps_recomendadas'] ?? '8-12');
            $tipo_equipo = strval($data['tipo_equipo'] ?? '');
            $grupo_muscular = strval($data['grupo_muscular'] ?? '');
            $notas = strval($data['notas'] ?? '');

            $sql = "UPDATE ejercicios
                    SET nombre = ?, orden = ?, sets_recomendados = ?, sets_objetivo = ?,
                        reps_recomendadas = ?, reps_objetivo = ?,
                        tipo_equipo = ?, grupo_muscular = ?, notas = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception('Error preparando query: ' . $conn->error);
            }

            $stmt->bind_param("sisisssssi",
                $nombre,
                $orden,
                $sets_recomendados,
                $sets_recomendados,
                $reps_recomendadas,
                $reps_recomendadas,
                $tipo_equipo,
                $grupo_muscular,
                $notas,
                $ejercicio_id
            );

            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando query: ' . $stmt->error);
            }

            echo json_encode([
                'success' => true,
                'accion' => 'editar',
                'ejercicio_id' => $data['ejercicio_id']
            ]);
            break;

        case 'eliminar':
            // Validar datos requeridos
            if (!isset($data['ejercicio_id'])) {
                throw new Exception('Falta ejercicio_id');
            }

            // Iniciar transacción
            $conn->begin_transaction();

            // Eliminar primero los registros de entrenamiento
            $sql_registros = "DELETE FROM registros_entrenamiento WHERE ejercicio_id = ?";
            $stmt = $conn->prepare($sql_registros);
            $stmt->bind_param("i", $data['ejercicio_id']);
            $stmt->execute();

            // Eliminar el ejercicio
            $sql_ejercicio = "DELETE FROM ejercicios WHERE id = ?";
            $stmt = $conn->prepare($sql_ejercicio);
            $stmt->bind_param("i", $data['ejercicio_id']);
            $stmt->execute();

            // Commit
            $conn->commit();

            echo json_encode([
                'success' => true,
                'accion' => 'eliminar',
                'ejercicio_id' => $data['ejercicio_id']
            ]);
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'sql_error' => isset($conn) ? $conn->error : null
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>
