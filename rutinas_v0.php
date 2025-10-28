<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

// Conexión a base de datos
require_once 'connection.php';

// Obtener rutina activa
$sql_rutina = "SELECT * FROM rutinas WHERE activa = TRUE LIMIT 1";
$resultado_rutina = $conn->query($sql_rutina);
$rutina = $resultado_rutina->fetch_assoc();

if (!$rutina) {
    die("No hay rutina activa. Ejecuta el script rutinas_db.sql primero.");
}

// Obtener días de entrenamiento
$sql_dias = "SELECT * FROM dias_entrenamiento WHERE rutina_id = ? ORDER BY dia_semana";
$stmt = $conn->prepare($sql_dias);
$stmt->bind_param("i", $rutina['id']);
$stmt->execute();
$dias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener último entrenamiento
$sql_ultimo = "SELECT r.fecha, e.nombre as ejercicio, d.nombre as dia
               FROM registros_entrenamiento r
               JOIN ejercicios e ON r.ejercicio_id = e.id
               JOIN dias_entrenamiento d ON e.dia_id = d.id
               WHERE r.nombre = ? AND r.apellidos = ?
               ORDER BY r.fecha DESC, r.created_at DESC
               LIMIT 1";
$stmt = $conn->prepare($sql_ultimo);
$stmt->bind_param("ss", $nombre, $apellidos);
$stmt->execute();
$ultimo_entrenamiento = $stmt->get_result()->fetch_assoc();

// Obtener días de la semana actual con entrenamientos
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

// Obtener fechas en las que entrenó esta semana
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

// Convertir a array simple de fechas
$fechas_array = array_column($fechas_entrenadas, 'fecha');

// Crear array de los 7 días de la semana (con abreviaturas personalizadas)
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Rutinas - Gym Tracker</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">

    <style>
        .dia-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            height: 100%;
            border: none;
            overflow: hidden;
        }
        .dia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .dia-descanso {
            background: linear-gradient(135deg, #e0e0e0 0%, #bdbdbd 100%);
            border: none;
        }
        .dia-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .gradient-push {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-pull {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .gradient-legs {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .gradient-torso {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .progress-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 50px;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .progress-badge.completed {
            background: #10b981;
            color: white;
        }
        .progress-badge.partial {
            background: #f59e0b;
            color: white;
        }
        @media (max-width: 768px) {
            .dia-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index_v0_design.php" class="navbar-brand-modern">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <span style="color: #64748b; margin-right: 1rem;">👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></span>
            <a href="index.php" title="Calculadora">🧮</a>
            <a href="reverse_diet_v0.php" title="Reverse Diet">🔄</a>
            <a href="rutinas_v0.php" title="Rutinas" style="color: #6366f1;">🏋️</a>
            <a href="analisis_progreso.php" title="Análisis de Progreso">📊</a>
            <a href="introducir_peso_v0.php" title="Registrar Peso">⚖️</a>
            <a href="grafica_v0.php" title="Progreso Peso">📈</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Header -->
        <div class="v0-card">
            <div class="v0-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="dumbbell" style="color: #6366f1; width: 28px; height: 28px;"></i>
                    <div>
                        <h3><?php echo htmlspecialchars($rutina['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($rutina['descripcion']); ?></p>
                    </div>
                </div>
                <a href="gestionar_ejercicios_v0.php" class="v0-btn v0-btn-secondary" style="margin-top: 0.5rem;">
                    <i data-lucide="settings" style="width: 18px; height: 18px;"></i>
                    Gestionar Ejercicios
                </a>
            </div>
            <div class="v0-card-body">
                <div class="grid-2" style="gap: 2rem;">

                    <!-- Último Entrenamiento -->
                    <div>
                        <h5 style="color: #1e293b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="calendar" style="width: 20px; height: 20px; color: #6366f1;"></i>
                            Último Entrenamiento
                        </h5>
                        <?php if ($ultimo_entrenamiento): ?>
                            <div style="padding: 1rem; background: #f8fafc; border-radius: 12px; border-left: 4px solid #6366f1;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem;">
                                    <?php echo date('d/m/Y', strtotime($ultimo_entrenamiento['fecha'])); ?>
                                </div>
                                <div style="color: #64748b;">
                                    <?php echo htmlspecialchars($ultimo_entrenamiento['dia']); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="padding: 1.5rem; text-align: center; background: #f8fafc; border-radius: 12px; color: #94a3b8;">
                                <i data-lucide="inbox" style="width: 32px; height: 32px; margin-bottom: 0.5rem; display: inline-block;"></i>
                                <p style="margin: 0;">Sin registros aún</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Progreso de esta semana -->
                    <div>
                        <h5 style="color: #1e293b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="bar-chart-2" style="width: 20px; height: 20px; color: #6366f1;"></i>
                            Progreso de esta semana
                        </h5>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <?php foreach ($progreso_semana as $dia_progreso): ?>
                                <?php
                                $entreno = $dia_progreso['entreno'];
                                $es_hoy = $dia_progreso['fecha'] == date('Y-m-d');
                                $dia_abrev = $dia_progreso['dia_abrev'];
                                ?>
                                <div style="text-align: center; flex: 1; min-width: 70px; padding: 0.75rem; background: <?php echo $es_hoy ? '#eef2ff' : '#f8fafc'; ?>; border-radius: 12px; border: <?php echo $es_hoy ? '2px solid #6366f1' : '1px solid #e2e8f0'; ?>;">
                                    <div style="margin-bottom: 0.5rem;">
                                        <?php if ($entreno): ?>
                                            <span class="progress-badge completed">
                                                <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="progress-badge partial">0%</span>
                                        <?php endif; ?>
                                    </div>
                                    <small style="color: <?php echo $es_hoy ? '#6366f1' : '#64748b'; ?>; font-weight: <?php echo $es_hoy ? '700' : '600'; ?>; font-size: 0.7rem;">
                                        <?php echo $dia_abrev; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Días de la semana -->
        <div class="grid-4 mt-3">
            <?php
            // Iconos para cada tipo de día
            $iconos = [
                'PUSH' => '💪',
                'PULL' => '🏋️‍♂️',
                'LEGS' => '🦵',
                'TORSO' => '🏋️',
                'DESCANSO' => '😴'
            ];

            $gradientes = [
                'PUSH' => 'gradient-push',
                'PULL' => 'gradient-pull',
                'LEGS' => 'gradient-legs',
                'TORSO' => 'gradient-torso'
            ];

            foreach ($dias as $dia):
                $tipo_upper = strtoupper($dia['tipo']);
                $icono_dia = $iconos[$tipo_upper] ?? '🏋️';
            ?>
                <?php if ($dia['es_descanso']): ?>
                    <!-- Día de descanso -->
                    <div class="v0-card dia-card dia-descanso" style="text-align: center; padding: 2rem 1rem;">
                        <div class="dia-icon">😴</div>
                        <h5 style="color: white; margin-bottom: 0.25rem; font-size: 1rem;"><?php echo htmlspecialchars($dia['nombre']); ?></h5>
                        <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 0.875rem;">Descanso</p>
                    </div>
                <?php else: ?>
                    <!-- Día de entrenamiento -->
                    <div class="v0-card dia-card" onclick="location.href='dia_entrenamiento_v0.php?dia_id=<?php echo $dia['id']; ?>'" style="overflow: hidden; padding: 0;">
                        <div class="<?php echo $gradientes[$tipo_upper] ?? 'gradient-push'; ?>" style="padding: 1.5rem 1rem; text-align: center;">
                            <div class="dia-icon"><?php echo $icono_dia; ?></div>
                            <h5 style="color: white; margin-bottom: 0.5rem; font-size: 1.125rem; font-weight: 700;"><?php echo strtoupper($dia['tipo']); ?></h5>
                        </div>
                        <div style="padding: 1rem;">
                            <div style="margin-bottom: 0.75rem; text-align: center;">
                                <small style="color: #64748b; font-weight: 600;">
                                    <?php
                                    // Contar ejercicios
                                    $sql_count = "SELECT COUNT(*) as total FROM ejercicios WHERE dia_id = ?";
                                    $stmt_count = $conn->prepare($sql_count);
                                    if ($stmt_count) {
                                        $stmt_count->bind_param("i", $dia['id']);
                                        $stmt_count->execute();
                                        $count = $stmt_count->get_result()->fetch_assoc()['total'];
                                        $stmt_count->close();
                                        echo $count . " ejercicios";
                                    } else {
                                        echo "0 ejercicios";
                                    }
                                    ?>
                                </small>
                            </div>
                            <?php
                            // Obtener última vez entrenado
                            $sql_ultimo_dia = "SELECT MAX(fecha) as ultima_fecha
                                              FROM registros_entrenamiento r
                                              JOIN ejercicios e ON r.ejercicio_id = e.id
                                              WHERE e.dia_id = ? AND r.nombre = ? AND r.apellidos = ?";
                            $stmt_ultimo = $conn->prepare($sql_ultimo_dia);
                            if ($stmt_ultimo) {
                                $stmt_ultimo->bind_param("iss", $dia['id'], $nombre, $apellidos);
                                $stmt_ultimo->execute();
                                $ultima = $stmt_ultimo->get_result()->fetch_assoc();
                                $stmt_ultimo->close();

                                if ($ultima && $ultima['ultima_fecha']) {
                                    $fecha_obj = new DateTime($ultima['ultima_fecha']);
                                    $hoy = new DateTime();
                                    $diff = $hoy->diff($fecha_obj);

                                    if ($diff->days == 0) {
                                        $texto_fecha = "Hoy";
                                        $color = "#10b981";
                                    } elseif ($diff->days == 1) {
                                        $texto_fecha = "Ayer";
                                        $color = "#3b82f6";
                                    } elseif ($diff->days < 7) {
                                        $texto_fecha = "Hace " . $diff->days . " días";
                                        $color = "#64748b";
                                    } else {
                                        $texto_fecha = $fecha_obj->format('d/m/Y');
                                        $color = "#94a3b8";
                                    }
                                    echo "<div style='text-align: center; margin-bottom: 0.75rem;'>";
                                    echo "<small style='color: $color; font-weight: 500;'>📅 $texto_fecha</small>";
                                    echo "</div>";
                                }
                            }
                            ?>
                            <button class="v0-btn v0-btn-dark" style="width: 100%;">
                                Entrenar
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    </div>

    <?php $conn->close(); ?>

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
