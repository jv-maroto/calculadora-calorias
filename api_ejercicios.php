<?php
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
