<?php
header('Content-Type: application/json');
include 'connection.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('No se recibieron datos válidos');
    }

    // Los datos vienen en $data['formulario'] y $data['resultados']
    $form = $data['formulario'];
    $result = $data['resultados'];

    // Log temporal para debug
    error_log("Datos recibidos: " . json_encode($data));

    $stmt = $conn->prepare("INSERT INTO planes_nutricionales
        (nombre, apellidos, edad, sexo, peso, altura, anos_entrenando, historial_dietas,
         dias_entreno, horas_gym, dias_cardio, horas_cardio, tipo_trabajo, horas_trabajo,
         horas_sueno, objetivo, kg_objetivo, velocidad, nivel_gym, tmb, tdee, calorias_plan,
         duracion_semanas, duracion_meses, proteina_gramos, grasa_gramos, carbohidratos_gramos, plan_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $planJson = json_encode($result);

    // Obtener valores con fallback a null
    $nombre = isset($form['nombre']) ? $form['nombre'] : '';
    $apellidos = isset($form['apellidos']) ? $form['apellidos'] : '';
    $anosEntrenando = isset($form['anos_entrenando']) ? $form['anos_entrenando'] : null;
    $historialDietas = isset($form['historial_dietas']) ? $form['historial_dietas'] : null;
    $kgObjetivo = isset($form['kg_objetivo']) ? $form['kg_objetivo'] : null;
    $velocidad = isset($form['velocidad']) ? $form['velocidad'] : null;
    $nivelGym = isset($form['nivel_gym']) ? $form['nivel_gym'] : null;

    // Obtener datos del plan (puede estar en $result['plan'] o directo en $result)
    $plan = isset($result['plan']) ? $result['plan'] : $result;
    $tipo = isset($plan['tipo']) ? $plan['tipo'] : $form['objetivo'];

    // Calcular calorías según el tipo de plan
    if ($tipo === 'deficit') {
        $calorias = isset($result['tdeeAjustado']) && isset($result['deficitDiario']) ?
            ($result['tdeeAjustado'] - $result['deficitDiario']) : $result['tdee'];
    } elseif ($tipo === 'volumen') {
        if (isset($plan['semanas'][0]['calorias'])) {
            $calorias = $plan['semanas'][0]['calorias'];
        } elseif (isset($plan['fases'][0]['calorias'])) {
            $calorias = $plan['fases'][0]['calorias'];
        } else {
            $calorias = $result['tdee'];
        }
    } else {
        $calorias = $result['tdee'];
    }

    $duracionSemanas = isset($plan['duracion']['semanas']) ? $plan['duracion']['semanas'] : 0;
    $duracionMeses = isset($plan['duracion']['meses']) ? $plan['duracion']['meses'] : 0;

    // Validar macros (pueden estar en plan o en result)
    $macros = isset($plan['macros']) ? $plan['macros'] : (isset($result['macros']) ? $result['macros'] : null);

    if (!$macros || !isset($macros['proteina'])) {
        throw new Exception('Datos de macronutrientes incompletos. Plan: ' . json_encode($plan));
    }

    $proteina = intval($macros['proteina']);
    $grasa = intval($macros['grasa']);
    $carbohidratos = intval($macros['carbohidratos']);

    $stmt->bind_param("ssisdissididsddsdssdddiiiiis",
        $nombre,
        $apellidos,
        $form['edad'],
        $form['sexo'],
        $form['peso'],
        $form['altura'],
        $anosEntrenando,
        $historialDietas,
        $form['dias_entreno'],
        $form['horas_gym'],
        $form['dias_cardio'],
        $form['horas_cardio'],
        $form['tipo_trabajo'],
        $form['horas_trabajo'],
        $form['horas_sueno'],
        $form['objetivo'],
        $kgObjetivo,
        $velocidad,
        $nivelGym,
        $result['tmb'],
        $result['tdee'],
        $calorias,
        $duracionSemanas,
        $duracionMeses,
        $proteina,
        $grasa,
        $carbohidratos,
        $planJson
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'id' => $conn->insert_id,
            'message' => 'Plan guardado correctamente'
        ]);
    } else {
        throw new Exception($stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
