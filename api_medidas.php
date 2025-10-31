<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
include 'connection.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'guardar':
            $data = json_decode(file_get_contents('php://input'), true);

            // Preparar columnas y valores
            $columns = ['usuario_id', 'fecha'];
            $values = [$usuario_id, $data['fecha']];
            $placeholders = ['?', '?'];
            $types = 'is';

            // Campos opcionales
            $campos = [
                'peso', 'cuello', 'hombros', 'pecho',
                'brazo_derecho', 'brazo_izquierdo',
                'antebrazo_derecho', 'antebrazo_izquierdo',
                'cintura', 'cadera',
                'muslo_derecho', 'muslo_izquierdo',
                'pantorrilla_derecha', 'pantorrilla_izquierda',
                'pliegue_triceps', 'pliegue_subescapular',
                'pliegue_suprailiaco', 'pliegue_abdominal',
                'pliegue_muslo', 'pliegue_pectoral', 'pliegue_axilar',
                'porcentaje_grasa', 'masa_muscular', 'masa_grasa', 'notas'
            ];

            foreach ($campos as $campo) {
                if (isset($data[$campo]) && $data[$campo] !== '') {
                    $columns[] = $campo;
                    $values[] = $data[$campo];
                    $placeholders[] = '?';
                    $types .= ($campo === 'notas') ? 's' : 'd';
                }
            }

            // Construir query
            $sql = "INSERT INTO medidas_corporales (" . implode(', ', $columns) . ")
                    VALUES (" . implode(', ', $placeholders) . ")
                    ON DUPLICATE KEY UPDATE ";

            $updates = [];
            foreach ($campos as $campo) {
                if (isset($data[$campo]) && $data[$campo] !== '') {
                    $updates[] = "$campo = VALUES($campo)";
                }
            }
            $sql .= implode(', ', $updates);

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'id' => $stmt->insert_id ?: $conn->insert_id]);
            } else {
                throw new Exception('Error al guardar medidas');
            }

            $stmt->close();
            break;

        case 'obtener_historial':
            $stmt = $conn->prepare("SELECT * FROM medidas_corporales
                                    WHERE usuario_id = ?
                                    ORDER BY fecha DESC
                                    LIMIT 50");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $medidas = [];
            while ($row = $result->fetch_assoc()) {
                $medidas[] = $row;
            }

            echo json_encode(['success' => true, 'medidas' => $medidas]);
            $stmt->close();
            break;

        case 'obtener_por_fecha':
            $fecha = $_GET['fecha'];
            $stmt = $conn->prepare("SELECT * FROM medidas_corporales
                                    WHERE usuario_id = ? AND fecha = ?");
            $stmt->bind_param("is", $usuario_id, $fecha);
            $stmt->execute();
            $result = $stmt->get_result();
            $medida = $result->fetch_assoc();

            echo json_encode(['success' => true, 'medida' => $medida]);
            $stmt->close();
            break;

        case 'eliminar':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $conn->prepare("DELETE FROM medidas_corporales
                                    WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $data['id'], $usuario_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al eliminar medida');
            }
            $stmt->close();
            break;

        case 'estadisticas':
            // Obtener última medición
            $stmt = $conn->prepare("SELECT * FROM medidas_corporales
                                    WHERE usuario_id = ?
                                    ORDER BY fecha DESC LIMIT 1");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $ultima = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Obtener primera medición
            $stmt = $conn->prepare("SELECT * FROM medidas_corporales
                                    WHERE usuario_id = ?
                                    ORDER BY fecha ASC LIMIT 1");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $primera = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Calcular progreso
            $progreso = [];
            if ($ultima && $primera) {
                $campos_numericos = ['peso', 'brazo_derecho', 'pecho', 'cintura', 'muslo_derecho', 'porcentaje_grasa'];
                foreach ($campos_numericos as $campo) {
                    if (isset($ultima[$campo]) && isset($primera[$campo])) {
                        $progreso[$campo] = [
                            'actual' => (float)$ultima[$campo],
                            'inicial' => (float)$primera[$campo],
                            'diferencia' => (float)$ultima[$campo] - (float)$primera[$campo]
                        ];
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'ultima' => $ultima,
                'primera' => $primera,
                'progreso' => $progreso
            ]);
            break;

        default:
            throw new Exception('Acción no válida');
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
