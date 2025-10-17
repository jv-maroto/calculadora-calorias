<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'connection.php';

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('No se recibieron datos JSON');
    }

    // Log para debugging
    file_put_contents('debug_guardar.log', date('Y-m-d H:i:s') . " - Datos recibidos: " . print_r($data, true) . "\n", FILE_APPEND);

    // Extraer con valores seguros
    $form = $data['formulario'] ?? [];
    $res = $data['resultados'] ?? [];
    $plan = $res['plan'] ?? [];

    // Datos básicos (obligatorios)
    $edad = intval($form['edad'] ?? 0);
    $sexo = $form['sexo'] ?? 'hombre';
    $peso = floatval($form['peso'] ?? 0);
    $altura = intval($form['altura'] ?? 0);
    $tipo_trabajo = $form['tipo_trabajo'] ?? 'sedentario';
    $objetivo = $form['objetivo'] ?? 'mantenimiento';

    if ($edad == 0 || $peso == 0 || $altura == 0) {
        throw new Exception('Faltan datos básicos obligatorios');
    }

    // Actividad (opcionales con default 0)
    $dias_entreno = intval($form['dias_entreno'] ?? 0);
    $horas_gym = floatval($form['horas_gym'] ?? 0);
    $dias_cardio = intval($form['dias_cardio'] ?? 0);
    $horas_cardio = floatval($form['horas_cardio'] ?? 0);
    $horas_trabajo = floatval($form['horas_trabajo'] ?? 0);
    $horas_sueno = floatval($form['horas_sueno'] ?? 0);

    // Objetivo específico
    $kg_objetivo = floatval($form['kg_objetivo'] ?? 0);
    $velocidad = !empty($form['velocidad']) ? $form['velocidad'] : null;
    $nivel_gym = !empty($form['nivel_gym']) ? $form['nivel_gym'] : null;

    // Resultados
    $tmb = floatval($res['tmb'] ?? 0);
    $tdee = floatval($res['tdee'] ?? 0);

    // Calorías del plan
    $calorias_plan = $tdee;
    if (isset($plan['fases'][0]['calorias'])) {
        $calorias_plan = floatval($plan['fases'][0]['calorias']);
    }

    // Duración
    $duracion_semanas = isset($plan['duracion']['semanas']) ? intval($plan['duracion']['semanas']) : null;
    $duracion_meses = isset($plan['duracion']['meses']) ? intval($plan['duracion']['meses']) : null;

    // Macros
    $proteina = intval($plan['macros']['proteina'] ?? 0);
    $grasa = intval($plan['macros']['grasa'] ?? 0);
    $carbohidratos = intval($plan['macros']['carbohidratos'] ?? 0);

    // JSON completo
    $plan_json = json_encode($res, JSON_UNESCAPED_UNICODE);

    // INSERT con manejo de NULL
    $sql = "INSERT INTO planes_nutricionales
        (edad, sexo, peso, altura, dias_entreno, horas_gym, dias_cardio, horas_cardio,
         tipo_trabajo, horas_trabajo, horas_sueno, objetivo, kg_objetivo, velocidad, nivel_gym,
         tmb, tdee, calorias_plan, duracion_semanas, duracion_meses,
         proteina_gramos, grasa_gramos, carbohidratos_gramos, plan_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparando query: " . $conn->error);
    }

    // Bind con tipos correctos
    $stmt->bind_param(
        "isdiididsddsdssdddiiiis",
        $edad,              // i
        $sexo,              // s
        $peso,              // d
        $altura,            // i
        $dias_entreno,      // i
        $horas_gym,         // d
        $dias_cardio,       // i
        $horas_cardio,      // d
        $tipo_trabajo,      // s
        $horas_trabajo,     // d
        $horas_sueno,       // d
        $objetivo,          // s
        $kg_objetivo,       // d
        $velocidad,         // s (puede ser NULL)
        $nivel_gym,         // s (puede ser NULL)
        $tmb,               // d
        $tdee,              // d
        $calorias_plan,     // d
        $duracion_semanas,  // i (puede ser NULL)
        $duracion_meses,    // i (puede ser NULL)
        $proteina,          // i
        $grasa,             // i
        $carbohidratos,     // i
        $plan_json          // s
    );

    if (!$stmt->execute()) {
        file_put_contents('debug_guardar.log', date('Y-m-d H:i:s') . " - Error SQL: " . $stmt->error . "\n", FILE_APPEND);
        throw new Exception("Error SQL: " . $stmt->error);
    }

    $id = $conn->insert_id;

    file_put_contents('debug_guardar.log', date('Y-m-d H:i:s') . " - Guardado OK con ID: $id\n", FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => 'Plan guardado correctamente',
        'id' => $id
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    file_put_contents('debug_guardar.log', date('Y-m-d H:i:s') . " - EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
