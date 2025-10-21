<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de base de datos
$host = 'localhost';
$dbname = 'calculadora_calorias';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a base de datos']);
    exit;
}

// Obtener o crear session_id
session_start();
if (!isset($_SESSION['calculadora_id'])) {
    $_SESSION['calculadora_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['calculadora_id'];

// Procesar la petición
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        if ($action === 'guardar_peso') {
            guardarPeso($pdo, $session_id);
        } elseif ($action === 'guardar_plan') {
            guardarPlan($pdo, $session_id);
        }
        break;

    case 'GET':
        if ($action === 'obtener_pesos') {
            obtenerPesos($pdo, $session_id);
        } elseif ($action === 'obtener_plan_activo') {
            obtenerPlanActivo($pdo, $session_id);
        }
        break;

    case 'PUT':
        if ($action === 'actualizar_peso') {
            actualizarPeso($pdo, $session_id);
        }
        break;

    case 'DELETE':
        if ($action === 'eliminar_peso') {
            eliminarPeso($pdo, $session_id);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}

// Función para guardar peso diario
function guardarPeso($pdo, $session_id) {
    $data = json_decode(file_get_contents('php://input'), true);

    $peso = $data['peso'] ?? null;
    $fecha = $data['fecha'] ?? date('Y-m-d');
    $notas = $data['notas'] ?? null;
    $plan_id = $data['plan_id'] ?? null;

    if (!$peso) {
        http_response_code(400);
        echo json_encode(['error' => 'Peso es requerido']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO peso_diario (usuario_session, plan_id, peso, fecha, notas)
            VALUES (:session_id, :plan_id, :peso, :fecha, :notas)
            ON DUPLICATE KEY UPDATE
                peso = :peso,
                notas = :notas,
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ':session_id' => $session_id,
            ':plan_id' => $plan_id,
            ':peso' => $peso,
            ':fecha' => $fecha,
            ':notas' => $notas
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Peso guardado correctamente',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar peso: ' . $e->getMessage()]);
    }
}

// Función para obtener pesos
function obtenerPesos($pdo, $session_id) {
    $dias = $_GET['dias'] ?? 30; // Por defecto últimos 30 días

    try {
        $stmt = $pdo->prepare("
            SELECT id, peso, fecha, notas, created_at
            FROM peso_diario
            WHERE usuario_session = :session_id
            AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
            ORDER BY fecha ASC
        ");

        $stmt->execute([
            ':session_id' => $session_id,
            ':dias' => $dias
        ]);

        $pesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $pesos
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener pesos: ' . $e->getMessage()]);
    }
}

// Función para guardar plan
function guardarPlan($pdo, $session_id) {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO planes_nutricionales (
                edad, sexo, peso, altura,
                dias_entreno, horas_gym, dias_cardio, horas_cardio,
                tipo_trabajo, horas_trabajo, horas_sueno,
                objetivo, kg_objetivo, velocidad, nivel_gym,
                tmb, tdee, calorias_plan,
                duracion_semanas, duracion_meses,
                proteina_gramos, grasa_gramos, carbohidratos_gramos,
                plan_json
            ) VALUES (
                :edad, :sexo, :peso, :altura,
                :dias_entreno, :horas_gym, :dias_cardio, :horas_cardio,
                :tipo_trabajo, :horas_trabajo, :horas_sueno,
                :objetivo, :kg_objetivo, :velocidad, :nivel_gym,
                :tmb, :tdee, :calorias_plan,
                :duracion_semanas, :duracion_meses,
                :proteina_gramos, :grasa_gramos, :carbohidratos_gramos,
                :plan_json
            )
        ");

        $plan = $data['plan'] ?? [];
        $datosPersonales = $data['datosPersonales'] ?? [];

        $stmt->execute([
            ':edad' => $datosPersonales['edad'] ?? 0,
            ':sexo' => $datosPersonales['sexo'] ?? 'hombre',
            ':peso' => $datosPersonales['peso'] ?? 0,
            ':altura' => $datosPersonales['altura'] ?? 0,
            ':dias_entreno' => $datosPersonales['diasEntreno'] ?? 0,
            ':horas_gym' => $datosPersonales['horasGym'] ?? 0,
            ':dias_cardio' => $datosPersonales['diasCardio'] ?? 0,
            ':horas_cardio' => $datosPersonales['horasCardio'] ?? 0,
            ':tipo_trabajo' => $datosPersonales['tipoTrabajo'] ?? 'sedentario',
            ':horas_trabajo' => $datosPersonales['horasTrabajo'] ?? 0,
            ':horas_sueno' => $datosPersonales['horasSueno'] ?? 0,
            ':objetivo' => $plan['tipo'] ?? 'mantenimiento',
            ':kg_objetivo' => $plan['kgObjetivo'] ?? 0,
            ':velocidad' => $datosPersonales['velocidad'] ?? null,
            ':nivel_gym' => $datosPersonales['nivelGym'] ?? null,
            ':tmb' => $data['tmb'] ?? 0,
            ':tdee' => $data['tdee'] ?? 0,
            ':calorias_plan' => $plan['calorias'] ?? $data['tdee'] ?? 0,
            ':duracion_semanas' => $plan['duracion']['semanas'] ?? null,
            ':duracion_meses' => $plan['duracion']['meses'] ?? null,
            ':proteina_gramos' => $plan['macros']['proteina'] ?? 0,
            ':grasa_gramos' => $plan['macros']['grasa'] ?? 0,
            ':carbohidratos_gramos' => $plan['macros']['carbohidratos'] ?? 0,
            ':plan_json' => json_encode($data)
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Plan guardado correctamente',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar plan: ' . $e->getMessage()]);
    }
}

// Función para obtener plan activo
function obtenerPlanActivo($pdo, $session_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM planes_nutricionales
            ORDER BY fecha_calculo DESC
            LIMIT 1
        ");

        $stmt->execute();
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plan) {
            $plan['plan_json'] = json_decode($plan['plan_json'], true);
        }

        echo json_encode([
            'success' => true,
            'data' => $plan
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener plan: ' . $e->getMessage()]);
    }
}

// Función para actualizar peso
function actualizarPeso($pdo, $session_id) {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? null;
    $peso = $data['peso'] ?? null;
    $notas = $data['notas'] ?? null;

    if (!$id || !$peso) {
        http_response_code(400);
        echo json_encode(['error' => 'ID y peso son requeridos']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE peso_diario
            SET peso = :peso, notas = :notas, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND usuario_session = :session_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':peso' => $peso,
            ':notas' => $notas,
            ':session_id' => $session_id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Peso actualizado correctamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar peso: ' . $e->getMessage()]);
    }
}

// Función para eliminar peso
function eliminarPeso($pdo, $session_id) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID es requerido']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            DELETE FROM peso_diario
            WHERE id = :id AND usuario_session = :session_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':session_id' => $session_id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Peso eliminado correctamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar peso: ' . $e->getMessage()]);
    }
}
