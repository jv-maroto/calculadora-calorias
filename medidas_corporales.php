<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medidas Corporales</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #1a1a1a;
            --border: #e5e5e5;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-600: #666;
            --gray-900: #1a1a1a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-50);
            padding-bottom: 80px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .card-header p {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--gray-600);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.15s;
        }

        .tab.active {
            color: var(--gray-900);
            border-bottom-color: var(--gray-900);
        }

        .tab:hover {
            color: var(--gray-900);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        input, textarea {
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border);
            font-size: 0.9375rem;
            transition: all 0.15s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--gray-900);
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: 1px solid var(--border);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--gray-900);
            color: white;
            border-color: var(--gray-900);
        }

        .btn-primary:hover {
            background: #000;
        }

        .btn-secondary {
            background: white;
            color: var(--gray-600);
        }

        .btn-secondary:hover {
            border-color: var(--gray-900);
            color: var(--gray-900);
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .history-item {
            padding: 1rem;
            border: 1px solid var(--border);
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .history-item:hover {
            border-color: var(--gray-900);
            background: var(--gray-50);
        }

        .history-date {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .history-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.5rem;
            border: 1px solid var(--border);
            background: white;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: #16a34a;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        .percentile-bar {
            position: relative;
            height: 8px;
            background: var(--gray-100);
            margin: 0.75rem 0;
        }

        .percentile-fill {
            position: absolute;
            height: 100%;
            background: linear-gradient(to right, #ef4444, #f59e0b, #16a34a);
        }

        .percentile-marker {
            position: absolute;
            width: 3px;
            height: 16px;
            background: var(--gray-900);
            top: -4px;
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 80px !important;
            }

            .container {
                padding: 1rem;
            }

            .card {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .tabs {
                overflow-x: auto;
            }

            .tab {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Nav - Desktop -->
    <nav style="display: none; background: white; border-bottom: 1px solid #e5e5e5; padding: 0 2rem; height: 60px; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; gap: 2rem;">
            <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">← Dashboard</a>
            <a href="diet_hub.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">DIET Hub</a>
            <a href="calculatorkcal.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Calculadora</a>
            <a href="medidas_corporales.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Medidas</a>
        </div>
        <a href="logout.php" style="color: #999; text-decoration: none; font-size: 14px; font-weight: 500;">Salir</a>
    </nav>

    <!-- Bottom Nav - Mobile -->
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e5e5; display: flex; justify-content: space-around; padding: 12px 0; z-index: 100;">
        <a href="dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Inicio</div>
        </a>
        <a href="diet_hub.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>DIET</div>
        </a>
        <a href="calculatorkcal.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Calculadora</div>
        </a>
        <a href="medidas_corporales.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #1a1a1a; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Medidas</div>
        </a>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <i data-lucide="ruler" style="color: #1a1a1a;"></i>
                <div>
                    <h2>Medidas Corporales</h2>
                    <p>Registro y seguimiento de tu composición corporal</p>
                </div>
            </div>

            <div class="tabs">
                <button class="tab active" onclick="cambiarTab('nueva')">Nueva Medición</button>
                <button class="tab" onclick="cambiarTab('historial')">Historial</button>
                <button class="tab" onclick="cambiarTab('estadisticas')">Estadísticas</button>
            </div>

            <!-- Tab: Nueva Medición -->
            <div id="tab-nueva" class="tab-content active">
                <form id="formMedidas">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Fecha</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>

                    <div class="section-title">Datos Básicos</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Peso (kg)</label>
                            <input type="number" step="0.1" name="peso" placeholder="84.5">
                        </div>
                        <div class="form-group">
                            <label>% Grasa Corporal</label>
                            <input type="number" step="0.1" name="porcentaje_grasa" placeholder="15.2">
                        </div>
                    </div>

                    <div class="section-title">Circunferencias (cm)</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Cuello</label>
                            <input type="number" step="0.1" name="cuello" placeholder="38.0">
                        </div>
                        <div class="form-group">
                            <label>Hombros</label>
                            <input type="number" step="0.1" name="hombros" placeholder="112.0">
                        </div>
                        <div class="form-group">
                            <label>Pecho</label>
                            <input type="number" step="0.1" name="pecho" placeholder="100.0">
                        </div>
                        <div class="form-group">
                            <label>Brazo Derecho</label>
                            <input type="number" step="0.1" name="brazo_derecho" placeholder="35.0">
                        </div>
                        <div class="form-group">
                            <label>Brazo Izquierdo</label>
                            <input type="number" step="0.1" name="brazo_izquierdo" placeholder="35.0">
                        </div>
                        <div class="form-group">
                            <label>Antebrazo Derecho</label>
                            <input type="number" step="0.1" name="antebrazo_derecho" placeholder="28.0">
                        </div>
                        <div class="form-group">
                            <label>Antebrazo Izquierdo</label>
                            <input type="number" step="0.1" name="antebrazo_izquierdo" placeholder="28.0">
                        </div>
                        <div class="form-group">
                            <label>Cintura</label>
                            <input type="number" step="0.1" name="cintura" placeholder="82.0">
                        </div>
                        <div class="form-group">
                            <label>Cadera</label>
                            <input type="number" step="0.1" name="cadera" placeholder="95.0">
                        </div>
                        <div class="form-group">
                            <label>Muslo Derecho</label>
                            <input type="number" step="0.1" name="muslo_derecho" placeholder="58.0">
                        </div>
                        <div class="form-group">
                            <label>Muslo Izquierdo</label>
                            <input type="number" step="0.1" name="muslo_izquierdo" placeholder="58.0">
                        </div>
                        <div class="form-group">
                            <label>Pantorrilla Derecha</label>
                            <input type="number" step="0.1" name="pantorrilla_derecha" placeholder="38.0">
                        </div>
                        <div class="form-group">
                            <label>Pantorrilla Izquierda</label>
                            <input type="number" step="0.1" name="pantorrilla_izquierda" placeholder="38.0">
                        </div>
                    </div>

                    <div class="section-title">Pliegues Cutáneos (mm) - Opcional</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tríceps</label>
                            <input type="number" step="0.1" name="pliegue_triceps" placeholder="12.0">
                        </div>
                        <div class="form-group">
                            <label>Subescapular</label>
                            <input type="number" step="0.1" name="pliegue_subescapular" placeholder="15.0">
                        </div>
                        <div class="form-group">
                            <label>Suprailiaco</label>
                            <input type="number" step="0.1" name="pliegue_suprailiaco" placeholder="18.0">
                        </div>
                        <div class="form-group">
                            <label>Abdominal</label>
                            <input type="number" step="0.1" name="pliegue_abdominal" placeholder="20.0">
                        </div>
                        <div class="form-group">
                            <label>Muslo</label>
                            <input type="number" step="0.1" name="pliegue_muslo" placeholder="16.0">
                        </div>
                        <div class="form-group">
                            <label>Pectoral</label>
                            <input type="number" step="0.1" name="pliegue_pectoral" placeholder="10.0">
                        </div>
                        <div class="form-group">
                            <label>Axilar</label>
                            <input type="number" step="0.1" name="pliegue_axilar" placeholder="12.0">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label>Notas</label>
                        <textarea name="notas" rows="3" placeholder="Observaciones, cambios en la dieta, entrenamientos..."></textarea>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                            Guardar Medición
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Historial -->
            <div id="tab-historial" class="tab-content">
                <div id="historial-lista">
                    <p style="text-align: center; color: #999; padding: 2rem;">Cargando historial...</p>
                </div>
            </div>

            <!-- Tab: Estadísticas -->
            <div id="tab-estadisticas" class="tab-content">
                <div id="estadisticas-contenido">
                    <p style="text-align: center; color: #999; padding: 2rem;">Cargando estadísticas...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Establecer fecha actual por defecto
        document.getElementById('fecha').valueAsDate = new Date();

        function cambiarTab(tab) {
            // Actualizar tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            event.target.classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');

            // Cargar datos según tab
            if (tab === 'historial') {
                cargarHistorial();
            } else if (tab === 'estadisticas') {
                cargarEstadisticas();
            }

            lucide.createIcons();
        }

        function limpiarFormulario() {
            document.getElementById('formMedidas').reset();
            document.getElementById('fecha').valueAsDate = new Date();
        }

        let chartPeso = null;
        let chartCircunferencias = null;

        async function cargarHistorial() {
            try {
                const response = await fetch('api_medidas.php?action=obtener_historial');
                const result = await response.json();

                if (!result.success || !result.medidas || result.medidas.length === 0) {
                    document.getElementById('historial-lista').innerHTML = `
                        <div style="text-align: center; color: #999; padding: 2rem;">
                            No hay medidas registradas aún. Crea tu primera medición en la pestaña "Nueva Medición".
                        </div>
                    `;
                    return;
                }

                let html = '';
                result.medidas.forEach(medida => {
                    const fecha = new Date(medida.fecha);
                    const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    html += `
                        <div class="history-item" onclick="verDetalleMedida(${medida.id})">
                            <div class="history-date">${fechaFormateada}</div>
                            <div class="history-stats">
                                ${medida.peso ? `<span>Peso: ${medida.peso} kg</span>` : ''}
                                ${medida.porcentaje_grasa ? `<span>Grasa: ${medida.porcentaje_grasa}%</span>` : ''}
                                ${medida.brazo_derecho ? `<span>Brazo: ${medida.brazo_derecho} cm</span>` : ''}
                                ${medida.cintura ? `<span>Cintura: ${medida.cintura} cm</span>` : ''}
                            </div>
                        </div>
                    `;
                });

                document.getElementById('historial-lista').innerHTML = html;
            } catch (error) {
                document.getElementById('historial-lista').innerHTML = `
                    <div style="text-align: center; color: #ef4444; padding: 2rem;">
                        Error al cargar historial
                    </div>
                `;
                console.error(error);
            }
        }

        async function cargarEstadisticas() {
            try {
                const response = await fetch('api_medidas.php?action=estadisticas');
                const result = await response.json();

                if (!result.success || !result.ultima) {
                    document.getElementById('estadisticas-contenido').innerHTML = `
                        <div style="text-align: center; color: #999; padding: 2rem;">
                            No hay suficientes datos para generar estadísticas. Registra al menos 2 mediciones.
                        </div>
                    `;
                    return;
                }

                // Obtener historial completo para gráficas
                const historialResponse = await fetch('api_medidas.php?action=obtener_historial');
                const historialResult = await historialResponse.json();

                renderizarEstadisticas(result, historialResult.medidas || []);
            } catch (error) {
                document.getElementById('estadisticas-contenido').innerHTML = `
                    <div style="text-align: center; color: #ef4444; padding: 2rem;">
                        Error al cargar estadísticas
                    </div>
                `;
                console.error(error);
            }
        }

        function renderizarEstadisticas(stats, historial) {
            const progreso = stats.progreso || {};
            let html = `
                <div class="stats-grid">
            `;

            // Cards de progreso
            const campos = [
                {key: 'peso', label: 'Peso', unidad: 'kg'},
                {key: 'brazo_derecho', label: 'Brazo', unidad: 'cm'},
                {key: 'pecho', label: 'Pecho', unidad: 'cm'},
                {key: 'cintura', label: 'Cintura', unidad: 'cm'},
                {key: 'porcentaje_grasa', label: '% Grasa', unidad: '%'}
            ];

            campos.forEach(campo => {
                if (progreso[campo.key]) {
                    const dato = progreso[campo.key];
                    const diferencia = dato.diferencia;
                    const cambio = diferencia > 0 ? 'positive' : diferencia < 0 ? 'negative' : '';

                    html += `
                        <div class="stat-card">
                            <div class="stat-label">${campo.label}</div>
                            <div class="stat-value">${dato.actual} ${campo.unidad}</div>
                            <div class="stat-change ${cambio}">
                                ${diferencia > 0 ? '↑' : diferencia < 0 ? '↓' : '→'}
                                ${Math.abs(diferencia).toFixed(1)} ${campo.unidad} desde el inicio
                            </div>
                        </div>
                    `;
                }
            });

            html += `</div>`;

            // Gráficas
            html += `
                <h3 style="margin: 2rem 0 1rem 0;">Evolución en el Tiempo</h3>
                <div class="chart-container">
                    <canvas id="chartPeso"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="chartCircunferencias"></canvas>
                </div>
            `;

            document.getElementById('estadisticas-contenido').innerHTML = html;

            // Renderizar gráficas
            crearGraficas(historial);
        }

        function crearGraficas(historial) {
            if (historial.length === 0) return;

            // Ordenar por fecha
            historial.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

            const fechas = historial.map(m => new Date(m.fecha).toLocaleDateString('es-ES', {day: 'numeric', month: 'short'}));

            // Gráfica de Peso
            const ctxPeso = document.getElementById('chartPeso');
            if (chartPeso) chartPeso.destroy();

            chartPeso = new Chart(ctxPeso, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Peso (kg)',
                        data: historial.map(m => m.peso),
                        borderColor: '#1a1a1a',
                        backgroundColor: 'rgba(26, 26, 26, 0.1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {display: true}
                    }
                }
            });

            // Gráfica de Circunferencias
            const ctxCirc = document.getElementById('chartCircunferencias');
            if (chartCircunferencias) chartCircunferencias.destroy();

            chartCircunferencias = new Chart(ctxCirc, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [
                        {
                            label: 'Brazo (cm)',
                            data: historial.map(m => m.brazo_derecho),
                            borderColor: '#3b82f6',
                            tension: 0.3
                        },
                        {
                            label: 'Pecho (cm)',
                            data: historial.map(m => m.pecho),
                            borderColor: '#16a34a',
                            tension: 0.3
                        },
                        {
                            label: 'Cintura (cm)',
                            data: historial.map(m => m.cintura),
                            borderColor: '#ef4444',
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {display: true}
                    }
                }
            });
        }

        function verDetalleMedida(id) {
            // TODO: Modal con detalle completo de la medida
            console.log('Ver medida:', id);
        }

        // Manejo del formulario
        document.getElementById('formMedidas').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const datos = {};

            formData.forEach((value, key) => {
                if (value) datos[key] = value;
            });

            try {
                const response = await fetch('api_medidas.php?action=guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Medición guardada correctamente');
                    limpiarFormulario();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error de conexión');
                console.error(error);
            }
        });

        // Responsive nav
        const mediaQuery = window.matchMedia('(min-width: 769px)');
        function handleNav(e) {
            const desktopNav = document.querySelector('nav:first-of-type');
            const mobileNav = document.querySelector('nav:nth-of-type(2)');
            if (e.matches) {
                desktopNav.style.display = 'flex';
                mobileNav.style.display = 'none';
            } else {
                desktopNav.style.display = 'none';
                mobileNav.style.display = 'flex';
            }
        }
        mediaQuery.addListener(handleNav);
        handleNav(mediaQuery);
    </script>
</body>
</html>
