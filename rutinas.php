<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

require_once 'connection.php';

// Obtener rutina activa
$sql_rutina = "SELECT * FROM rutinas WHERE activa = TRUE LIMIT 1";
$resultado_rutina = $conn->query($sql_rutina);
$rutina = $resultado_rutina->fetch_assoc();

if (!$rutina) {
    die("No hay rutina activa.");
}

// Obtener días de entrenamiento
$sql_dias = "SELECT * FROM dias_entrenamiento WHERE rutina_id = ? ORDER BY dia_semana";
$stmt = $conn->prepare($sql_dias);
$stmt->bind_param("i", $rutina['id']);
$stmt->execute();
$dias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener días entrenados esta semana
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

$sql_fechas = "SELECT DISTINCT DATE(r.fecha) as fecha
               FROM registros_entrenamiento r
               WHERE r.nombre = ? AND r.apellidos = ?
               AND r.fecha BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_fechas);
$stmt->bind_param("ssss", $nombre, $apellidos, $inicio_semana, $fin_semana);
$stmt->execute();
$fechas_entrenadas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$fechas_array = array_column($fechas_entrenadas, 'fecha');

// Días de la semana
$dias_semana_info = [
    ['abrev' => 'L'],
    ['abrev' => 'M'],
    ['abrev' => 'X'],
    ['abrev' => 'J'],
    ['abrev' => 'V'],
    ['abrev' => 'S'],
    ['abrev' => 'D']
];
$progreso_semana = [];

for ($i = 0; $i < 7; $i++) {
    $fecha_dia = date('Y-m-d', strtotime($inicio_semana . " +$i days"));
    $entreno = in_array($fecha_dia, $fechas_array);
    $es_hoy = $fecha_dia == date('Y-m-d');

    $progreso_semana[] = [
        'abrev' => $dias_semana_info[$i]['abrev'],
        'fecha' => $fecha_dia,
        'entreno' => $entreno,
        'es_hoy' => $es_hoy
    ];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutinas - GYM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #fafafa;
            color: #1a1a1a;
            padding-bottom: 80px;
        }

        /* Top Nav - Desktop */
        .top-nav {
            display: none;
            background: white;
            border-bottom: 1px solid #e5e5e5;
            padding: 0 2rem;
            height: 60px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-nav-links {
            display: flex;
            gap: 2rem;
        }

        .top-nav a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .top-nav a:hover,
        .top-nav a.active {
            color: #1a1a1a;
        }

        /* Bottom Nav - Mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            z-index: 100;
        }

        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #999;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .bottom-nav a.active {
            color: #1a1a1a;
        }

        .bottom-nav-icon {
            font-size: 20px;
        }

        /* Header */
        .header {
            padding: 2rem 1rem 1rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            color: #666;
            font-size: 14px;
        }

        /* Week Progress */
        .week-progress {
            display: flex;
            gap: 8px;
            margin: 2rem 0;
            padding: 0 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .day-dot {
            flex: 1;
            height: 8px;
            background: #e5e5e5;
            position: relative;
        }

        .day-dot.active {
            background: #1a1a1a;
        }

        .day-dot.today::after {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #1a1a1a;
            background: white;
            border-radius: 50%;
        }

        /* Workouts */
        .workouts {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .workout-item {
            background: white;
            border: 1px solid #e5e5e5;
            margin-bottom: 1rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .workout-item:hover {
            border-color: #1a1a1a;
        }

        .workout-item:active {
            transform: scale(0.99);
        }

        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .workout-type {
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .workout-arrow {
            color: #ccc;
            font-size: 20px;
        }

        .workout-meta {
            display: flex;
            gap: 1rem;
            font-size: 13px;
            color: #666;
        }

        .workout-badge {
            background: #f5f5f5;
            padding: 2px 8px;
            font-size: 11px;
            color: #666;
        }

        /* Desktop styles */
        @media (min-width: 768px) {
            body {
                padding-bottom: 2rem;
                padding-top: 60px;
            }

            .top-nav {
                display: flex;
            }

            .bottom-nav {
                display: none;
            }

            .week-progress {
                margin: 3rem auto 2rem;
            }

            .workout-item {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation - Desktop -->
    <nav class="top-nav">
        <div class="top-nav-links">
            <a href="dashboard.php">← Dashboard</a>
            <a href="gym_hub.php">GYM Hub</a>
            <a href="rutinas.php" class="active">Rutinas</a>
            <a href="analisis_progreso.php">Progreso</a>
            <a href="gestionar_ejercicios.php">Ejercicios</a>
            <a href="volumen_semanal.php">Volumen</a>
        </div>
        <a href="logout.php" style="color: #999;">Salir</a>
    </nav>

    <!-- Header -->
    <div class="header">
        <h1>Rutinas</h1>
        <p class="header-subtitle">
            <?php
            $dias_nombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $dia_actual = $dias_nombres[date('w')];
            $fecha_actual = date('d/m/Y');
            echo "$dia_actual, $fecha_actual";
            ?>
        </p>
    </div>

    <!-- Week Progress -->
    <div class="week-progress">
        <?php foreach ($progreso_semana as $dia): ?>
            <div class="day-dot <?php echo $dia['entreno'] ? 'active' : ''; ?> <?php echo $dia['es_hoy'] ? 'today' : ''; ?>"></div>
        <?php endforeach; ?>
    </div>

    <!-- Workouts -->
    <div class="workouts">
        <?php foreach ($dias as $dia): ?>
            <?php if ($dia['es_descanso']) continue; ?>
            <?php
            // Obtener info del día
            $conn = new mysqli("localhost", "root", "", "calculadora_calorias");

            $sql_ultimo = "SELECT MAX(fecha) as ultima_fecha
                          FROM registros_entrenamiento r
                          JOIN ejercicios e ON r.ejercicio_id = e.id
                          WHERE e.dia_id = ? AND r.nombre = ? AND r.apellidos = ?";
            $stmt = $conn->prepare($sql_ultimo);
            $stmt->bind_param("iss", $dia['id'], $nombre, $apellidos);
            $stmt->execute();
            $ultima = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $texto_fecha = '';
            if ($ultima && $ultima['ultima_fecha']) {
                $diff = (strtotime(date('Y-m-d')) - strtotime($ultima['ultima_fecha'])) / 86400;
                if ($diff == 0) $texto_fecha = 'Hoy';
                elseif ($diff == 1) $texto_fecha = 'Ayer';
                elseif ($diff < 7) $texto_fecha = 'Hace ' . $diff . 'd';
                else $texto_fecha = date('d/m', strtotime($ultima['ultima_fecha']));
            }

            $sql_count = "SELECT COUNT(*) as total FROM ejercicios WHERE dia_id = ?";
            $stmt = $conn->prepare($sql_count);
            $stmt->bind_param("i", $dia['id']);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();
            $conn->close();
            ?>
            <div class="workout-item" onclick="location.href='dia_entrenamiento.php?dia_id=<?php echo $dia['id']; ?>'">
                <div class="workout-header">
                    <div class="workout-type"><?php echo htmlspecialchars($dia['tipo']); ?></div>
                    <div class="workout-arrow">→</div>
                </div>
                <div class="workout-meta">
                    <span><?php echo $count; ?> ejercicios</span>
                    <?php if ($texto_fecha): ?>
                        <span class="workout-badge"><?php echo $texto_fecha; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Navigation - Mobile -->
    <nav class="bottom-nav">
        <a href="dashboard.php">
            <div>Inicio</div>
        </a>
        <a href="gym_hub.php">
            <div>GYM</div>
        </a>
        <a href="rutinas.php" class="active">
            <div>Rutinas</div>
        </a>
        <a href="analisis_progreso.php">
            <div>Progreso</div>
        </a>
    </nav>

</body>
</html>
