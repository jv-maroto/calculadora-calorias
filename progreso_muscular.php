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

// Valores por defecto para cálculos (TODO: obtener del perfil del usuario cuando esté implementado)
$peso_usuario = 70;
$altura_usuario = 170;
$edad_usuario = 25;
$sexo_usuario = 'M';

// Función para calcular 1RM estimado (Brzycki formula)
function calcular1RM($peso, $reps) {
    if ($reps == 1) return $peso;
    if ($reps > 30) $reps = 30; // Límite de la fórmula
    return $peso * (36 / (37 - $reps));
}

// Función para determinar nivel según tipo de ejercicio y peso corporal
function determinarNivel($musculo, $oneRM, $peso_corporal, $tipo_equipo, $ejercicio_nombre = '') {
    $ratio = $oneRM / $peso_corporal;

    // Ajustar según tipo de equipo - REALISTA
    // Las máquinas con poleas/palancas tienen ventaja mecánica MUY grande
    $multiplicador = 1.0;

    // Ejercicios específicos que son EXTREMADAMENTE fáciles en máquina
    $ejercicio_lower = strtolower($ejercicio_nombre);
    if (stripos($ejercicio_lower, 'reverse fly') !== false ||
        stripos($ejercicio_lower, 'lateral raise') !== false ||
        stripos($ejercicio_lower, 'rear delt') !== false ||
        stripos($ejercicio_lower, 'peck deck') !== false) {
        $multiplicador = 0.3; // Estas máquinas son ~70% más fáciles (polea + palanca larga)
    } else if ($tipo_equipo == 'Machine' || $tipo_equipo == 'maquina') {
        $multiplicador = 0.5; // Otras máquinas son ~50% más fáciles
    } else if ($tipo_equipo == 'Cable' || $tipo_equipo == 'polea') {
        $multiplicador = 0.75; // Poleas son ~25% más fáciles
    } else if ($tipo_equipo == 'Assisted') {
        $multiplicador = 0.4; // Asistidos son ~60% más fáciles
    } else if ($tipo_equipo == 'Dumbbell') {
        $multiplicador = 0.9; // Mancuernas son ~10% más difíciles que barra (estabilización)
    }

    $ratio = $ratio * $multiplicador;

    // Estándares basados en PERCENTILES DE POBLACIÓN REAL del gym
    // Nivel 1: Bottom 50% (mayoría de la gente)
    // Nivel 2: Top 50% (mejor que la mitad)
    // Nivel 3: Top 20% (fuerte)
    // Nivel 4: Top 5% (muy fuerte)
    // Nivel 5: Top 1% (élite competitivo)
    $estandares = [
        // Empujes horizontales (pecho) - Basado en Bench Press
        'Pecho' => [0.6, 0.9, 1.2, 1.6, 2.0],
        'Pecho superior' => [0.5, 0.75, 1.0, 1.4, 1.8],
        'Pecho/Tríceps' => [0.6, 0.9, 1.2, 1.6, 2.0],

        // Empujes verticales (hombros) - Basado en Overhead Press
        'Hombros' => [0.4, 0.6, 0.8, 1.1, 1.4],
        'Deltoides lateral' => [0.1, 0.18, 0.28, 0.4, 0.55],  // MUY bajos porque suelen ser máquina
        'Deltoides posterior' => [0.1, 0.18, 0.28, 0.4, 0.55], // MUY bajos porque suelen ser máquina
        'Deltoides' => [0.4, 0.6, 0.8, 1.1, 1.4],

        // Tríceps - Estimado de extensions/dips
        'Tríceps' => [0.35, 0.55, 0.75, 1.0, 1.3],

        // Jalones (espalda) - Basado en Row/Pulldown
        'Dorsal' => [0.5, 0.8, 1.1, 1.5, 1.9],
        'Espalda grosor' => [0.5, 0.8, 1.1, 1.5, 1.9],
        'Espalda media' => [0.45, 0.7, 1.0, 1.3, 1.7],
        'Espalda' => [0.5, 0.8, 1.1, 1.5, 1.9],
        'Trapecio' => [0.6, 1.0, 1.4, 1.9, 2.5],
        'Lumbares' => [0.6, 1.0, 1.4, 1.9, 2.3],
        'Lumbar' => [0.6, 1.0, 1.4, 1.9, 2.3],

        // Bíceps - Estimado de curl
        'Bíceps' => [0.2, 0.35, 0.5, 0.7, 0.9],
        'Espalda/Bíceps' => [0.35, 0.55, 0.8, 1.1, 1.4],

        // Piernas - Basado en Squat
        'Cuádriceps' => [0.9, 1.3, 1.7, 2.2, 2.8],
        'Glúteos' => [0.9, 1.3, 1.7, 2.2, 2.8],
        'Isquiotibiales' => [0.6, 0.9, 1.3, 1.7, 2.2],
        'Femoral' => [0.6, 0.9, 1.3, 1.7, 2.2],
        'Aductores' => [0.5, 0.8, 1.1, 1.5, 1.9],
        'Gemelos' => [0.6, 0.9, 1.3, 1.7, 2.2],
        'Pantorrillas' => [0.6, 0.9, 1.3, 1.7, 2.2],

        // Core y accesorios
        'Abdomen' => [0.25, 0.45, 0.65, 0.9, 1.2],
        'Antebrazos' => [0.25, 0.4, 0.55, 0.75, 1.0],
        'Antebrazo' => [0.25, 0.4, 0.55, 0.75, 1.0],
    ];

    $limites = $estandares[$musculo] ?? [0.4, 0.6, 0.85, 1.15, 1.5]; // Por defecto

    // Determinar nivel según el ratio (5 niveles basados en percentiles)
    if ($ratio < $limites[0]) return 1;      // Bottom 50%
    else if ($ratio < $limites[1]) return 2; // Top 50%
    else if ($ratio < $limites[2]) return 3; // Top 20%
    else if ($ratio < $limites[3]) return 4; // Top 5%
    else return 5;                            // Top 1%
}

function calcularNivelMusculo($musculo, $peso, $reps, $peso_corporal, $tipo_equipo = 'Barbell', $ejercicio_nombre = '') {
    if ($peso == 0 || $peso < 1 || $tipo_equipo == 'Bodyweight') {
        // Niveles basados solo en repeticiones para ejercicios sin peso (dominadas, fondos, etc.)
        if ($reps < 3) return 1;         // Bottom 50%
        else if ($reps < 8) return 2;    // Top 50%
        else if ($reps < 15) return 3;   // Top 20%
        else if ($reps < 25) return 4;   // Top 5%
        else return 5;                    // Top 1%
    } else {
        // Calcular 1RM estimado y determinar nivel
        $oneRM = calcular1RM($peso, $reps);
        return determinarNivel($musculo, $oneRM, $peso_corporal, $tipo_equipo, $ejercicio_nombre);
    }
}

// Obtener el MEJOR SET de CADA EJERCICIO individual
$sql_progreso = "SELECT
                    e.musculo_principal as grupo_muscular,
                    e.nombre as ejercicio,
                    e.tipo_equipo,
                    MAX(r.peso * r.reps) as mejor_volumen,
                    (SELECT peso FROM registros_entrenamiento r2
                     WHERE r2.ejercicio_id = e.id AND r2.nombre = r.nombre AND r2.apellidos = r.apellidos
                     ORDER BY (r2.peso * r2.reps) DESC LIMIT 1) as peso_mejor,
                    (SELECT reps FROM registros_entrenamiento r3
                     WHERE r3.ejercicio_id = e.id AND r3.nombre = r.nombre AND r3.apellidos = r.apellidos
                     ORDER BY (r3.peso * r3.reps) DESC LIMIT 1) as reps_mejor
                FROM registros_entrenamiento r
                JOIN ejercicios e ON r.ejercicio_id = e.id
                WHERE r.nombre = ? AND r.apellidos = ?
                GROUP BY e.id, e.musculo_principal, e.nombre, e.tipo_equipo
                ORDER BY e.musculo_principal, mejor_volumen DESC";

$stmt = $conn->prepare($sql_progreso);
$stmt->bind_param("ss", $nombre, $apellidos);
$stmt->execute();
$resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular nivel individual de cada ejercicio y agrupar por músculo
$ejercicios_por_musculo = [];
foreach ($resultados as $dato) {
    $musculo = $dato['grupo_muscular'];
    $ejercicio = $dato['ejercicio'];
    $peso = $dato['peso_mejor'];
    $reps = $dato['reps_mejor'];
    $tipo_equipo = $dato['tipo_equipo'];

    // Calcular nivel individual del ejercicio (pasando nombre para ajustes específicos)
    $nivel = calcularNivelMusculo($musculo, $peso, $reps, $peso_usuario, $tipo_equipo, $ejercicio);

    // Calcular volumen real (si es bodyweight, usar peso corporal)
    $volumen_real = $dato['mejor_volumen'];
    if ($peso == 0 || $peso < 1 || $tipo_equipo == 'Bodyweight') {
        $volumen_real = $peso_usuario * $reps; // peso corporal × reps
    }

    // Agregar a la lista de ejercicios por músculo
    if (!isset($ejercicios_por_musculo[$musculo])) {
        $ejercicios_por_musculo[$musculo] = [];
    }

    $ejercicios_por_musculo[$musculo][] = [
        'ejercicio' => $ejercicio,
        'peso' => $peso,
        'reps' => $reps,
        'tipo_equipo' => $tipo_equipo,
        'nivel' => $nivel,
        'volumen' => $volumen_real
    ];
}

// Calcular nivel PROMEDIO por músculo (nivel global del músculo)
$niveles_por_musculo = [];
foreach ($ejercicios_por_musculo as $musculo => $ejercicios) {
    $suma_niveles = 0;
    $total_ejercicios = count($ejercicios);

    foreach ($ejercicios as $ej) {
        $suma_niveles += $ej['nivel'];
    }

    $nivel_promedio = round($suma_niveles / $total_ejercicios, 1);

    $niveles_por_musculo[$musculo] = [
        'nivel_promedio' => $nivel_promedio,
        'ejercicios' => $ejercicios,
        'total_ejercicios' => $total_ejercicios
    ];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso Muscular</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fafafa;
            min-height: 100vh;
            padding: 2rem;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
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

        .back-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: white;
            border: 1px solid #e5e5e5;
            color: #666;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.15s;
            margin-bottom: 2rem;
        }

        .back-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        .body-container {
            display: grid;
            grid-template-columns: 1fr 500px 1fr;
            gap: 2rem;
            align-items: start;
            margin-top: 2rem;
        }

        .human-body {
            grid-column: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #muscle-svg {
            width: 100%;
            max-width: 500px;
            height: auto;
            filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.1));
        }

        .legend-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            position: sticky;
            top: 2rem;
        }

        .legend-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }

        .level-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .level-item:last-child {
            border-bottom: none;
        }

        .level-color {
            width: 24px;
            height: 24px;
            border: 1px solid #e5e5e5;
        }

        .level-info {
            flex: 1;
        }

        .level-name {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .level-desc {
            font-size: 11px;
            color: #999;
        }

        .muscle-levels {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
        }

        .muscle-level-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #f5f5f5;
        }

        .muscle-level-item:last-child {
            border-bottom: none;
        }

        .muscle-group-header {
            cursor: pointer;
            transition: background 0.15s;
        }

        .muscle-group-header:hover {
            background: #fafafa;
        }

        .muscle-group-container {
            border-bottom: 1px solid #f5f5f5;
        }

        .muscle-group-container:last-child {
            border-bottom: none;
        }

        .muscle-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .muscle-exercise {
            font-size: 12px;
            color: #999;
        }

        .muscle-stats {
            text-align: right;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .muscle-weight {
            font-size: 13px;
            color: #666;
        }

        .expand-icon {
            transition: transform 0.2s;
            color: #999;
        }

        .muscle-group-header.expanded .expand-icon {
            transform: rotate(180deg);
        }

        .exercise-details {
            background: #fafafa;
            padding: 0.5rem 0.5rem 0.5rem 1.5rem;
        }

        .exercise-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: white;
            border: 1px solid #e5e5e5;
            margin-bottom: 0.5rem;
        }

        .exercise-item:last-child {
            margin-bottom: 0;
        }

        .exercise-name {
            font-size: 13px;
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .exercise-info {
            font-size: 11px;
            color: #666;
        }

        .level-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid;
        }

        .level-1 { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .level-2 { background: #fff7ed; color: #9a3412; border-color: #fed7aa; }
        .level-3 { background: #fefce8; color: #854d0e; border-color: #fde047; }
        .level-4 { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .level-5 { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
        .level-6 { background: #f5f3ff; color: #6b21a8; border-color: #d8b4fe; }

        /* Colores del SVG según nivel */
        .svg-level-0 { fill: #e5e5e5 !important; stroke: #bbb !important; }
        .svg-level-1 { fill: #fecaca !important; stroke: #991b1b !important; }
        .svg-level-2 { fill: #fed7aa !important; stroke: #9a3412 !important; }
        .svg-level-3 { fill: #fde047 !important; stroke: #854d0e !important; }
        .svg-level-4 { fill: #bbf7d0 !important; stroke: #166534 !important; }
        .svg-level-5 { fill: #bfdbfe !important; stroke: #1e40af !important; }
        .svg-level-6 { fill: #d8b4fe !important; stroke: #6b21a8 !important; }

        /* Tooltip para mostrar info al hover */
        .muscle-tooltip {
            position: absolute;
            background: white;
            border: 2px solid #1a1a1a;
            padding: 0.75rem 1rem;
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            top: 10px;
            left: 10px;
            min-width: 180px;
        }

        .muscle-tooltip.show {
            opacity: 1;
        }

        .tooltip-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .tooltip-level {
            font-size: 12px;
            color: #666;
        }

        .human-body {
            position: relative;
        }

        @media (max-width: 1200px) {
            .body-container {
                grid-template-columns: 1fr;
            }
            .human-body {
                grid-column: 1;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 0;
            }

            .v0-card {
                padding: 1rem;
            }

            .section-title {
                font-size: 18px;
            }

            .section-description {
                font-size: 13px;
            }

            .back-btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .body-container {
                gap: 1rem;
            }

            .legend-card {
                position: static;
                padding: 1rem;
            }

            .legend-title {
                font-size: 14px;
            }

            .level-item {
                padding: 0.4rem 0;
            }

            .level-name {
                font-size: 12px;
            }

            .level-desc {
                font-size: 10px;
            }

            .muscle-levels {
                padding: 1rem;
            }

            .muscle-name {
                font-size: 13px;
            }

            .muscle-exercise {
                font-size: 11px;
            }

            .exercise-name {
                font-size: 12px;
            }

            .exercise-info {
                font-size: 10px;
            }

            .level-badge {
                font-size: 10px;
                padding: 3px 8px;
            }

            .muscle-tooltip {
                min-width: 150px;
                padding: 0.5rem 0.75rem;
                font-size: 11px;
            }

            .tooltip-title {
                font-size: 12px;
            }

            .tooltip-level {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="analisis_progreso.php" class="back-btn">← Volver al Análisis</a>

        <div class="v0-card">
            <div class="section-title">Progreso Muscular</div>
            <div class="section-description">Visualiza tu progreso de fuerza en cada grupo muscular según estándares profesionales</div>
        </div>

        <div class="body-container">
            <!-- Leyenda izquierda -->
            <div class="legend-card">
                <div class="legend-title">Niveles de Fuerza</div>
                <div class="level-item">
                    <div class="level-color" style="background: #fecaca;"></div>
                    <div class="level-info">
                        <div class="level-name">Nivel 1 - Bottom 50%</div>
                        <div class="level-desc">Mayoría de la gente que va al gym</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #fed7aa;"></div>
                    <div class="level-info">
                        <div class="level-name">Nivel 2 - Top 50%</div>
                        <div class="level-desc">Mejor que la mitad del gym</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #fde047;"></div>
                    <div class="level-info">
                        <div class="level-name">Nivel 3 - Top 20%</div>
                        <div class="level-desc">Entre los más fuertes</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #bbf7d0;"></div>
                    <div class="level-info">
                        <div class="level-name">Nivel 4 - Top 5%</div>
                        <div class="level-desc">Muy fuerte, nivel competitivo</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #bfdbfe;"></div>
                    <div class="level-info">
                        <div class="level-name">Nivel 5 - Top 1%</div>
                        <div class="level-desc">Élite absoluta</div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e5e5;">
                    <div style="font-size: 12px; color: #666; line-height: 1.6;">
                        <strong>Percentiles de población real</strong><br>
                        Se calcula tu 1RM ajustado por tipo de equipo (máquinas ~50% más fáciles, poleas ~25%) y se compara con la población que entrena regularmente.
                    </div>
                </div>
            </div>

            <!-- Mapa muscular central -->
            <div class="human-body">
                <!-- Tooltip flotante dentro del contenedor -->
                <div class="muscle-tooltip" id="muscle-tooltip">
                    <div class="tooltip-title" id="tooltip-title"></div>
                    <div class="tooltip-level" id="tooltip-level"></div>
                </div>
                <object data="muscles-body.svg" type="image/svg+xml" id="muscle-svg"></object>
            </div>

            <!-- Detalle por músculo derecha -->
            <div class="muscle-levels">
                <div class="legend-title">Detalle por Músculo</div>
                <?php if (empty($niveles_por_musculo)): ?>
                    <div style="text-align: center; padding: 2rem; color: #999;">
                        <div style="font-size: 48px; margin-bottom: 1rem;">💪</div>
                        <div>Comienza a entrenar para ver tus niveles</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($niveles_por_musculo as $musculo => $dato): ?>
                        <div class="muscle-group-container" data-muscle="<?php echo htmlspecialchars($musculo); ?>">
                            <!-- Nivel global del músculo (expandible) -->
                            <div class="muscle-level-item muscle-group-header" onclick="toggleMuscleDetails(this)">
                                <div style="flex: 1;">
                                    <div class="muscle-name">
                                        <?php echo htmlspecialchars($musculo); ?>
                                        <span style="font-size: 11px; color: #999; font-weight: 400;">
                                            (<?php echo $dato['total_ejercicios']; ?> ejercicio<?php echo $dato['total_ejercicios'] > 1 ? 's' : ''; ?>)
                                        </span>
                                    </div>
                                    <div class="muscle-exercise">Nivel promedio de todos los ejercicios</div>
                                </div>
                                <div class="muscle-stats">
                                    <?php
                                        $nivel_promedio = $dato['nivel_promedio'];
                                        $nivel_display = floor($nivel_promedio); // Para color del badge
                                    ?>
                                    <div class="level-badge level-<?php echo $nivel_display; ?>">
                                        Nivel <?php echo number_format($nivel_promedio, 1); ?>
                                    </div>
                                    <span class="expand-icon" style="margin-left: 0.5rem; font-size: 18px;">▼</span>
                                </div>
                            </div>

                            <!-- Ejercicios individuales (ocultos por defecto) -->
                            <div class="exercise-details" style="display: none;">
                                <?php foreach ($dato['ejercicios'] as $ejercicio): ?>
                                    <div class="exercise-item">
                                        <div style="flex: 1;">
                                            <div class="exercise-name"><?php echo htmlspecialchars($ejercicio['ejercicio']); ?></div>
                                            <div class="exercise-info">
                                                <?php
                                                if ($ejercicio['peso'] > 0) {
                                                    echo $ejercicio['peso'] . ' kg × ' . $ejercicio['reps'] . ' reps';
                                                } else {
                                                    echo $ejercicio['reps'] . ' reps (bodyweight)';
                                                }
                                                ?>
                                                <span style="color: #999; margin-left: 0.5rem;">(<?php echo $ejercicio['tipo_equipo']; ?>)</span>
                                            </div>
                                        </div>
                                        <div class="level-badge level-<?php echo $ejercicio['nivel']; ?>">
                                            Nivel <?php echo $ejercicio['nivel']; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Función para expandir/colapsar detalles de ejercicios
        function toggleMuscleDetails(header) {
            const container = header.parentElement;
            const details = container.querySelector('.exercise-details');
            const isExpanded = header.classList.contains('expanded');

            if (isExpanded) {
                header.classList.remove('expanded');
                details.style.display = 'none';
            } else {
                header.classList.add('expanded');
                details.style.display = 'block';
            }
        }

        const nivelPorMusculo = <?php
            $niveles_js = [];
            foreach ($niveles_por_musculo as $musculo => $dato) {
                // Usar el nivel promedio para colorear el SVG
                $niveles_js[$musculo] = floor($dato['nivel_promedio']);
            }
            echo json_encode($niveles_js, JSON_UNESCAPED_UNICODE);
        ?>;

        console.log('=== TODOS LOS MÚSCULOS CON NIVELES ===');
        console.log(nivelPorMusculo);
        console.log('Total músculos:', Object.keys(nivelPorMusculo).length);

        // Mapeo de nombres de grupos musculares DB a SVG IDs, lado y posición vertical (%)
        const muscleMapping = {
            'Pecho superior': { ids: ['upper_pecs'], side: 'left', position: 25 },
            'Pecho': { ids: ['upper_pecs', 'middle_pecs', 'lower_pecs'], side: 'left', position: 28 },
            'Pecho/Tríceps': { ids: ['middle_pecs', 'lower_pecs', 'triceps'], side: 'left', position: 30 },
            'Bíceps': { ids: ['biceps'], side: 'left', position: 35 },
            'Espalda/Bíceps': { ids: ['biceps', 'lats'], side: 'left', position: 35 },
            'Tríceps': { ids: ['triceps'], side: 'right', position: 35 },
            'Hombros': { ids: ['front_delts', 'side_delts', 'rear_delts'], side: 'left', position: 20 },
            'Deltoides lateral': { ids: ['side_delts'], side: 'left', position: 22 },
            'Deltoides posterior': { ids: ['rear_delts'], side: 'right', position: 22 },
            'Deltoides': { ids: ['front_delts', 'side_delts'], side: 'left', position: 20 },
            'Abdomen': { ids: ['upper_abs', 'lower_abs', 'obliques'], side: 'left', position: 44 },
            'Cuádriceps': { ids: ['quads'], side: 'left', position: 60 },
            'Isquiotibiales': { ids: ['hamstrings'], side: 'right', position: 60 },
            'Femoral': { ids: ['hamstrings'], side: 'right', position: 60 },
            'Gemelos': { ids: ['calves'], side: 'left', position: 80 },
            'Pantorrillas': { ids: ['calves'], side: 'left', position: 80 },
            'Antebrazos': { ids: ['forearms'], side: 'left', position: 45 },
            'Antebrazo': { ids: ['forearms'], side: 'left', position: 45 },
            'Dorsal': { ids: ['lats'], side: 'right', position: 35 },
            'Espalda grosor': { ids: ['lats'], side: 'right', position: 35 },
            'Espalda media': { ids: ['lower_traps', 'rhomboids'], side: 'right', position: 32 },
            'Espalda': { ids: ['upper_traps', 'lats', 'lower_back'], side: 'right', position: 30 },
            'Trapecio': { ids: ['upper_traps', 'lower_traps'], side: 'right', position: 18 },
            'Lumbares': { ids: ['lower_back'], side: 'right', position: 50 },
            'Lumbar': { ids: ['lower_back'], side: 'right', position: 50 },
            'Glúteos': { ids: ['glutes'], side: 'right', position: 55 },
            'Aductores': { ids: ['hip_abductor', 'hip_adductor'], side: 'left', position: 58 }
        };

        // Esperar a que el SVG se cargue
        const svgObject = document.getElementById('muscle-svg');
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

            // Aplicar colores según nivel
            console.log('Niveles por músculo:', nivelPorMusculo);

            // Ordenar músculos por especificidad (más específicos al final para que sobrescriban)
            // Esto asegura que músculos específicos (como "Deltoides lateral") sobrescriban los generales (como "Hombros")
            const musculos = Object.keys(nivelPorMusculo);
            musculos.sort((a, b) => {
                // Priorizar músculos con nombres más cortos primero (genéricos primero, específicos después)
                return a.length - b.length;
            });

            musculos.forEach(musculo => {
                const nivel = nivelPorMusculo[musculo];
                const muscleData = muscleMapping[musculo];

                console.log(`Procesando: ${musculo} - Nivel ${nivel} - SVG IDs:`, muscleData);

                if (muscleData && muscleData.ids) {
                    muscleData.ids.forEach(svgGroupId => {
                        const group = svgDoc.getElementById(svgGroupId);
                        if (group) {
                            console.log(`✓ Encontrado grupo SVG: ${svgGroupId} para ${musculo}`);
                            const paths = group.querySelectorAll('path');
                            console.log(`  Paths encontrados: ${paths.length}`);
                            paths.forEach(path => {
                                // Aplicar color según nivel
                                if (nivel === 1) {
                                    path.style.fill = '#fecaca';
                                    path.style.stroke = '#991b1b';
                                } else if (nivel === 2) {
                                    path.style.fill = '#fed7aa';
                                    path.style.stroke = '#9a3412';
                                } else if (nivel === 3) {
                                    path.style.fill = '#fde047';
                                    path.style.stroke = '#854d0e';
                                } else if (nivel === 4) {
                                    path.style.fill = '#bbf7d0';
                                    path.style.stroke = '#166534';
                                } else if (nivel === 5) {
                                    path.style.fill = '#bfdbfe';
                                    path.style.stroke = '#1e40af';
                                } else if (nivel === 6) {
                                    path.style.fill = '#d8b4fe';
                                    path.style.stroke = '#6b21a8';
                                }
                                path.style.strokeWidth = '8';
                            });

                            // Agregar eventos de hover al grupo
                            group.style.cursor = 'pointer';
                            group.addEventListener('mouseenter', function(e) {
                                showTooltip(musculo, nivel, muscleData.side, muscleData.position);
                            });
                            group.addEventListener('mouseleave', function() {
                                hideTooltip();
                            });

                        } else {
                            console.log(`NO encontrado grupo SVG: ${svgGroupId}`);
                        }
                    });
                }
            });
        });

        function showTooltip(musculo, nivel, side, position) {
            const tooltip = document.getElementById('muscle-tooltip');
            const title = document.getElementById('tooltip-title');
            const levelText = document.getElementById('tooltip-level');

            const levelNames = {
                1: 'Bottom 50%',
                2: 'Top 50%',
                3: 'Top 20%',
                4: 'Top 5%',
                5: 'Top 1%'
            };

            title.textContent = musculo;
            levelText.textContent = `Nivel ${nivel} - ${levelNames[nivel]}`;

            // Cambiar posición horizontal según lado (más separado del gráfico)
            tooltip.style.left = side === 'left' ? '-220px' : 'auto';
            tooltip.style.right = side === 'right' ? '-220px' : 'auto';

            // Cambiar posición vertical según músculo
            tooltip.style.top = position + '%';

            tooltip.classList.add('show');
        }

        function hideTooltip() {
            const tooltip = document.getElementById('muscle-tooltip');
            tooltip.classList.remove('show');
        }
    </script>
</body>
</html>
