<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'connection.php';

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id == 0) {
        throw new Exception('ID de plan inválido');
    }

    // Cargar plan completo
    $sql = "SELECT * FROM planes_nutricionales WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        throw new Exception('Plan no encontrado');
    }

    $plan = $result->fetch_assoc();

    // Decodificar el JSON del plan
    $plan['plan_json'] = json_decode($plan['plan_json'], true);

    echo json_encode([
        'success' => true,
        'plan' => $plan
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
