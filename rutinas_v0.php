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
require_once 'config.php';

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

// Obtener resumen semanal (entrenamientos de esta semana)
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

$sql_semana = "SELECT d.nombre as dia, COUNT(DISTINCT r.ejercicio_id) as ejercicios_completados,
               (SELECT COUNT(*) FROM ejercicios WHERE dia_id = d.id) as total_ejercicios
               FROM dias_entrenamiento d
               LEFT JOIN ejercicios e ON e.dia_id = d.id
               LEFT JOIN registros_entrenamiento r ON r.ejercicio_id = e.id
                   AND r.nombre = ? AND r.apellidos = ?
                   AND r.fecha BETWEEN ? AND ?
               WHERE d.rutina_id = ? AND d.es_descanso = FALSE
               GROUP BY d.id, d.nombre, d.dia_semana
               ORDER BY d.dia_semana";
$stmt = $conn->prepare($sql_semana);
$stmt->bind_param("ssssi", $nombre, $apellidos, $inicio_semana, $fin_semana, $rutina['id']);
$stmt->execute();
$progreso_semana = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
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
            <a href="index_v0_design.php" title="Calculadora">🧮</a>
            <a href="reverse_diet_v0.php" title="Reverse Diet">🔄</a>
            <a href="rutinas_v0.php" title="Rutinas" style="color: #6366f1;">🏋️</a>
            <a href="introducir_peso_v0.php" title="Registrar Peso">⚖️</a>
            <a href="grafica_v0.php" title="Progreso">📊</a>
            <a href="seguimiento_v0.php" title="Ajuste de Calorías">📈</a>
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
                                $porcentaje = $dia_progreso['total_ejercicios'] > 0
                                    ? ($dia_progreso['ejercicios_completados'] / $dia_progreso['total_ejercicios']) * 100
                                    : 0;
                                $completado = $porcentaje >= 100;
                                ?>
                                <div style="text-align: center; flex: 1; min-width: 80px; padding: 0.75rem; background: #f8fafc; border-radius: 12px;">
                                    <div style="margin-bottom: 0.5rem;">
                                        <?php if ($completado): ?>
                                            <span class="progress-badge completed">
                                                <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="progress-badge partial"><?php echo round($porcentaje); ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <small style="color: #64748b; font-weight: 600;"><?php echo explode(' - ', $dia_progreso['dia'])[0]; ?></small>
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
                            <h5 style="color: white; margin-bottom: 0.5rem; font-size: 1rem;"><?php echo htmlspecialchars($dia['nombre']); ?></h5>
                            <span style="background: rgba(255,255,255,0.25); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($dia['tipo']); ?>
                            </span>
                        </div>
                        <div style="padding: 1rem; text-align: center;">
                            <div style="margin-bottom: 0.75rem;">
                                <small style="color: #64748b; font-weight: 600;">
                                    <?php
                                    // Contar ejercicios
                                    $sql_count = "SELECT COUNT(*) as total FROM ejercicios WHERE dia_id = ?";
                                    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                    $stmt = $conn->prepare($sql_count);
                                    $stmt->bind_param("i", $dia['id']);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_assoc()['total'];
                                    $conn->close();
                                    echo $count . " ejercicios";
                                    ?>
                                </small>
                            </div>
                            <button class="v0-btn v0-btn-dark" style="width: 100%;">
                                Ver
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

    </div>

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
