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
    die("No hay rutina activa.");
}

// Obtener todos los ejercicios
$sql_ejercicios = "SELECT DISTINCT e.*, d.tipo as dia_tipo
                  FROM ejercicios e
                  JOIN dias_entrenamiento d ON e.dia_id = d.id
                  WHERE d.rutina_id = ?
                  ORDER BY d.tipo, e.orden, e.nombre";
$stmt = $conn->prepare($sql_ejercicios);
$stmt->bind_param("i", $rutina['id']);
$stmt->execute();
$ejercicios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Progreso - Rutinas</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="styles.css">

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

        .exercise-selector {
            margin-bottom: 1.5rem;
        }

        .exercise-group-title {
            font-size: 11px;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 1rem 0 0.5rem 0;
        }

        .exercise-group-title:first-child {
            margin-top: 0;
        }

        .exercise-btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            border: 1px solid #e5e5e5;
            background: white;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .exercise-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        .exercise-btn.active {
            border-color: #1a1a1a;
            background: #1a1a1a;
            color: white;
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

        .progress-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            font-size: 12px;
            font-weight: 600;
            color: #666;
        }

        .progress-badge.up {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .progress-badge.down {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 1rem;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1rem;
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

        @media (max-width: 768px) {
            body {
                padding-bottom: 100px;
            }

            .v0-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }

            .exercise-selector {
                margin-bottom: 1rem;
            }

            select {
                font-size: 14px;
                padding: 0.6rem 2rem 0.6rem 0.75rem;
            }

            canvas {
                max-height: 250px !important;
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

            .btn {
                padding: 0.6rem 1rem;
                font-size: 13px;
            }

            .mobile-back-btn {
                display: inline-block !important;
            }
        }

        @media (min-width: 769px) {
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
            <a href="rutinas.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Rutinas</a>
            <a href="analisis_progreso.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Progreso</a>
            <a href="gestionar_ejercicios.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Ejercicios</a>
            <a href="volumen_semanal.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Volumen</a>
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
        <a href="rutinas.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Rutinas</div>
        </a>
        <a href="analisis_progreso.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #1a1a1a; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Progreso</div>
        </a>
    </nav>

    <style>
        @media (min-width: 768px) {
            body {
                padding-bottom: 2rem !important;
            }
            nav:first-of-type {
                display: flex !important;
            }
            nav:nth-of-type(2) {
                display: none !important;
            }
        }
    </style>

    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Botón volver - Solo móvil -->
        <a href="dashboard.php" class="mobile-back-btn" style="display: inline-block; padding: 0.75rem 1.5rem; background: white; border: 1px solid #e5e5e5; color: #666; text-decoration: none; font-size: 0.875rem; font-weight: 600; margin: 1rem 0;">
            ← Volver
        </a>

        <!-- Selector de ejercicio -->
        <div class="v0-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div class="section-title" style="margin-bottom: 0;">Selecciona un ejercicio</div>
                <a href="progreso_muscular.php" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 8px 16px; border: 1px solid #e5e5e5; background: white; color: #666; font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.15s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Progreso Muscular
                </a>
            </div>
            <div class="exercise-selector" id="exercise-selector">
                <?php
                $tipo_actual = '';
                foreach ($ejercicios as $ejercicio):
                    if ($tipo_actual != $ejercicio['dia_tipo']) {
                        if ($tipo_actual != '') echo '</div>';
                        $tipo_actual = $ejercicio['dia_tipo'];
                        echo "<div class='exercise-group-title'>{$tipo_actual}</div>";
                        echo "<div style='display: flex; flex-wrap: wrap;'>";
                    }
                ?>
                    <button class="exercise-btn" onclick="cargarAnalisis(<?php echo $ejercicio['id']; ?>, '<?php echo htmlspecialchars($ejercicio['nombre']); ?>')">
                        <?php echo htmlspecialchars($ejercicio['nombre']); ?>
                    </button>
                <?php
                endforeach;
                if ($tipo_actual != '') echo '</div>';
                ?>
            </div>
        </div>

        <!-- Contenedor de análisis (se carga dinámicamente) -->
        <div id="analisis-container"></div>

    </div>

    <?php $conn->close(); ?>

    <script>
        lucide.createIcons();

        let chartPeso = null;
        let chartVolumen = null;
        let chartReps = null;

        async function cargarAnalisis(ejercicioId, ejercicioNombre) {
            // Marcar botón activo
            document.querySelectorAll('.exercise-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Mostrar loading
            document.getElementById('analisis-container').innerHTML = '<div class="v0-card" style="text-align: center; padding: 3rem;"><div style="font-size: 2rem;">⏳</div><p>Cargando análisis...</p></div>';

            // Obtener datos
            const response = await fetch(`api_analisis_ejercicio.php?ejercicio_id=${ejercicioId}`);
            const data = await response.json();

            if (!data.success) {
                document.getElementById('analisis-container').innerHTML = '<div class="v0-card"><p style="color: #ef4444;">Error al cargar datos</p></div>';
                return;
            }

            // Renderizar análisis
            renderizarAnalisis(data.datos, ejercicioNombre);
        }

        function renderizarAnalisis(datos, ejercicioNombre) {
            const container = document.getElementById('analisis-container');
            const usaPeso = datos.usa_peso;

            // Calcular estadísticas
            const pesoMax = datos.historico.reduce((max, reg) => Math.max(max, reg.peso), 0);
            const repsMax = datos.historico.reduce((max, reg) => Math.max(max, reg.reps), 0);
            const repsTotal = datos.historico.reduce((sum, reg) => sum + reg.reps, 0);
            const volumenTotal = datos.historico.reduce((sum, reg) => sum + (reg.peso * reg.reps), 0);

            // Calcular tendencia (últimas 4 semanas vs anteriores 4 semanas)
            const tendencia = calcularTendencia(datos.historico, usaPeso);

            // Generar tarjetas de estadísticas según el tipo de ejercicio
            let statsHTML = '';
            if (usaPeso) {
                statsHTML = `
                    <div class="stat-card">
                        <div class="stat-value">${pesoMax} kg</div>
                        <div class="stat-label">Peso Máximo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${repsMax}</div>
                        <div class="stat-label">Reps Máximas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${Math.round(volumenTotal).toLocaleString()}</div>
                        <div class="stat-label">Volumen Total (kg)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${datos.por_sesion.length}</div>
                        <div class="stat-label">Sesiones</div>
                    </div>
                `;
            } else {
                // Para ejercicios sin peso (bodyweight)
                statsHTML = `
                    <div class="stat-card">
                        <div class="stat-value">${repsMax}</div>
                        <div class="stat-label">Reps Máximas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${repsTotal.toLocaleString()}</div>
                        <div class="stat-label">Reps Totales</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${Math.round(repsTotal / datos.por_sesion.length)}</div>
                        <div class="stat-label">Reps por Sesión</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${datos.por_sesion.length}</div>
                        <div class="stat-label">Sesiones</div>
                    </div>
                `;
            }

            // Generar gráficas según el tipo de ejercicio
            let graficasHTML = '';
            if (usaPeso) {
                graficasHTML = `
                    <div>
                        <div class="section-title">Evolución de Peso Máximo</div>
                        <div class="chart-container">
                            <canvas id="chartPeso"></canvas>
                        </div>
                    </div>
                    <div>
                        <div class="section-title">Volumen por Sesión</div>
                        <div class="chart-container">
                            <canvas id="chartVolumen"></canvas>
                        </div>
                    </div>
                    <div>
                        <div class="section-title">Repeticiones Promedio</div>
                        <div class="chart-container">
                            <canvas id="chartReps"></canvas>
                        </div>
                    </div>
                `;
            } else {
                graficasHTML = `
                    <div>
                        <div class="section-title">Evolución de Reps Máximas</div>
                        <div class="chart-container">
                            <canvas id="chartRepsMax"></canvas>
                        </div>
                    </div>
                    <div>
                        <div class="section-title">Total de Reps por Sesión</div>
                        <div class="chart-container">
                            <canvas id="chartRepsTotal"></canvas>
                        </div>
                    </div>
                    <div>
                        <div class="section-title">Sets por Sesión</div>
                        <div class="chart-container">
                            <canvas id="chartSets"></canvas>
                        </div>
                    </div>
                `;
            }

            const html = `
                <div class="v0-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                        <div class="section-title" style="margin-bottom: 0;">${ejercicioNombre}</div>
                        ${tendencia.html}
                    </div>

                    <!-- Estadísticas principales -->
                    <div class="stats-grid">
                        ${statsHTML}
                    </div>

                    <!-- Gráficas -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                        ${graficasHTML}
                    </div>

                    <!-- Historial detallado -->
                    <div style="margin-top: 2rem;">
                        <div class="section-title">Historial Reciente</div>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Sets</th>
                                        <th>Mejor Serie</th>
                                        <th>Volumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${generarHistorialHTML(datos.por_sesion.slice(0, 10), usaPeso)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = html;
            lucide.createIcons();

            // Renderizar gráficas
            renderizarGraficas(datos);
        }

        function calcularTendencia(historico, usaPeso) {
            if (historico.length < 8) {
                return { html: '' };
            }

            // Agrupar por fecha
            const porFecha = {};
            historico.forEach(reg => {
                if (!porFecha[reg.fecha]) {
                    porFecha[reg.fecha] = [];
                }
                porFecha[reg.fecha].push(reg);
            });

            const fechas = Object.keys(porFecha).sort().reverse();

            // Últimas 4 sesiones
            const ultimasCuatro = fechas.slice(0, Math.min(4, fechas.length));
            const anterioresCuatro = fechas.slice(4, Math.min(8, fechas.length));

            if (anterioresCuatro.length === 0) {
                return { html: '' };
            }

            let metricaUltimas, metricaAnteriores;

            if (usaPeso) {
                // Calcular peso promedio en cada período
                metricaUltimas = ultimasCuatro.reduce((sum, fecha) => {
                    const maxPeso = Math.max(...porFecha[fecha].map(r => r.peso));
                    return sum + maxPeso;
                }, 0) / ultimasCuatro.length;

                metricaAnteriores = anterioresCuatro.reduce((sum, fecha) => {
                    const maxPeso = Math.max(...porFecha[fecha].map(r => r.peso));
                    return sum + maxPeso;
                }, 0) / anterioresCuatro.length;
            } else {
                // Para bodyweight, calcular reps máximas promedio
                metricaUltimas = ultimasCuatro.reduce((sum, fecha) => {
                    const maxReps = Math.max(...porFecha[fecha].map(r => r.reps));
                    return sum + maxReps;
                }, 0) / ultimasCuatro.length;

                metricaAnteriores = anterioresCuatro.reduce((sum, fecha) => {
                    const maxReps = Math.max(...porFecha[fecha].map(r => r.reps));
                    return sum + maxReps;
                }, 0) / anterioresCuatro.length;
            }

            const diferencia = ((metricaUltimas - metricaAnteriores) / metricaAnteriores) * 100;

            let clase, icono, texto;
            if (diferencia > 2) {
                clase = 'up';
                icono = '↗';
                texto = `+${diferencia.toFixed(1)}%`;
            } else if (diferencia < -2) {
                clase = 'down';
                icono = '↘';
                texto = `${diferencia.toFixed(1)}%`;
            } else {
                clase = '';
                icono = '→';
                texto = 'Estable';
            }

            return {
                html: `<span class="progress-badge ${clase}">${icono} ${texto}</span>`
            };
        }

        function generarHistorialHTML(sesiones, usaPeso) {
            return sesiones.map(sesion => {
                const fecha = new Date(sesion.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
                const mejorSerie = sesion.registros.reduce((mejor, reg) => {
                    const metrica = usaPeso ? (reg.peso * reg.reps) : reg.reps;
                    const mejorMetrica = usaPeso ? (mejor.peso * mejor.reps) : mejor.reps;
                    return metrica > mejorMetrica ? reg : mejor;
                });
                const volumenTotal = sesion.registros.reduce((sum, reg) => sum + (reg.peso * reg.reps), 0);

                let mejorSerieText = '';
                let volumenText = '';

                if (usaPeso) {
                    mejorSerieText = `${mejorSerie.peso} kg × ${mejorSerie.reps}`;
                    volumenText = `${Math.round(volumenTotal)} kg`;
                } else {
                    mejorSerieText = `${mejorSerie.reps} reps`;
                    const totalReps = sesion.registros.reduce((sum, reg) => sum + reg.reps, 0);
                    volumenText = `${totalReps} reps`;
                }

                return `
                    <tr>
                        <td>${fecha}</td>
                        <td>${sesion.registros.length}</td>
                        <td><strong>${mejorSerieText}</strong></td>
                        <td>${volumenText}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderizarGraficas(datos) {
            const fechas = datos.por_sesion.map(s => new Date(s.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })).reverse();
            const usaPeso = datos.usa_peso;

            // Destruir gráficas anteriores
            if (chartPeso) chartPeso.destroy();
            if (chartVolumen) chartVolumen.destroy();
            if (chartReps) chartReps.destroy();

            if (usaPeso) {
                // EJERCICIOS CON PESO
                // Peso máximo por sesión
                const pesosPorSesion = datos.por_sesion.map(s =>
                    Math.max(...s.registros.map(r => r.peso))
                ).reverse();

                // Volumen por sesión
                const volumenPorSesion = datos.por_sesion.map(s =>
                    s.registros.reduce((sum, r) => sum + (r.peso * r.reps), 0)
                ).reverse();

                // Reps promedio por sesión
                const repsPorSesion = datos.por_sesion.map(s => {
                    const totalReps = s.registros.reduce((sum, r) => sum + r.reps, 0);
                    return (totalReps / s.registros.length).toFixed(1);
                }).reverse();

            // Gráfica de peso
            const ctxPeso = document.getElementById('chartPeso').getContext('2d');
            chartPeso = new Chart(ctxPeso, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Peso (kg)',
                        data: pesosPorSesion,
                        borderColor: '#1a1a1a',
                        backgroundColor: 'rgba(26, 26, 26, 0.05)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });

            // Gráfica de volumen
            const ctxVolumen = document.getElementById('chartVolumen').getContext('2d');
            chartVolumen = new Chart(ctxVolumen, {
                type: 'bar',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Volumen (kg)',
                        data: volumenPorSesion,
                        backgroundColor: '#1a1a1a',
                        borderColor: '#1a1a1a',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

                // Gráfica de reps
                const ctxReps = document.getElementById('chartReps').getContext('2d');
                chartReps = new Chart(ctxReps, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Reps promedio',
                            data: repsPorSesion,
                            borderColor: '#1a1a1a',
                            backgroundColor: 'rgba(26, 26, 26, 0.05)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: false }
                        }
                    }
                });
            } else {
                // EJERCICIOS SIN PESO (BODYWEIGHT)
                // Reps máximas por sesión
                const repsMaxPorSesion = datos.por_sesion.map(s =>
                    Math.max(...s.registros.map(r => r.reps))
                ).reverse();

                // Total de reps por sesión
                const repsTotalPorSesion = datos.por_sesion.map(s =>
                    s.registros.reduce((sum, r) => sum + r.reps, 0)
                ).reverse();

                // Sets por sesión
                const setsPorSesion = datos.por_sesion.map(s =>
                    s.registros.length
                ).reverse();

                // Gráfica de reps máximas
                const ctxRepsMax = document.getElementById('chartRepsMax').getContext('2d');
                chartPeso = new Chart(ctxRepsMax, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Reps Máximas',
                            data: repsMaxPorSesion,
                            borderColor: '#1a1a1a',
                            backgroundColor: 'rgba(26, 26, 26, 0.05)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: false }
                        }
                    }
                });

                // Gráfica de total de reps
                const ctxRepsTotal = document.getElementById('chartRepsTotal').getContext('2d');
                chartVolumen = new Chart(ctxRepsTotal, {
                    type: 'bar',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Total Reps',
                            data: repsTotalPorSesion,
                            backgroundColor: '#1a1a1a',
                            borderColor: '#1a1a1a',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });

                // Gráfica de sets
                const ctxSets = document.getElementById('chartSets').getContext('2d');
                chartReps = new Chart(ctxSets, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Sets',
                            data: setsPorSesion,
                            borderColor: '#1a1a1a',
                            backgroundColor: 'rgba(26, 26, 26, 0.05)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>
