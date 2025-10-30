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
function determinarNivel($musculo, $oneRM, $peso_corporal) {
    $ratio = $oneRM / $peso_corporal;

    // Estándares según grupo muscular (ratio de 1RM / peso corporal)
    // Basados en ExRx.net, Symmetric Strength y datos de competición
    // [Untrained, Novice, Intermediate, Advanced, Elite]
    $estandares = [
        // Empujes horizontales (pecho) - Basado en Bench Press
        'Pecho' => [0.5, 0.75, 1.0, 1.5, 2.0],
        'Pecho superior' => [0.4, 0.65, 0.9, 1.35, 1.8],
        'Pecho/Tríceps' => [0.5, 0.75, 1.0, 1.5, 2.0],

        // Empujes verticales (hombros) - Basado en Overhead Press
        'Hombros' => [0.3, 0.5, 0.75, 1.0, 1.35],
        'Deltoides lateral' => [0.2, 0.35, 0.5, 0.7, 0.9],
        'Deltoides posterior' => [0.2, 0.35, 0.5, 0.7, 0.9],
        'Deltoides' => [0.3, 0.5, 0.75, 1.0, 1.35],

        // Tríceps - Estimado de extensions/dips
        'Tríceps' => [0.3, 0.5, 0.7, 1.0, 1.3],

        // Jalones (espalda) - Basado en Row/Pulldown
        'Dorsal' => [0.4, 0.7, 1.0, 1.4, 1.8],
        'Espalda grosor' => [0.4, 0.7, 1.0, 1.4, 1.8],
        'Espalda media' => [0.35, 0.6, 0.9, 1.25, 1.6],
        'Espalda' => [0.4, 0.7, 1.0, 1.4, 1.8],
        'Trapecio' => [0.5, 0.9, 1.3, 1.8, 2.4],
        'Lumbares' => [0.5, 0.9, 1.3, 1.8, 2.2],
        'Lumbar' => [0.5, 0.9, 1.3, 1.8, 2.2],

        // Bíceps - Estimado de curl
        'Bíceps' => [0.25, 0.4, 0.6, 0.85, 1.15],
        'Espalda/Bíceps' => [0.3, 0.5, 0.75, 1.05, 1.4],

        // Piernas - Basado en Squat
        'Cuádriceps' => [0.75, 1.0, 1.5, 2.0, 2.5],
        'Glúteos' => [0.75, 1.0, 1.5, 2.0, 2.5],
        'Isquiotibiales' => [0.5, 0.8, 1.2, 1.6, 2.0],
        'Femoral' => [0.5, 0.8, 1.2, 1.6, 2.0],
        'Aductores' => [0.4, 0.7, 1.0, 1.4, 1.8],
        'Gemelos' => [0.5, 0.8, 1.2, 1.6, 2.0],
        'Pantorrillas' => [0.5, 0.8, 1.2, 1.6, 2.0],

        // Core y accesorios
        'Abdomen' => [0.2, 0.4, 0.6, 0.85, 1.1],
        'Antebrazos' => [0.2, 0.35, 0.5, 0.7, 0.9],
        'Antebrazo' => [0.2, 0.35, 0.5, 0.7, 0.9],
    ];

    $limites = $estandares[$musculo] ?? [0.3, 0.5, 0.75, 1.0, 1.35]; // Por defecto

    // Determinar nivel según el ratio (5 niveles)
    if ($ratio < $limites[0]) return 1;      // Untrained
    else if ($ratio < $limites[1]) return 2; // Novice
    else if ($ratio < $limites[2]) return 3; // Intermediate
    else if ($ratio < $limites[3]) return 4; // Advanced
    else return 5;                            // Elite
}

function calcularNivelMusculo($musculo, $peso, $reps, $peso_corporal) {
    if ($peso == 0 || $peso < 1) {
        // Niveles basados solo en repeticiones para ejercicios sin peso (dominadas, fondos, etc.)
        if ($reps < 3) return 1;         // Untrained
        else if ($reps < 8) return 2;    // Novice
        else if ($reps < 15) return 3;   // Intermediate
        else if ($reps < 25) return 4;   // Advanced
        else return 5;                    // Elite
    } else {
        // Calcular 1RM estimado y determinar nivel
        $oneRM = calcular1RM($peso, $reps);
        return determinarNivel($musculo, $oneRM, $peso_corporal);
    }
}

// Obtener los mejores registros por grupo muscular
$sql_progreso = "SELECT
                    e.musculo_principal as grupo_muscular,
                    e.nombre as ejercicio,
                    MAX(r.peso) as peso_max,
                    MAX(r.reps) as reps_max,
                    MAX(r.peso * r.reps) as one_rm_estimate
                FROM registros_entrenamiento r
                JOIN ejercicios e ON r.ejercicio_id = e.id
                WHERE r.nombre = ? AND r.apellidos = ?
                GROUP BY e.musculo_principal, e.nombre
                ORDER BY grupo_muscular, one_rm_estimate DESC";

$stmt = $conn->prepare($sql_progreso);
$stmt->bind_param("ss", $nombre, $apellidos);
$stmt->execute();
$progreso_datos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Agrupar por músculo (tomar el mejor ejercicio de cada grupo)
$niveles_por_musculo = [];
foreach ($progreso_datos as $dato) {
    $musculo = $dato['grupo_muscular'];
    if (!isset($niveles_por_musculo[$musculo])) {
        $niveles_por_musculo[$musculo] = $dato;
    }
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
        }

        .muscle-weight {
            font-size: 13px;
            color: #666;
        }

        .level-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid;
            margin-top: 4px;
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
                <div class="legend-title">Niveles de Progreso</div>
                <div class="level-item">
                    <div class="level-color" style="background: #fecaca;"></div>
                    <div class="level-info">
                        <div class="level-name">Untrained</div>
                        <div class="level-desc">Sin entrenamiento previo</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #fed7aa;"></div>
                    <div class="level-info">
                        <div class="level-name">Novice</div>
                        <div class="level-desc">Primeros meses de entrenamiento</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #fde047;"></div>
                    <div class="level-info">
                        <div class="level-name">Intermediate</div>
                        <div class="level-desc">1-2 años de entrenamiento</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #bbf7d0;"></div>
                    <div class="level-info">
                        <div class="level-name">Advanced</div>
                        <div class="level-desc">Varios años de entrenamiento</div>
                    </div>
                </div>
                <div class="level-item">
                    <div class="level-color" style="background: #bfdbfe;"></div>
                    <div class="level-info">
                        <div class="level-name">Elite</div>
                        <div class="level-desc">Nivel competitivo</div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e5e5;">
                    <div style="font-size: 12px; color: #666; line-height: 1.6;">
                        <strong>Estándares basados en:</strong><br>
                        ExRx.net y Symmetric Strength. Se calcula tu 1RM (una repetición máxima) dividido por tu peso corporal y se compara con datos de miles de atletas.
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
                        <div class="muscle-level-item">
                            <div>
                                <div class="muscle-name"><?php echo htmlspecialchars($musculo); ?></div>
                                <div class="muscle-exercise"><?php echo htmlspecialchars($dato['ejercicio']); ?></div>
                            </div>
                            <div class="muscle-stats">
                                <div class="muscle-weight">
                                    <?php
                                    if ($dato['peso_max'] > 0) {
                                        echo $dato['peso_max'] . ' kg × ' . $dato['reps_max'] . ' reps';
                                    } else {
                                        echo $dato['reps_max'] . ' reps (bodyweight)';
                                    }
                                    ?>
                                </div>
                                <?php
                                    $nivel_calculado = calcularNivelMusculo($musculo, $dato['peso_max'], $dato['reps_max'], $peso_usuario);
                                ?>
                                <div class="level-badge level-<?php echo $nivel_calculado; ?>">Nivel <?php echo $nivel_calculado; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const nivelPorMusculo = <?php
            $niveles_js = [];
            foreach ($niveles_por_musculo as $musculo => $dato) {
                $niveles_js[$musculo] = calcularNivelMusculo($musculo, $dato['peso_max'], $dato['reps_max'], $peso_usuario);
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
                1: 'Principiante',
                2: 'Novato',
                3: 'Intermedio',
                4: 'Avanzado',
                5: 'Élite',
                6: 'Maestro'
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
