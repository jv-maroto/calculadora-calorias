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

    // Obtener datos del resultado
    $tipo = isset($result['tipo']) ? $result['tipo'] : $form['objetivo'];

    // Calcular calorías según el tipo de plan
    if ($tipo === 'deficit') {
        $calorias = isset($result['tdeeAjustado']) && isset($result['deficitDiario']) ?
            ($result['tdeeAjustado'] - $result['deficitDiario']) : $result['tdee'];
    } elseif ($tipo === 'volumen') {
        if (isset($result['semanas'][0]['calorias'])) {
            $calorias = $result['semanas'][0]['calorias'];
        } elseif (isset($result['fases'][0]['calorias'])) {
            $calorias = $result['fases'][0]['calorias'];
        } else {
            $calorias = $result['tdee'];
        }
    } else {
        $calorias = $result['tdee'];
    }

    $duracionSemanas = isset($result['duracion']['semanas']) ? $result['duracion']['semanas'] : 0;
    $duracionMeses = isset($result['duracion']['meses']) ? $result['duracion']['meses'] : 0;

    // Validar macros
    if (!isset($result['macros']) || !isset($result['macros']['proteina'])) {
        throw new Exception('Datos de macronutrientes incompletos. Resultado: ' . json_encode($result));
    }

    $proteina = intval($result['macros']['proteina']);
    $grasa = intval($result['macros']['grasa']);
    $carbohidratos = intval($result['macros']['carbohidratos']);

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
