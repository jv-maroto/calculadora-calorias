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

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso - GYM</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Top Nav */
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
        }

        .top-nav a.active {
            color: #1a1a1a;
        }

        /* Header */
        .header {
            padding: 2rem 1rem 1rem;
            max-width: 1200px;
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

        /* Exercise Selector */
        .selector-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }

        .selector-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 0.75rem;
        }

        .exercise-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .exercise-chip {
            border: 1px solid #e5e5e5;
            background: white;
            padding: 0.75rem 1rem;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .exercise-chip:hover {
            border-color: #1a1a1a;
        }

        .exercise-chip.active {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        /* Analysis */
        .analysis-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .analysis-header {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .analysis-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .trend-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            font-size: 12px;
            font-weight: 600;
            background: #f5f5f5;
            color: #666;
        }

        .trend-badge.up {
            background: #1a1a1a;
            color: white;
        }

        .trend-badge.down {
            background: #e5e5e5;
            color: #666;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
        }

        .chart-section {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 1rem;
        }

        .chart-container {
            position: relative;
            height: 250px;
        }

        .history-table {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
        }

        .history-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .history-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        /* Bottom Nav */
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
        }

        .bottom-nav a.active {
            color: #1a1a1a;
        }

        /* Desktop */
        @media (min-width: 768px) {
            body {
                padding-top: 60px;
                padding-bottom: 2rem;
            }

            .top-nav {
                display: flex;
            }

            .bottom-nav {
                display: none;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Nav - Desktop -->
    <nav class="top-nav">
        <div class="top-nav-links">
            <a href="dashboard.php">← Dashboard</a>
            <a href="rutinas.php">Rutinas</a>
            <a href="progreso.php" class="active">Progreso</a>
        </div>
        <a href="logout.php" style="color: #999;">Salir</a>
    </nav>

    <!-- Header -->
    <div class="header">
        <h1>Progreso</h1>
        <p class="header-subtitle">Análisis de ejercicios</p>
    </div>

    <!-- Exercise Selector -->
    <div class="selector-section">
        <div class="selector-label">Selecciona un ejercicio</div>
        <div class="exercise-chips">
            <?php
            $tipo_actual = '';
            foreach ($ejercicios as $ejercicio):
                if ($tipo_actual != $ejercicio['dia_tipo']) {
                    if ($tipo_actual != '') echo '</div><div class="exercise-chips" style="margin-top: 1rem;">';
                    $tipo_actual = $ejercicio['dia_tipo'];
                    echo '<div style="width: 100%; font-size: 11px; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">' . htmlspecialchars($tipo_actual) . '</div>';
                }
            ?>
                <div class="exercise-chip" onclick="cargarAnalisis(<?php echo $ejercicio['id']; ?>, '<?php echo htmlspecialchars($ejercicio['nombre']); ?>')">
                    <?php echo htmlspecialchars($ejercicio['nombre']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Analysis Container -->
    <div id="analisis-container" class="analysis-container">
        <div class="empty-state">
            <div style="font-size: 48px; margin-bottom: 1rem;">📊</div>
            <p>Selecciona un ejercicio para ver su progreso</p>
        </div>
    </div>

    <!-- Bottom Nav - Mobile -->
    <nav class="bottom-nav">
        <a href="dashboard.php">
            <div style="font-size: 20px;">⌂</div>
            <div>Inicio</div>
        </a>
        <a href="rutinas.php">
            <div style="font-size: 20px;">▦</div>
            <div>Rutinas</div>
        </a>
        <a href="progreso.php" class="active">
            <div style="font-size: 20px;">📊</div>
            <div>Progreso</div>
        </a>
    </nav>

    <script>
        let chartPeso = null;
        let chartVolumen = null;
        let chartReps = null;

        async function cargarAnalisis(ejercicioId, ejercicioNombre) {
            // Marcar activo
            document.querySelectorAll('.exercise-chip').forEach(chip => chip.classList.remove('active'));
            event.target.classList.add('active');

            // Loading
            document.getElementById('analisis-container').innerHTML = '<div class="empty-state"><p>Cargando...</p></div>';

            // Fetch data
            const response = await fetch(`api_analisis_ejercicio.php?ejercicio_id=${ejercicioId}`);
            const data = await response.json();

            if (!data.success) {
                document.getElementById('analisis-container').innerHTML = '<div class="empty-state"><p>Error al cargar datos</p></div>';
                return;
            }

            renderizarAnalisis(data.datos, ejercicioNombre);
        }

        function renderizarAnalisis(datos, ejercicioNombre) {
            const container = document.getElementById('analisis-container');
            const usaPeso = datos.usa_peso;

            // Calcular stats
            const pesoMax = datos.historico.reduce((max, reg) => Math.max(max, reg.peso), 0);
            const repsMax = datos.historico.reduce((max, reg) => Math.max(max, reg.reps), 0);
            const repsTotal = datos.historico.reduce((sum, reg) => sum + reg.reps, 0);
            const volumenTotal = datos.historico.reduce((sum, reg) => sum + (reg.peso * reg.reps), 0);

            // Calcular tendencia
            const tendencia = calcularTendencia(datos.historico, usaPeso);

            let statsHTML = '';
            if (usaPeso) {
                statsHTML = `
                    <div class="stat-card">
                        <div class="stat-label">Peso Máximo</div>
                        <div class="stat-value">${pesoMax} kg</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Reps Máximas</div>
                        <div class="stat-value">${repsMax}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Volumen Total</div>
                        <div class="stat-value">${Math.round(volumenTotal).toLocaleString()} kg</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Sesiones</div>
                        <div class="stat-value">${datos.por_sesion.length}</div>
                    </div>
                `;
            } else {
                statsHTML = `
                    <div class="stat-card">
                        <div class="stat-label">Reps Máximas</div>
                        <div class="stat-value">${repsMax}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Reps Totales</div>
                        <div class="stat-value">${repsTotal.toLocaleString()}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Reps/Sesión</div>
                        <div class="stat-value">${Math.round(repsTotal / datos.por_sesion.length)}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Sesiones</div>
                        <div class="stat-value">${datos.por_sesion.length}</div>
                    </div>
                `;
            }

            const html = `
                <div class="analysis-header">
                    <div class="analysis-title">${ejercicioNombre}</div>
                    ${tendencia.html}
                </div>

                <div class="stats-grid">
                    ${statsHTML}
                </div>

                <div class="chart-section">
                    <div class="chart-title">${usaPeso ? 'Evolución de Peso' : 'Evolución de Reps'}</div>
                    <div class="chart-container">
                        <canvas id="chart1"></canvas>
                    </div>
                </div>

                <div class="chart-section">
                    <div class="chart-title">${usaPeso ? 'Volumen por Sesión' : 'Total Reps por Sesión'}</div>
                    <div class="chart-container">
                        <canvas id="chart2"></canvas>
                    </div>
                </div>

                <div class="history-table">
                    <div class="chart-title">Historial (últimas 10 sesiones)</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Sets</th>
                                <th>Mejor</th>
                                ${usaPeso ? '<th>Volumen</th>' : '<th>Total Reps</th>'}
                            </tr>
                        </thead>
                        <tbody>
                            ${generarHistorialHTML(datos.por_sesion.slice(0, 10), usaPeso)}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
            renderizarGraficas(datos);
        }

        function calcularTendencia(historico, usaPeso) {
            if (historico.length < 8) {
                return { html: '' };
            }

            const porFecha = {};
            historico.forEach(reg => {
                if (!porFecha[reg.fecha]) porFecha[reg.fecha] = [];
                porFecha[reg.fecha].push(reg);
            });

            const fechas = Object.keys(porFecha).sort().reverse();
            const ultimasCuatro = fechas.slice(0, Math.min(4, fechas.length));
            const anterioresCuatro = fechas.slice(4, Math.min(8, fechas.length));

            if (anterioresCuatro.length === 0) return { html: '' };

            let metricaUltimas, metricaAnteriores;

            if (usaPeso) {
                metricaUltimas = ultimasCuatro.reduce((sum, fecha) => {
                    return sum + Math.max(...porFecha[fecha].map(r => r.peso));
                }, 0) / ultimasCuatro.length;

                metricaAnteriores = anterioresCuatro.reduce((sum, fecha) => {
                    return sum + Math.max(...porFecha[fecha].map(r => r.peso));
                }, 0) / anterioresCuatro.length;
            } else {
                metricaUltimas = ultimasCuatro.reduce((sum, fecha) => {
                    return sum + Math.max(...porFecha[fecha].map(r => r.reps));
                }, 0) / ultimasCuatro.length;

                metricaAnteriores = anterioresCuatro.reduce((sum, fecha) => {
                    return sum + Math.max(...porFecha[fecha].map(r => r.reps));
                }, 0) / anterioresCuatro.length;
            }

            const diferencia = ((metricaUltimas - metricaAnteriores) / metricaAnteriores) * 100;

            let clase, texto;
            if (diferencia > 2) {
                clase = 'up';
                texto = `↗ +${diferencia.toFixed(1)}%`;
            } else if (diferencia < -2) {
                clase = 'down';
                texto = `↘ ${diferencia.toFixed(1)}%`;
            } else {
                clase = '';
                texto = '→ Estable';
            }

            return {
                html: `<span class="trend-badge ${clase}">${texto}</span>`
            };
        }

        function generarHistorialHTML(sesiones, usaPeso) {
            return sesiones.map(sesion => {
                const fecha = new Date(sesion.fecha).toLocaleDateString('es-ES');
                const mejorSerie = sesion.registros.reduce((mejor, reg) => {
                    const volumen = usaPeso ? (reg.peso * reg.reps) : reg.reps;
                    const mejorVolumen = usaPeso ? (mejor.peso * mejor.reps) : mejor.reps;
                    return volumen > mejorVolumen ? reg : mejor;
                });
                const total = usaPeso
                    ? sesion.registros.reduce((sum, reg) => sum + (reg.peso * reg.reps), 0)
                    : sesion.registros.reduce((sum, reg) => sum + reg.reps, 0);

                return `
                    <tr>
                        <td>${fecha}</td>
                        <td>${sesion.registros.length}</td>
                        <td><strong>${usaPeso ? mejorSerie.peso + ' kg × ' : ''}${mejorSerie.reps}</strong></td>
                        <td>${Math.round(total)}${usaPeso ? ' kg' : ''}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderizarGraficas(datos) {
            const fechas = datos.por_sesion.map(s => new Date(s.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' })).reverse();
            const usaPeso = datos.usa_peso;

            if (chartPeso) chartPeso.destroy();
            if (chartVolumen) chartVolumen.destroy();

            if (usaPeso) {
                const pesos = datos.por_sesion.map(s => Math.max(...s.registros.map(r => r.peso))).reverse();
                const volumenes = datos.por_sesion.map(s => s.registros.reduce((sum, r) => sum + (r.peso * r.reps), 0)).reverse();

                chartPeso = new Chart(document.getElementById('chart1'), {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Peso (kg)',
                            data: pesos,
                            borderColor: '#1a1a1a',
                            backgroundColor: 'rgba(26, 26, 26, 0.05)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: '#1a1a1a'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: false } }
                    }
                });

                chartVolumen = new Chart(document.getElementById('chart2'), {
                    type: 'bar',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Volumen (kg)',
                            data: volumenes,
                            backgroundColor: '#1a1a1a',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            } else {
                const repsMax = datos.por_sesion.map(s => Math.max(...s.registros.map(r => r.reps))).reverse();
                const repsTotal = datos.por_sesion.map(s => s.registros.reduce((sum, r) => sum + r.reps, 0)).reverse();

                chartPeso = new Chart(document.getElementById('chart1'), {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Reps Máximas',
                            data: repsMax,
                            borderColor: '#1a1a1a',
                            backgroundColor: 'rgba(26, 26, 26, 0.05)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: '#1a1a1a'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: false } }
                    }
                });

                chartVolumen = new Chart(document.getElementById('chart2'), {
                    type: 'bar',
                    data: {
                        labels: fechas,
                        datasets: [{
                            label: 'Total Reps',
                            data: repsTotal,
                            backgroundColor: '#1a1a1a',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }
    </script>
</body>
</html>
