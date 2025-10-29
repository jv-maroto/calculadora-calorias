<?php
header('Content-Type: application/json');
include 'connection.php';

try {
    $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
    $apellidos = isset($_GET['apellidos']) ? $_GET['apellidos'] : '';

    if (empty($nombre) && empty($apellidos)) {
        // Si no hay filtros, devolver los últimos 10 planes
        $stmt = $conn->prepare("SELECT id, nombre, apellidos, objetivo, fecha_calculo, plan_json
                                FROM planes_nutricionales
                                ORDER BY fecha_calculo DESC
                                LIMIT 10");
        $stmt->execute();
    } else {
        // Buscar por nombre y/o apellidos
        $stmt = $conn->prepare("SELECT id, nombre, apellidos, objetivo, fecha_calculo, plan_json
                                FROM planes_nutricionales
                                WHERE nombre LIKE ? OR apellidos LIKE ?
                                ORDER BY fecha_calculo DESC
                                LIMIT 10");
        $busqueda = "%$nombre%";
        $busquedaApellidos = "%$apellidos%";
        $stmt->bind_param("ss", $busqueda, $busquedaApellidos);
        $stmt->execute();
    }

    $result = $stmt->get_result();
    $planes = [];

    while ($row = $result->fetch_assoc()) {
        $planes[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'apellidos' => $row['apellidos'],
            'objetivo' => $row['objetivo'],
            'fecha_calculo' => $row['fecha_calculo'],
            'plan_json' => json_decode($row['plan_json'], true)
        ];
    }

    echo json_encode([
        'success' => true,
        'planes' => $planes
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
