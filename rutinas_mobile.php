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

// Obtener días de la semana actual con entrenamientos
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

$sql_fechas = "SELECT DISTINCT DATE(r.fecha) as fecha
               FROM registros_entrenamiento r
               WHERE r.nombre = ? AND r.apellidos = ?
               AND r.fecha BETWEEN ? AND ?
               ORDER BY r.fecha";
$stmt = $conn->prepare($sql_fechas);
$stmt->bind_param("ssss", $nombre, $apellidos, $inicio_semana, $fin_semana);
$stmt->execute();
$fechas_entrenadas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$fechas_array = array_column($fechas_entrenadas, 'fecha');

// Crear array de los 7 días
$dias_semana = [
    ['nombre' => 'Lunes', 'abrev' => 'LUN'],
    ['nombre' => 'Martes', 'abrev' => 'MAR'],
    ['nombre' => 'Miércoles', 'abrev' => 'MIÉ'],
    ['nombre' => 'Jueves', 'abrev' => 'JUE'],
    ['nombre' => 'Viernes', 'abrev' => 'VIE'],
    ['nombre' => 'Sábado', 'abrev' => 'SÁB'],
    ['nombre' => 'Domingo', 'abrev' => 'DOM']
];
$progreso_semana = [];

for ($i = 0; $i < 7; $i++) {
    $fecha_dia = date('Y-m-d', strtotime($inicio_semana . " +$i days"));
    $entreno = in_array($fecha_dia, $fechas_array);

    $progreso_semana[] = [
        'dia' => $dias_semana[$i]['nombre'],
        'dia_abrev' => $dias_semana[$i]['abrev'],
        'fecha' => $fecha_dia,
        'entreno' => $entreno
    ];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Rutinas - GYM</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-card: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-push: #f43f5e;
            --accent-pull: #8b5cf6;
            --accent-legs: #06b6d4;
            --accent-torso: #f59e0b;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding-bottom: 2rem;
        }

        .header {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 1.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .workout-card {
            background: var(--bg-card);
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .workout-card:active {
            transform: scale(0.98);
        }

        .workout-card.push {
            border-top: 4px solid var(--accent-push);
        }

        .workout-card.pull {
            border-top: 4px solid var(--accent-pull);
        }

        .workout-card.legs {
            border-top: 4px solid var(--accent-legs);
        }

        .workout-card.torso {
            border-top: 4px solid var(--accent-torso);
        }

        .day-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            height: 48px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.75rem;
            background: #334155;
            color: var(--text-secondary);
            transition: all 0.3s;
        }

        .day-badge.active {
            background: #10b981;
            color: white;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }

        .day-badge.today {
            border: 2px solid #6366f1;
            background: #312e81;
            color: #a5b4fc;
        }

        .floating-btn {
            position: fixed;
            bottom: 2rem;
            right: 1rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 100;
        }

        .floating-btn:active {
            transform: scale(0.9);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .workout-card {
            animation: slideUp 0.4s ease-out;
        }

        .workout-card:nth-child(1) { animation-delay: 0.1s; }
        .workout-card:nth-child(2) { animation-delay: 0.2s; }
        .workout-card:nth-child(3) { animation-delay: 0.3s; }
        .workout-card:nth-child(4) { animation-delay: 0.4s; }

        .last-workout-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 8px;
            font-size: 0.75rem;
            color: #10b981;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <a href="gym_hub.php" style="color: var(--text-secondary); text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
                <span style="font-weight: 600;">Volver</span>
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0;">
                💪 MIS RUTINAS
            </h1>
            <div style="width: 70px;"></div>
        </div>

        <!-- Progreso semanal -->
        <div>
            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                Esta Semana
            </p>
            <div style="display: flex; gap: 0.5rem; overflow-x: auto;">
                <?php foreach ($progreso_semana as $dia): ?>
                    <?php
                    $es_hoy = $dia['fecha'] == date('Y-m-d');
                    $entreno = $dia['entreno'];
                    $clase = $entreno ? 'active' : '';
                    if ($es_hoy) $clase .= ' today';
                    ?>
                    <div class="day-badge <?php echo $clase; ?>">
                        <?php if ($entreno): ?>
                            ✓
                        <?php else: ?>
                            <?php echo $dia['dia_abrev']; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Workouts -->
    <div style="padding: 1.5rem 1rem;">
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($dias as $dia): ?>
                <?php if ($dia['es_descanso']) continue; ?>
                <?php
                $tipo = strtolower($dia['tipo']);
                $iconos = [
                    'push' => '💪',
                    'pull' => '🏋️',
                    'legs' => '🦵',
                    'torso' => '🔥'
                ];
                $icono = $iconos[$tipo] ?? '🏋️';

                // Obtener última vez entrenado
                $conn = new mysqli("localhost", "root", "", "calculadora_calorias");
                $sql_ultimo = "SELECT MAX(fecha) as ultima_fecha
                              FROM registros_entrenamiento r
                              JOIN ejercicios e ON r.ejercicio_id = e.id
                              WHERE e.dia_id = ? AND r.nombre = ? AND r.apellidos = ?";
                $stmt_u = $conn->prepare($sql_ultimo);
                $stmt_u->bind_param("iss", $dia['id'], $nombre, $apellidos);
                $stmt_u->execute();
                $ultima = $stmt_u->get_result()->fetch_assoc();
                $stmt_u->close();

                $texto_fecha = '';
                if ($ultima && $ultima['ultima_fecha']) {
                    $diff_days = (strtotime(date('Y-m-d')) - strtotime($ultima['ultima_fecha'])) / (60 * 60 * 24);
                    if ($diff_days == 0) {
                        $texto_fecha = 'Hoy';
                    } elseif ($diff_days == 1) {
                        $texto_fecha = 'Ayer';
                    } elseif ($diff_days < 7) {
                        $texto_fecha = 'Hace ' . $diff_days . ' días';
                    } else {
                        $texto_fecha = date('d/m/Y', strtotime($ultima['ultima_fecha']));
                    }
                }

                // Contar ejercicios
                $sql_count = "SELECT COUNT(*) as total FROM ejercicios WHERE dia_id = ?";
                $stmt_c = $conn->prepare($sql_count);
                $stmt_c->bind_param("i", $dia['id']);
                $stmt_c->execute();
                $count = $stmt_c->get_result()->fetch_assoc()['total'];
                $stmt_c->close();
                $conn->close();
                ?>
                <div class="workout-card <?php echo $tipo; ?>" onclick="location.href='dia_entrenamiento_v0.php?dia_id=<?php echo $dia['id']; ?>'">
                    <div style="padding: 1.5rem;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <span style="font-size: 2rem;"><?php echo $icono; ?></span>
                                    <h3 style="font-size: 1.5rem; font-weight: 800; margin: 0; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($dia['tipo']); ?>
                                    </h3>
                                </div>
                                <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                                    <?php echo $count; ?> ejercicios
                                </p>
                                <?php if ($texto_fecha): ?>
                                    <div class="last-workout-badge">
                                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                        <?php echo $texto_fecha; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <i data-lucide="chevron-right" style="width: 28px; height: 28px; color: var(--text-secondary);"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Botón flotante para análisis -->
    <div class="floating-btn" onclick="location.href='analisis_progreso.php'">
        <i data-lucide="bar-chart-3" style="width: 28px; height: 28px; color: white;"></i>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
