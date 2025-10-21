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

    // Identificación del usuario (obligatorios)
    $nombre = trim($form['nombre'] ?? '');
    $apellidos = trim($form['apellidos'] ?? '');

    if (empty($nombre) || empty($apellidos)) {
        throw new Exception('Debes proporcionar nombre y apellidos');
    }

    // Datos básicos (obligatorios)
    $edad = intval($form['edad'] ?? 0);
    $sexo = $form['sexo'] ?? 'hombre';
    $peso = floatval($form['peso'] ?? 0);
    $altura = intval($form['altura'] ?? 0);
    $anos_entrenando = !empty($form['anos_entrenando']) ? $form['anos_entrenando'] : null;
    $historial_dietas = !empty($form['historial_dietas']) ? $form['historial_dietas'] : null;
    $tipo_trabajo = $form['tipo_trabajo'] ?? 'sedentario';

    // Obtener objetivo del formulario o de los resultados
    $objetivo = $form['objetivo'] ?? null;
    if (!$objetivo && isset($res['objetivo'])) {
        $objetivo = $res['objetivo'];
    }
    if (!$objetivo && isset($res['plan']['tipo'])) {
        $objetivo = $res['plan']['tipo'];
    }
    if (!$objetivo) {
        $objetivo = 'mantenimiento';
    }

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
        (nombre, apellidos, edad, sexo, peso, altura, anos_entrenando, historial_dietas,
         dias_entreno, horas_gym, dias_cardio, horas_cardio,
         tipo_trabajo, horas_trabajo, horas_sueno, objetivo, kg_objetivo, velocidad, nivel_gym,
         tmb, tdee, calorias_plan, duracion_semanas, duracion_meses,
         proteina_gramos, grasa_gramos, carbohidratos_gramos, plan_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparando query: " . $conn->error);
    }

    // Convertir NULL a valores por defecto para bind_param
    $anos_entrenando = $anos_entrenando ?? '';
    $historial_dietas = $historial_dietas ?? '';
    $velocidad = $velocidad ?? '';
    $nivel_gym = $nivel_gym ?? '';
    $duracion_semanas = $duracion_semanas ?? 0;
    $duracion_meses = $duracion_meses ?? 0;

    // Bind con tipos correctos (28 parámetros)
    // Cadena: s s i s d i s s i d i d s d d s d s s d d d i i i i i s
    $stmt->bind_param(
        "ssisdissididsddsdssdddiiiiis",
        $nombre,            // 1  - s
        $apellidos,         // 2  - s
        $edad,              // 3  - i
        $sexo,              // 4  - s
        $peso,              // 5  - d
        $altura,            // 6  - i
        $anos_entrenando,   // 7  - s
        $historial_dietas,  // 8  - s
        $dias_entreno,      // 9  - i
        $horas_gym,         // 10 - d
        $dias_cardio,       // 11 - i
        $horas_cardio,      // 12 - d
        $tipo_trabajo,      // 13 - s
        $horas_trabajo,     // 14 - d
        $horas_sueno,       // 15 - d
        $objetivo,          // 16 - s
        $kg_objetivo,       // 17 - d
        $velocidad,         // 18 - s
        $nivel_gym,         // 19 - s
        $tmb,               // 20 - d
        $tdee,              // 21 - d
        $calorias_plan,     // 22 - d
        $duracion_semanas,  // 23 - i
        $duracion_meses,    // 24 - i
        $proteina,          // 25 - i
        $grasa,             // 26 - i
        $carbohidratos,     // 27 - i
        $plan_json          // 28 - s
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
