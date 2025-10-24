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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
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
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .dia-descanso {
            background: linear-gradient(135deg, #e0e0e0 0%, #bdbdbd 100%);
            border: none;
        }
        .dia-descanso .card-body {
            padding: 1.5rem 1rem;
        }
        .dia-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .dia-entrenamiento {
            position: relative;
        }
        .dia-entrenamiento .card-header {
            padding: 1.25rem 1rem;
            border: none;
        }
        .dia-entrenamiento .card-body {
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .dia-icon {
                font-size: 2rem;
                margin-bottom: 0.25rem;
            }
            .dia-entrenamiento .card-header {
                padding: 1rem 0.75rem;
            }
            .dia-entrenamiento .card-body {
                padding: 0.75rem;
            }
            .dia-descanso .card-body {
                padding: 1rem 0.75rem;
            }
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
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">💪 Calculadora de Calorías</a>
            <span class="navbar-text text-white me-3">
                👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?>
            </span>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link" href="index.php" title="Calculadora Principal">🏠</a>
                <a class="nav-link" href="reverse_diet.php" title="Reverse Diet">🔄</a>
                <a class="nav-link active" href="rutinas.php" title="Rutinas">🏋️</a>
                <a class="nav-link" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container py-4" style="max-width: 1400px;">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">🏋️ <?php echo htmlspecialchars($rutina['nombre']); ?></h3>
                                <small><?php echo htmlspecialchars($rutina['descripcion']); ?></small>
                            </div>
                            <a href="gestionar_ejercicios.php" class="btn btn-light btn-sm">
                                ⚙️ Gestionar Ejercicios
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <h5>Último Entrenamiento</h5>
                                <?php if ($ultimo_entrenamiento): ?>
                                    <p class="mb-0">
                                        <strong><?php echo date('d/m/Y', strtotime($ultimo_entrenamiento['fecha'])); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($ultimo_entrenamiento['dia']); ?></small>
                                    </p>
                                <?php else: ?>
                                    <p class="text-muted">Sin registros aún</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h5>Progreso de esta semana</h5>
                                <div class="row">
                                    <?php foreach ($progreso_semana as $dia_progreso): ?>
                                        <?php
                                        $porcentaje = $dia_progreso['total_ejercicios'] > 0
                                            ? ($dia_progreso['ejercicios_completados'] / $dia_progreso['total_ejercicios']) * 100
                                            : 0;
                                        $completado = $porcentaje >= 100;
                                        ?>
                                        <div class="col">
                                            <div class="text-center">
                                                <div class="mb-1">
                                                    <?php if ($completado): ?>
                                                        <span class="badge bg-success">✓</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo round($porcentaje); ?>%</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?php echo explode(' - ', $dia_progreso['dia'])[0]; ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Días de la semana -->
        <div class="row g-3">
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
                <div class="col-6 col-md-4 col-lg-3">
                    <?php if ($dia['es_descanso']): ?>
                        <!-- Día de descanso -->
                        <div class="card dia-card dia-descanso">
                            <div class="card-body text-center">
                                <div class="dia-icon">😴</div>
                                <h5 class="text-white mb-1" style="font-size: 0.95rem;"><?php echo htmlspecialchars($dia['nombre']); ?></h5>
                                <p class="text-white-50 mb-0" style="font-size: 0.8rem;">Descanso</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Día de entrenamiento -->
                        <div class="card dia-card dia-entrenamiento" onclick="location.href='dia_entrenamiento.php?dia_id=<?php echo $dia['id']; ?>'">
                            <div class="card-header text-center <?php echo $gradientes[$tipo_upper] ?? 'gradient-push'; ?>">
                                <div class="dia-icon"><?php echo $icono_dia; ?></div>
                                <h5 class="text-white mb-1" style="font-size: 0.95rem;"><?php echo htmlspecialchars($dia['nombre']); ?></h5>
                                <span class="badge bg-white bg-opacity-25 text-white px-2 py-1" style="font-size: 0.7rem;">
                                    <?php echo htmlspecialchars($dia['tipo']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-2">
                                    <small class="text-muted">
                                        <strong>
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
                                        </strong>
                                    </small>
                                </div>
                                <button class="btn btn-dark btn-sm w-100 fw-bold">
                                    Ver →
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
