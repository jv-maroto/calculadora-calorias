<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'connection.php';

    // Obtener nombre y apellidos del query string
    $nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
    $apellidos = isset($_GET['apellidos']) ? trim($_GET['apellidos']) : '';

    if (empty($nombre) || empty($apellidos)) {
        throw new Exception('Debes proporcionar nombre y apellidos');
    }

    // Buscar planes de este usuario
    $sql = "SELECT id, fecha_calculo, objetivo, peso, calorias_plan, tmb, tdee, duracion_semanas, duracion_meses
            FROM planes_nutricionales
            WHERE nombre = ? AND apellidos = ?
            ORDER BY fecha_calculo DESC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombre, $apellidos);
    $stmt->execute();
    $result = $stmt->get_result();

    $planes = [];
    while ($row = $result->fetch_assoc()) {
        $planes[] = [
            'id' => $row['id'],
            'fecha' => $row['fecha_calculo'],
            'objetivo' => $row['objetivo'],
            'peso' => $row['peso'],
            'calorias' => $row['calorias_plan'],
            'tmb' => $row['tmb'],
            'tdee' => $row['tdee'],
            'duracion_semanas' => $row['duracion_semanas'],
            'duracion_meses' => $row['duracion_meses']
        ];
    }

    echo json_encode([
        'success' => true,
        'planes' => $planes,
        'total' => count($planes)
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
