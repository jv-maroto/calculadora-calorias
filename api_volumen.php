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
    if ($accion === 'aumentar') {
        // Validar datos requeridos
        if (!isset($data['grupo_muscular']) || !isset($data['cantidad'])) {
            throw new Exception('Faltan datos requeridos');
        }

        $grupo_muscular = $data['grupo_muscular'];
        $cantidad = intval($data['cantidad']);

        if ($cantidad <= 0 || $cantidad > 8) {
            throw new Exception('Cantidad inválida (debe ser entre 1 y 8)');
        }

        // Obtener ejercicios del grupo ordenados por sets_recomendados (los que tienen menos series)
        $sql_get = "SELECT id, nombre, sets_recomendados FROM ejercicios
                    WHERE grupo_muscular = ?
                    ORDER BY sets_recomendados ASC, id ASC";
        $stmt_get = $conn->prepare($sql_get);
        $stmt_get->bind_param("s", $grupo_muscular);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        $ejercicios = $result->fetch_all(MYSQLI_ASSOC);

        if (count($ejercicios) == 0) {
            throw new Exception('No se encontraron ejercicios para este grupo muscular');
        }

        // Distribuir las series inteligentemente:
        // Priorizar ejercicios con menos series actuales
        $series_distribuidas = 0;
        $ejercicios_actualizados = 0;
        $idx = 0;

        while ($series_distribuidas < $cantidad && $idx < count($ejercicios)) {
            $ejercicio = $ejercicios[$idx];

            // Añadir 1 serie a este ejercicio
            $sql_update = "UPDATE ejercicios
                          SET sets_recomendados = sets_recomendados + 1,
                              sets_objetivo = sets_objetivo + 1
                          WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $ejercicio['id']);
            $stmt_update->execute();

            $ejercicios_actualizados++;
            $series_distribuidas++;

            // Rotar al siguiente ejercicio
            $idx++;
            if ($idx >= count($ejercicios)) {
                $idx = 0; // Volver al principio si quedan series por distribuir
            }
        }

        echo json_encode([
            'success' => true,
            'ejercicios_actualizados' => $ejercicios_actualizados,
            'grupo_muscular' => $grupo_muscular,
            'cantidad_agregada' => $cantidad,
            'total_ejercicios' => count($ejercicios)
        ]);

    } elseif ($accion === 'aumentar_global') {
        // Validar datos requeridos
        if (!isset($data['cantidad'])) {
            throw new Exception('Falta cantidad');
        }

        $cantidad = intval($data['cantidad']);
        $estado = $data['estado'] ?? 'todos';

        if ($cantidad <= 0 || $cantidad > 6) {
            throw new Exception('Cantidad inválida (debe ser entre 1 y 6)');
        }

        // Obtener grupos musculares según el filtro
        if ($estado === 'bajo') {
            $condicion = "< 10";
        } elseif ($estado === 'optimo') {
            $condicion = "BETWEEN 10 AND 18";
        } else {
            // 'todos'
            $condicion = ">= 0";
        }

        $sql_grupos = "SELECT grupo_muscular, SUM(sets_recomendados) as total_sets
                       FROM ejercicios
                       WHERE grupo_muscular IS NOT NULL
                       GROUP BY grupo_muscular
                       HAVING total_sets $condicion";

        $result_grupos = $conn->query($sql_grupos);
        $grupos = $result_grupos->fetch_all(MYSQLI_ASSOC);

        if (count($grupos) == 0) {
            throw new Exception('No se encontraron grupos musculares para actualizar con ese filtro');
        }

        $total_ejercicios_actualizados = 0;

        // Para cada grupo muscular, distribuir las series
        foreach ($grupos as $grupo_data) {
            $grupo_muscular = $grupo_data['grupo_muscular'];

            // Obtener ejercicios del grupo ordenados por sets_recomendados
            $sql_get = "SELECT id, nombre, sets_recomendados FROM ejercicios
                        WHERE grupo_muscular = ?
                        ORDER BY sets_recomendados ASC, id ASC";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("s", $grupo_muscular);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $ejercicios = $result->fetch_all(MYSQLI_ASSOC);

            if (count($ejercicios) == 0) continue;

            // Distribuir las series en este grupo
            $series_distribuidas = 0;
            $idx = 0;

            while ($series_distribuidas < $cantidad) {
                $ejercicio = $ejercicios[$idx];

                // Añadir 1 serie a este ejercicio
                $sql_update = "UPDATE ejercicios
                              SET sets_recomendados = sets_recomendados + 1,
                                  sets_objetivo = sets_objetivo + 1
                              WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("i", $ejercicio['id']);
                $stmt_update->execute();

                $series_distribuidas++;
                $total_ejercicios_actualizados++;

                // Rotar al siguiente ejercicio
                $idx++;
                if ($idx >= count($ejercicios)) {
                    $idx = 0;
                }
            }
        }

        echo json_encode([
            'success' => true,
            'ejercicios_actualizados' => $total_ejercicios_actualizados,
            'grupos_afectados' => count($grupos),
            'cantidad_agregada' => $cantidad,
            'estado_filtro' => $estado
        ]);

    } else {
        throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>
