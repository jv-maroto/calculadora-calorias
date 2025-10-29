<?php
header('Content-Type: application/json');
include 'connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'guardar_peso':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['fecha']) || !isset($data['peso'])) {
                throw new Exception('Datos incompletos');
            }

            $stmt = $conn->prepare("INSERT INTO peso_diario (fecha, peso, notas) VALUES (?, ?, ?)
                                    ON DUPLICATE KEY UPDATE peso = ?, notas = ?");

            $notas = isset($data['notas']) ? $data['notas'] : '';

            $stmt->bind_param("sdsds",
                $data['fecha'],
                $data['peso'],
                $notas,
                $data['peso'],
                $notas
            );

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Peso guardado correctamente'
                ]);
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
            break;

        case 'obtener_historial':
            $dias = isset($_GET['dias']) ? intval($_GET['dias']) : 30;

            $stmt = $conn->prepare("SELECT fecha, peso, notas FROM peso_diario
                                    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                                    ORDER BY fecha DESC");
            $stmt->bind_param("i", $dias);
            $stmt->execute();
            $result = $stmt->get_result();

            $historial = [];
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $historial
            ]);

            $stmt->close();
            break;

        case 'eliminar_peso':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['fecha'])) {
                throw new Exception('Fecha no especificada');
            }

            $stmt = $conn->prepare("DELETE FROM peso_diario WHERE fecha = ?");
            $stmt->bind_param("s", $data['fecha']);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registro eliminado'
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
