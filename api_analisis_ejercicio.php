<?php
session_start();
header('Content-Type: application/json');

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

// Conexión a base de datos
require_once 'connection.php';

$ejercicio_id = isset($_GET['ejercicio_id']) ? intval($_GET['ejercicio_id']) : 0;

if (!$ejercicio_id) {
    echo json_encode(['success' => false, 'error' => 'ID de ejercicio requerido']);
    exit;
}

try {
    // Obtener todo el historial del ejercicio (últimas 30 sesiones o 3 meses)
    $sql = "SELECT r.fecha, r.set_numero, r.peso, r.reps, r.rpe
            FROM registros_entrenamiento r
            WHERE r.ejercicio_id = ? AND r.nombre = ? AND r.apellidos = ?
            AND r.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            ORDER BY r.fecha DESC, r.set_numero ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $ejercicio_id, $nombre, $apellidos);
    $stmt->execute();
    $result = $stmt->get_result();

    $historico = [];
    while ($row = $result->fetch_assoc()) {
        $historico[] = [
            'fecha' => $row['fecha'],
            'set_numero' => intval($row['set_numero']),
            'peso' => floatval($row['peso']),
            'reps' => intval($row['reps']),
            'rpe' => $row['rpe'] ? floatval($row['rpe']) : null
        ];
    }

    $stmt->close();

    // Detectar si es ejercicio con peso o bodyweight
    $usa_peso = false;
    foreach ($historico as $reg) {
        if ($reg['peso'] > 0) {
            $usa_peso = true;
            break;
        }
    }

    // Agrupar por sesión (fecha)
    $por_sesion = [];
    foreach ($historico as $reg) {
        $fecha = $reg['fecha'];
        if (!isset($por_sesion[$fecha])) {
            $por_sesion[$fecha] = [
                'fecha' => $fecha,
                'registros' => []
            ];
        }
        $por_sesion[$fecha]['registros'][] = $reg;
    }

    // Convertir a array indexado
    $por_sesion = array_values($por_sesion);

    // Calcular estadísticas por mesociclo (cada 4 semanas)
    $mesociclos = calcularMesociclos($historico, $usa_peso);

    $conn->close();

    echo json_encode([
        'success' => true,
        'datos' => [
            'historico' => $historico,
            'por_sesion' => $por_sesion,
            'mesociclos' => $mesociclos,
            'usa_peso' => $usa_peso
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function calcularMesociclos($historico, $usa_peso = true) {
    if (empty($historico)) {
        return [];
    }

    // Agrupar por fecha
    $por_fecha = [];
    foreach ($historico as $reg) {
        $fecha = $reg['fecha'];
        if (!isset($por_fecha[$fecha])) {
            $por_fecha[$fecha] = [];
        }
        $por_fecha[$fecha][] = $reg;
    }

    // Ordenar fechas
    $fechas = array_keys($por_fecha);
    sort($fechas);

    // Dividir en períodos de 4 semanas
    $mesociclos = [];
    $fecha_inicio = null;
    $sesiones_mesociclo = [];

    foreach ($fechas as $fecha) {
        if ($fecha_inicio === null) {
            $fecha_inicio = $fecha;
        }

        // Calcular diferencia en días
        $diff = (strtotime($fecha) - strtotime($fecha_inicio)) / (60 * 60 * 24);

        if ($diff > 28) { // 4 semanas = 28 días
            // Guardar mesociclo actual
            if (!empty($sesiones_mesociclo)) {
                $mesociclos[] = calcularEstadisticasMesociclo($sesiones_mesociclo, $fecha_inicio, $fechas[count($sesiones_mesociclo) - 1]);
            }

            // Iniciar nuevo mesociclo
            $fecha_inicio = $fecha;
            $sesiones_mesociclo = [];
        }

        $sesiones_mesociclo[] = $por_fecha[$fecha];
    }

    // Agregar último mesociclo
    if (!empty($sesiones_mesociclo)) {
        $mesociclos[] = calcularEstadisticasMesociclo($sesiones_mesociclo, $fecha_inicio, end($fechas));
    }

    return $mesociclos;
}

function calcularEstadisticasMesociclo($sesiones, $fecha_inicio, $fecha_fin) {
    $todos_registros = [];
    foreach ($sesiones as $sesion) {
        $todos_registros = array_merge($todos_registros, $sesion);
    }

    $peso_max = max(array_map(function($r) { return $r['peso']; }, $todos_registros));
    $reps_max = max(array_map(function($r) { return $r['reps']; }, $todos_registros));
    $volumen_total = array_reduce($todos_registros, function($sum, $r) {
        return $sum + ($r['peso'] * $r['reps']);
    }, 0);

    return [
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'sesiones' => count($sesiones),
        'peso_max' => $peso_max,
        'reps_max' => $reps_max,
        'volumen_total' => $volumen_total,
        'volumen_promedio' => $volumen_total / count($sesiones)
    ];
}
?>
