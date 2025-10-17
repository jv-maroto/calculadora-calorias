<?php
// Deshabilitar visualización de errores HTML
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

try {
    // Configuración de conexión
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "calculadora_calorias";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Establecer charset UTF-8
    $conn->set_charset("utf8mb4");

    // Obtener datos del formulario
    $edad = isset($_POST['edad']) ? intval($_POST['edad']) : 0;
    $sexo = isset($_POST['sexo']) ? $_POST['sexo'] : '';
    $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 0;
    $altura = isset($_POST['altura']) ? intval($_POST['altura']) : 0;
    $dias_entreno = isset($_POST['dias_entreno']) ? intval($_POST['dias_entreno']) : 0;
    $horas_gym = isset($_POST['horas_gym']) ? floatval($_POST['horas_gym']) : 0;
    $dias_cardio = isset($_POST['dias_cardio']) ? intval($_POST['dias_cardio']) : 0;
    $horas_cardio = isset($_POST['horas_cardio']) ? floatval($_POST['horas_cardio']) : 0;
    $tipo_trabajo = isset($_POST['tipo_trabajo']) ? $_POST['tipo_trabajo'] : '';
    $horas_trabajo = isset($_POST['horas_trabajo']) ? floatval($_POST['horas_trabajo']) : 0;
    $horas_sueno = isset($_POST['horas_sueno']) ? floatval($_POST['horas_sueno']) : 0;

    // Validar campos obligatorios
    if (empty($edad) || empty($sexo) || empty($peso) || empty($altura) || empty($tipo_trabajo)) {
        throw new Exception("Faltan campos obligatorios. Edad: $edad, Sexo: $sexo, Peso: $peso, Altura: $altura, Tipo trabajo: $tipo_trabajo");
    }

    // Debug: verificar valores antes de insertar
    if ($edad <= 0 || $peso <= 0 || $altura <= 0) {
        throw new Exception("Valores inválidos. Edad: $edad, Peso: $peso, Altura: $altura");
    }

    // CÁLCULO TMB usando Mifflin-St Jeor
    if ($sexo === 'hombre') {
        $tmb = (10 * $peso) + (6.25 * $altura) - (5 * $edad) + 5;
    } else {
        $tmb = (10 * $peso) + (6.25 * $altura) - (5 * $edad) - 161;
    }

    // CÁLCULO FACTOR DE ACTIVIDAD PERSONALIZADO
    $factor_actividad = 1.2; // Base sedentario

    // Factor de entrenamiento con pesas
    if ($dias_entreno > 0 && $horas_gym > 0) {
        $horas_gym_semanal = $dias_entreno * $horas_gym;
        if ($horas_gym_semanal <= 3) {
            $factor_actividad += 0.1;
        } elseif ($horas_gym_semanal <= 5) {
            $factor_actividad += 0.2;
        } elseif ($horas_gym_semanal <= 7) {
            $factor_actividad += 0.3;
        } else {
            $factor_actividad += 0.4;
        }
    }

    // Factor de cardio
    if ($dias_cardio > 0 && $horas_cardio > 0) {
        $horas_cardio_semanal = $dias_cardio * $horas_cardio;
        if ($horas_cardio_semanal <= 2) {
            $factor_actividad += 0.05;
        } elseif ($horas_cardio_semanal <= 4) {
            $factor_actividad += 0.1;
        } elseif ($horas_cardio_semanal <= 6) {
            $factor_actividad += 0.15;
        } else {
            $factor_actividad += 0.2;
        }
    }

    // Factor de trabajo
    if ($tipo_trabajo === 'activo') {
        if ($horas_trabajo <= 4) {
            $factor_actividad += 0.1;
        } elseif ($horas_trabajo <= 8) {
            $factor_actividad += 0.15;
        } else {
            $factor_actividad += 0.2;
        }
    } else {
        // Trabajo sedentario reduce ligeramente si pasa muchas horas
        if ($horas_trabajo > 10) {
            $factor_actividad -= 0.05;
        }
    }

    // Factor de sueño (influye en metabolismo)
    if ($horas_sueno < 6) {
        $factor_actividad -= 0.05; // Poco sueño reduce metabolismo
    } elseif ($horas_sueno >= 8 && $horas_sueno <= 9) {
        $factor_actividad += 0.02; // Sueño óptimo mejora metabolismo
    }

    // Limitar factor de actividad entre 1.2 y 2.0
    $factor_actividad = max(1.2, min(2.0, $factor_actividad));

    // CÁLCULO TDEE
    $tdee = $tmb * $factor_actividad;

    // CÁLCULO DE CALORÍAS PARA DIFERENTES OBJETIVOS
    $calorias_deficit = $tdee - 500; // Déficit de 500 kcal para perder ~0.5kg/semana
    $calorias_mantenimiento = $tdee;
    $calorias_volumen = $tdee + 300; // Superávit de 300 kcal para ganancia muscular limpia

    // Guardar en base de datos
    $stmt = $conn->prepare("INSERT INTO calculos_calorias (edad, sexo, peso, altura, dias_entreno, horas_gym, dias_cardio, horas_cardio, tipo_trabajo, horas_trabajo, horas_sueno, tmb, tdee, calorias_deficit, calorias_mantenimiento, calorias_volumen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Error al preparar: " . $conn->error);
    }

    // Tipos: i=int, s=string, d=decimal
    // edad(i), sexo(s), peso(d), altura(i), dias_entreno(i), horas_gym(d),
    // dias_cardio(i), horas_cardio(d), tipo_trabajo(s), horas_trabajo(d), horas_sueno(d),
    // tmb(d), tdee(d), calorias_deficit(d), calorias_mantenimiento(d), calorias_volumen(d)

    $stmt->bind_param("isdiididsdddddd",
        $edad,              // INT
        $sexo,              // STRING (ENUM)
        $peso,              // DECIMAL
        $altura,            // INT
        $dias_entreno,      // INT
        $horas_gym,         // DECIMAL
        $dias_cardio,       // INT
        $horas_cardio,      // DECIMAL
        $tipo_trabajo,      // STRING (ENUM)
        $horas_trabajo,     // DECIMAL
        $horas_sueno,       // DECIMAL
        $tmb,               // DECIMAL
        $tdee,              // DECIMAL
        $calorias_deficit,  // DECIMAL
        $calorias_mantenimiento,  // DECIMAL
        $calorias_volumen   // DECIMAL
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al guardar: " . $stmt->error);
    }

    // Respuesta JSON
    echo json_encode([
        'success' => true,
        'tmb' => round($tmb, 2),
        'tdee' => round($tdee, 2),
        'deficit' => round($calorias_deficit, 2),
        'mantenimiento' => round($calorias_mantenimiento, 2),
        'volumen' => round($calorias_volumen, 2),
        'factor_actividad' => round($factor_actividad, 2)
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
