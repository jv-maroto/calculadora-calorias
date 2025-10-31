<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
include 'connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'obtener_todos':
            $stmt = $conn->prepare("SELECT id, nombre, musculo_principal as grupo_muscular, tipo_equipo as tipo_ejercicio, notas as descripcion
                                    FROM ejercicios
                                    ORDER BY musculo_principal, nombre");
            $stmt->execute();
            $result = $stmt->get_result();

            $ejercicios = [];
            while ($row = $result->fetch_assoc()) {
                $ejercicios[] = $row;
            }

            echo json_encode([
                'success' => true,
                'ejercicios' => $ejercicios
            ]);

            $stmt->close();
            break;

        case 'crear':
            $data = json_decode(file_get_contents('php://input'), true);

            $stmt = $conn->prepare("INSERT INTO ejercicios (dia_id, nombre, orden, sets_recomendados, reps_recomendadas, tipo_equipo, musculo_principal, notas)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiissss",
                $data['dia_id'],
                $data['nombre'],
                $data['orden'],
                $data['sets_recomendados'],
                $data['reps_recomendadas'],
                $data['tipo_equipo'],
                $data['grupo_muscular'],
                $data['notas']
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'id' => $conn->insert_id]);
            } else {
                throw new Exception('Error al crear el ejercicio');
            }

            $stmt->close();
            break;

        case 'editar':
            $data = json_decode(file_get_contents('php://input'), true);

            $stmt = $conn->prepare("UPDATE ejercicios
                                    SET nombre = ?, orden = ?, sets_recomendados = ?, reps_recomendadas = ?,
                                        tipo_equipo = ?, musculo_principal = ?, notas = ?
                                    WHERE id = ?");
            $stmt->bind_param("siissssi",
                $data['nombre'],
                $data['orden'],
                $data['sets_recomendados'],
                $data['reps_recomendadas'],
                $data['tipo_equipo'],
                $data['grupo_muscular'],
                $data['notas'],
                $data['ejercicio_id']
            );

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al actualizar el ejercicio');
            }

            $stmt->close();
            break;

        case 'eliminar':
            $data = json_decode(file_get_contents('php://input'), true);

            $stmt = $conn->prepare("DELETE FROM ejercicios WHERE id = ?");
            $stmt->bind_param("i", $data['id']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al eliminar el ejercicio');
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
