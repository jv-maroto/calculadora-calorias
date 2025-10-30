<?php
header('Content-Type: application/json');
include 'connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'crear_evento':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['fecha']) || !isset($data['tipo']) || !isset($data['titulo'])) {
                throw new Exception('Datos incompletos');
            }

            $stmt = $conn->prepare("INSERT INTO eventos_calendario (fecha, tipo, titulo, descripcion, es_recordatorio)
                                    VALUES (?, ?, ?, ?, ?)");

            $descripcion = isset($data['descripcion']) ? $data['descripcion'] : '';
            $esRecordatorio = isset($data['es_recordatorio']) ? intval($data['es_recordatorio']) : 0;

            $stmt->bind_param("ssssi",
                $data['fecha'],
                $data['tipo'],
                $data['titulo'],
                $descripcion,
                $esRecordatorio
            );

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'id' => $conn->insert_id,
                    'message' => 'Evento creado correctamente'
                ]);
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
            break;

        case 'obtener_eventos':
            $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
            $anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

            $stmt = $conn->prepare("SELECT id, fecha, tipo, titulo, descripcion, completado, es_recordatorio
                                    FROM eventos_calendario
                                    WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
                                    ORDER BY fecha ASC");
            $stmt->bind_param("ii", $mes, $anio);
            $stmt->execute();
            $result = $stmt->get_result();

            $eventos = [];
            while ($row = $result->fetch_assoc()) {
                $eventos[] = $row;
            }

            echo json_encode([
                'success' => true,
                'eventos' => $eventos
            ]);

            $stmt->close();
            break;

        case 'obtener_recordatorios_hoy':
            $hoy = date('Y-m-d');

            $stmt = $conn->prepare("SELECT id, fecha, tipo, titulo, descripcion
                                    FROM eventos_calendario
                                    WHERE fecha = ? AND es_recordatorio = 1 AND completado = 0
                                    ORDER BY fecha_creacion DESC");
            $stmt->bind_param("s", $hoy);
            $stmt->execute();
            $result = $stmt->get_result();

            $recordatorios = [];
            while ($row = $result->fetch_assoc()) {
                $recordatorios[] = $row;
            }

            echo json_encode([
                'success' => true,
                'recordatorios' => $recordatorios
            ]);

            $stmt->close();
            break;

        case 'obtener_todos_recordatorios':
            $stmt = $conn->prepare("SELECT id, fecha, tipo, titulo, descripcion, completado
                                    FROM eventos_calendario
                                    WHERE es_recordatorio = 1
                                    ORDER BY fecha DESC, fecha_creacion DESC");
            $stmt->execute();
            $result = $stmt->get_result();

            $recordatorios = [];
            while ($row = $result->fetch_assoc()) {
                $recordatorios[] = $row;
            }

            echo json_encode([
                'success' => true,
                'recordatorios' => $recordatorios
            ]);

            $stmt->close();
            break;

        case 'marcar_completado':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id'])) {
                throw new Exception('ID no especificado');
            }

            $stmt = $conn->prepare("UPDATE eventos_calendario SET completado = 1 WHERE id = ?");
            $stmt->bind_param("i", $data['id']);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Evento marcado como completado'
                ]);
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
            break;

        case 'eliminar_evento':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['id'])) {
                throw new Exception('ID no especificado');
            }

            $stmt = $conn->prepare("DELETE FROM eventos_calendario WHERE id = ?");
            $stmt->bind_param("i", $data['id']);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Evento eliminado'
                ]);
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
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
