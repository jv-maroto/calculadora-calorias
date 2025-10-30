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

// Obtener peso del usuario (por defecto 70kg si no está configurado)
$peso_usuario = 70;

// Obtener fecha actual y rango de semana
$hoy = date('Y-m-d');
$inicio_semana = date('Y-m-d', strtotime('monday this week'));
$fin_semana = date('Y-m-d', strtotime('sunday this week'));

// Obtener volumen por grupo muscular en esta semana
// Para bodyweight (peso=0), usar peso corporal del usuario
$sql_volumen = "SELECT
                    e.musculo_principal as grupo_muscular,
                    COUNT(DISTINCT DATE(r.fecha)) as sesiones,
                    COUNT(r.id) as total_sets,
                    SUM(r.reps) as total_reps,
                    SUM(
                        CASE
                            WHEN r.peso = 0 OR r.peso IS NULL THEN ? * r.reps
                            ELSE r.peso * r.reps
                        END
                    ) as volumen_kg
                FROM registros_entrenamiento r
                JOIN ejercicios e ON r.ejercicio_id = e.id
                WHERE r.nombre = ?
                    AND r.apellidos = ?
                    AND DATE(r.fecha) BETWEEN ? AND ?
                GROUP BY e.musculo_principal
                ORDER BY volumen_kg DESC";

$stmt = $conn->prepare($sql_volumen);
$stmt->bind_param("dssss", $peso_usuario, $nombre, $apellidos, $inicio_semana, $fin_semana);
$stmt->execute();
$volumen_datos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener total de ejercicios diferentes trabajados
$sql_ejercicios = "SELECT COUNT(DISTINCT r.ejercicio_id) as total_ejercicios
                   FROM registros_entrenamiento r
                   WHERE r.nombre = ?
                       AND r.apellidos = ?
                       AND DATE(r.fecha) BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_ejercicios);
$stmt->bind_param("ssss", $nombre, $apellidos, $inicio_semana, $fin_semana);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calcular totales
$total_sets = array_sum(array_column($volumen_datos, 'total_sets'));
$total_reps = array_sum(array_column($volumen_datos, 'total_reps'));
$total_volumen = array_sum(array_column($volumen_datos, 'volumen_kg'));

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volumen Semanal</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            padding-bottom: 80px;
        }

        .v0-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .section-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 1rem;
        }

        .week-range {
            display: inline-block;
            padding: 6px 12px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            font-size: 12px;
            color: #666;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1rem;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #999;
            border-bottom: 1px solid #e5e5e5;
        }

        tbody td {
            padding: 12px;
            font-size: 14px;
            color: #1a1a1a;
            border-bottom: 1px solid #f5f5f5;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 80px !important;
            }
            nav:first-of-type {
                display: none !important;
            }
            nav:nth-of-type(2) {
                display: flex !important;
            }
            .chart-muscle-grid {
                grid-template-columns: 1fr !important;
            }
            .muscle-mini-container {
                display: none !important;
            }

            .mobile-back-btn {
                display: inline-block !important;
                width: 100%;
                text-align: center;
            }

            .v0-card {
                padding: 1rem;
            }

            .section-title {
                font-size: 16px;
            }

            .section-description {
                font-size: 12px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .stat-value {
                font-size: 20px;
            }

            .stat-label {
                font-size: 11px;
            }

            table {
                font-size: 12px;
            }

            thead th {
                padding: 8px;
                font-size: 11px;
            }

            tbody td {
                padding: 8px;
                font-size: 12px;
            }
        }

        @media (min-width: 769px) {
            body {
                padding-bottom: 2rem !important;
            }
            nav:first-of-type {
                display: flex !important;
            }
            nav:nth-of-type(2) {
                display: none !important;
            }

            .mobile-back-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Nav - Desktop -->
    <nav style="display: none; background: white; border-bottom: 1px solid #e5e5e5; padding: 0 2rem; height: 60px; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; gap: 2rem;">
            <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">← Dashboard</a>
            <a href="gym_hub.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">GYM Hub</a>
            <a href="gestionar_ejercicios.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Ejercicios</a>
            <a href="volumen_semanal.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Volumen</a>
        </div>
        <a href="logout.php" style="color: #999; text-decoration: none; font-size: 14px; font-weight: 500;">Salir</a>
    </nav>

    <!-- Bottom Nav - Mobile -->
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e5e5; display: flex; justify-content: space-around; padding: 12px 0; z-index: 100;">
        <a href="dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Inicio</div>
        </a>
        <a href="gym_hub.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>GYM</div>
        </a>
        <a href="gestionar_ejercicios.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Ejercicios</div>
        </a>
        <a href="volumen_semanal.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #1a1a1a; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Volumen</div>
        </a>
    </nav>

    <div style="max-width: 1400px; margin: 0 auto; padding: 1rem 1rem 2rem;">

        <!-- Botón volver - Solo móvil -->
        <a href="dashboard.php" class="mobile-back-btn" style="display: inline-block; padding: 0.75rem 1.5rem; background: white; border: 1px solid #e5e5e5; color: #666; text-decoration: none; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem;">
            ← Volver
        </a>

        <!-- Header -->
        <div class="v0-card">
            <div class="section-title">Volumen Semanal</div>
            <div class="section-description">Seguimiento de volumen por grupo muscular</div>
            <div class="week-range">
                Semana: <?php echo date('d/m', strtotime($inicio_semana)); ?> - <?php echo date('d/m/Y', strtotime($fin_semana)); ?>
            </div>
        </div>

        <!-- Estadísticas generales -->
        <div class="v0-card">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_sets; ?></div>
                    <div class="stat-label">Sets Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_reps); ?></div>
                    <div class="stat-label">Reps Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_volumen); ?> kg</div>
                    <div class="stat-label">Volumen Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_ejercicios']; ?></div>
                    <div class="stat-label">Ejercicios Diferentes</div>
                </div>
            </div>
        </div>

        <?php if (empty($volumen_datos)): ?>
            <!-- Estado vacío -->
            <div class="v0-card">
                <div class="empty-state">
                    <div>No hay datos de entrenamiento esta semana</div>
                    <div style="font-size: 12px; margin-top: 0.5rem;">Comienza a entrenar para ver tus estadísticas</div>
                </div>
            </div>
        <?php else: ?>
            <!-- Gráfica de volumen y mapa muscular -->
            <div class="v0-card">
                <div class="section-title">Volumen por Grupo Muscular</div>
                <div class="chart-muscle-grid" style="display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center;">
                    <div class="chart-container">
                        <canvas id="chartVolumen"></canvas>
                    </div>
                    <div class="muscle-mini-container" style="width: 250px; display: flex; flex-direction: column; align-items: center;">
                        <div style="font-size: 12px; color: #999; margin-bottom: 1rem; text-align: center;">Músculos Trabajados</div>
                        <object data="muscles-body.svg" type="image/svg+xml" id="muscle-svg-mini" style="width: 100%; height: auto; max-height: 400px;"></object>
                    </div>
                </div>
            </div>

            <!-- Tabla detallada -->
            <div class="v0-card">
                <div class="section-title">Detalle por Grupo Muscular</div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Grupo Muscular</th>
                                <th>Sesiones</th>
                                <th>Sets</th>
                                <th>Reps</th>
                                <th>Volumen (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($volumen_datos as $grupo): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($grupo['grupo_muscular'] ?: 'Sin especificar'); ?></strong></td>
                                    <td><?php echo $grupo['sesiones']; ?></td>
                                    <td><?php echo $grupo['total_sets']; ?></td>
                                    <td><?php echo number_format($grupo['total_reps']); ?></td>
                                    <td><strong><?php echo number_format($grupo['volumen_kg']); ?> kg</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php if (!empty($volumen_datos)): ?>
    <script>
        // Preparar datos para la gráfica
        const grupos = <?php echo json_encode(array_column($volumen_datos, 'grupo_muscular')); ?>;
        const volumenes = <?php echo json_encode(array_column($volumen_datos, 'volumen_kg')); ?>;
        const sets = <?php echo json_encode(array_column($volumen_datos, 'total_sets')); ?>;

        // Crear gráfica de barras
        const ctx = document.getElementById('chartVolumen').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: grupos.map(g => g || 'Sin especificar'),
                datasets: [{
                    label: 'Volumen (kg)',
                    data: volumenes,
                    backgroundColor: '#1a1a1a',
                    borderColor: '#1a1a1a',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return 'Sets: ' + sets[index];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' kg';
                            }
                        }
                    }
                }
            }
        });

        // Mapeo de grupos musculares DB a SVG IDs
        const muscleMapping = {
            'Pecho superior': ['upper_pecs'],
            'Pecho': ['upper_pecs', 'middle_pecs', 'lower_pecs'],
            'Pecho/Tríceps': ['middle_pecs', 'lower_pecs', 'triceps'],
            'Bíceps': ['biceps'],
            'Espalda/Bíceps': ['biceps', 'lats'],
            'Tríceps': ['triceps'],
            'Hombros': ['front_delts', 'side_delts', 'rear_delts'],
            'Deltoides lateral': ['side_delts'],
            'Deltoides posterior': ['rear_delts'],
            'Deltoides': ['front_delts', 'side_delts'],
            'Abdomen': ['upper_abs', 'lower_abs', 'obliques'],
            'Cuádriceps': ['quads'],
            'Isquiotibiales': ['hamstrings'],
            'Femoral': ['hamstrings'],
            'Gemelos': ['calves'],
            'Pantorrillas': ['calves'],
            'Antebrazos': ['forearms'],
            'Antebrazo': ['forearms'],
            'Dorsal': ['lats'],
            'Espalda grosor': ['lats'],
            'Espalda media': ['lower_traps', 'rhomboids'],
            'Espalda': ['upper_traps', 'lats', 'lower_back'],
            'Trapecio': ['upper_traps', 'lower_traps'],
            'Lumbares': ['lower_back'],
            'Lumbar': ['lower_back'],
            'Glúteos': ['glutes'],
            'Aductores': ['hip_abductor', 'hip_adductor']
        };

        // Encontrar qué grupos del SVG se trabajaron esta semana
        const svgGroupsToHighlight = new Set();
        grupos.forEach(grupoMuscular => {
            if (grupoMuscular && muscleMapping[grupoMuscular]) {
                muscleMapping[grupoMuscular].forEach(svgId => svgGroupsToHighlight.add(svgId));
            }
        });

        // Esperar a que el SVG se cargue y aplicar highlights
        const svgObject = document.getElementById('muscle-svg-mini');
        if (svgObject) {
            svgObject.addEventListener('load', function() {
                const svgDoc = svgObject.contentDocument;
                if (!svgDoc) return;

                // Primero aplicar gris a todo
                const allPaths = svgDoc.querySelectorAll('path');
                allPaths.forEach(path => {
                    path.style.fill = '#e5e5e5';
                    path.style.stroke = '#bbb';
                    path.style.strokeWidth = '3';
                });

                // Highlight los grupos trabajados
                svgGroupsToHighlight.forEach(svgGroupId => {
                    const group = svgDoc.getElementById(svgGroupId);
                    if (group) {
                        const paths = group.querySelectorAll('path');
                        paths.forEach(path => {
                            path.style.fill = '#1a1a1a';
                            path.style.stroke = '#000';
                            path.style.strokeWidth = '8';
                        });
                    }
                });
            });
        }
    </script>
    <?php endif; ?>
</body>
</html>
