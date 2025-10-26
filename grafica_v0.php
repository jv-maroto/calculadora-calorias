<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso - Calculadora de Calorías</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">

    <style>
        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-value.positive {
            color: #16a34a;
        }

        .stat-value.negative {
            color: #dc2626;
        }

        .period-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .period-btn:hover {
            border-color: #6366f1;
            color: #6366f1;
        }

        .period-btn.active {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }
    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index_v0_design.php" class="navbar-brand-modern">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <a href="index_v0_design.php" title="Calculadora">🧮</a>
            <a href="reverse_diet_v0.php" title="Reverse Diet">🔄</a>
            <a href="rutinas_v0.php" title="Rutinas">🏋️</a>
            <a href="introducir_peso_v0.php" title="Registrar Peso">⚖️</a>
            <a href="grafica_v0.php" title="Progreso" style="color: #6366f1;">📊</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Card de gráfica -->
        <div class="v0-card">
            <div class="v0-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="trending-up" style="color: #6366f1; width: 24px; height: 24px;"></i>
                    <div>
                        <h3>Evolución de Peso</h3>
                        <p>Visualiza tu progreso en el tiempo</p>
                    </div>
                </div>

                <!-- Botones de período -->
                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                    <button class="period-btn" onclick="cargarGrafica(7, event)">7d</button>
                    <button class="period-btn" onclick="cargarGrafica(30, event)">30d</button>
                    <button class="period-btn active" onclick="cargarGrafica(90, event)">90d</button>
                    <button class="period-btn" onclick="cargarGrafica(365, event)">1 año</button>
                </div>
            </div>
            <div class="v0-card-body">
                <div style="position: relative; height: 400px;">
                    <canvas id="grafica-peso"></canvas>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid-4 mt-3">
            <div class="v0-card stat-card">
                <i data-lucide="activity" style="width: 32px; height: 32px; color: #6366f1; margin: 0 auto 0.5rem;"></i>
                <div class="stat-label">Peso Actual</div>
                <div class="stat-value" id="peso-actual">-- kg</div>
            </div>

            <div class="v0-card stat-card">
                <i data-lucide="flag" style="width: 32px; height: 32px; color: #64748b; margin: 0 auto 0.5rem;"></i>
                <div class="stat-label">Peso Inicial</div>
                <div class="stat-value" id="peso-inicial">-- kg</div>
            </div>

            <div class="v0-card stat-card">
                <i data-lucide="trending-down" style="width: 32px; height: 32px; color: #10b981; margin: 0 auto 0.5rem;"></i>
                <div class="stat-label">Cambio Total</div>
                <div class="stat-value" id="cambio-total">-- kg</div>
            </div>

            <div class="v0-card stat-card">
                <i data-lucide="calendar" style="width: 32px; height: 32px; color: #f59e0b; margin: 0 auto 0.5rem;"></i>
                <div class="stat-label">Promedio Semanal</div>
                <div class="stat-value" id="promedio-semanal">-- kg/sem</div>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="v0-card mt-3">
            <div class="v0-card-header">
                <i data-lucide="table" style="color: #6366f1; width: 24px; height: 24px;"></i>
                <div>
                    <h3>Historial Completo</h3>
                    <p>Todos tus registros de peso</p>
                </div>
            </div>
            <div class="v0-card-body">
                <div style="overflow-x: auto;">
                    <table class="v0-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Peso (kg)</th>
                                <th>Cambio</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-pesos">
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">
                                    <i data-lucide="loader-2" style="width: 24px; height: 24px; display: inline-block; animation: spin 1s linear infinite;"></i>
                                    Cargando datos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        let grafica = null;
        let diasActuales = 90;

        // Cargar la gráfica al inicio
        window.addEventListener('load', () => {
            cargarGrafica(90);
        });

        async function cargarGrafica(dias, clickEvent = null) {
            diasActuales = dias;

            // Actualizar botones activos
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Si hay un evento de click, marcar el botón como activo
            if (clickEvent && clickEvent.target) {
                clickEvent.target.classList.add('active');
            } else {
                // Si no hay evento (carga inicial), marcar el botón de 90d
                const botones = document.querySelectorAll('.period-btn');
                if (dias === 7) botones[0].classList.add('active');
                else if (dias === 30) botones[1].classList.add('active');
                else if (dias === 90) botones[2].classList.add('active');
                else if (dias === 365) botones[3].classList.add('active');
            }

            try {
                const response = await fetch(`api_peso.php?action=obtener_pesos&dias=${dias}`);
                const resultado = await response.json();

                if (resultado.success && resultado.data && resultado.data.length > 0) {
                    const datos = resultado.data;

                    // Preparar datos para la gráfica
                    const fechas = datos.map(d => {
                        const fecha = new Date(d.fecha + 'T00:00:00');
                        return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
                    });
                    const pesos = datos.map(d => parseFloat(d.peso));

                    // Crear o actualizar gráfica
                    if (grafica) {
                        grafica.destroy();
                    }

                    const ctx = document.getElementById('grafica-peso').getContext('2d');
                    grafica = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: fechas,
                            datasets: [{
                                label: 'Peso (kg)',
                                data: pesos,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 5,
                                pointBackgroundColor: '#6366f1',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 14,
                                            weight: 600
                                        },
                                        color: '#334155',
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    borderColor: '#6366f1',
                                    borderWidth: 2,
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            return ' Peso: ' + context.parsed.y.toFixed(1) + ' kg';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: 500
                                        },
                                        color: '#64748b',
                                        callback: function(value) {
                                            return value.toFixed(1) + ' kg';
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            weight: 500
                                        },
                                        color: '#64748b'
                                    }
                                }
                            }
                        }
                    });

                    // Actualizar estadísticas
                    actualizarEstadisticas(datos);

                    // Actualizar tabla
                    actualizarTabla(datos);
                } else {
                    const container = document.getElementById('grafica-peso').parentElement;
                    container.innerHTML = `
                        <div style="text-align: center; padding: 4rem 2rem;">
                            <i data-lucide="inbox" style="width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748b; font-size: 1.125rem; margin-bottom: 0.5rem;">No hay datos para mostrar</p>
                            <a href="introducir_peso_v0.php" class="v0-btn v0-btn-primary" style="margin-top: 1rem;">
                                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                                Añadir primer registro
                            </a>
                        </div>
                    `;
                    lucide.createIcons();

                    // También actualizar la tabla
                    document.getElementById('tabla-pesos').innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">
                                No hay registros todavía. <a href="introducir_peso_v0.php" style="color: #6366f1;">¡Añade tu primer peso!</a>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('grafica-peso').parentElement.innerHTML =
                    '<p style="text-align: center; color: #ef4444; padding: 2rem;">Error al cargar los datos</p>';
            }
        }

        function actualizarEstadisticas(datos) {
            if (datos.length === 0) return;

            const pesoActual = parseFloat(datos[datos.length - 1].peso);
            const pesoInicial = parseFloat(datos[0].peso);
            const cambioTotal = pesoActual - pesoInicial;

            // Calcular promedio semanal
            const primerRegistro = new Date(datos[0].fecha + 'T00:00:00');
            const ultimoRegistro = new Date(datos[datos.length - 1].fecha + 'T00:00:00');
            const diasTranscurridos = (ultimoRegistro - primerRegistro) / (1000 * 60 * 60 * 24);
            const semanasTranscurridas = diasTranscurridos / 7;
            const promedioSemanal = semanasTranscurridas > 0 ? cambioTotal / semanasTranscurridas : 0;

            // Actualizar valores
            document.getElementById('peso-actual').textContent = pesoActual.toFixed(1) + ' kg';
            document.getElementById('peso-inicial').textContent = pesoInicial.toFixed(1) + ' kg';

            const cambioElement = document.getElementById('cambio-total');
            cambioElement.textContent = (cambioTotal >= 0 ? '+' : '') + cambioTotal.toFixed(1) + ' kg';
            cambioElement.className = 'stat-value ' + (cambioTotal < 0 ? 'positive' : 'negative');

            const promedioElement = document.getElementById('promedio-semanal');
            promedioElement.textContent = (promedioSemanal >= 0 ? '+' : '') + promedioSemanal.toFixed(2) + ' kg/sem';
            promedioElement.className = 'stat-value ' + (promedioSemanal < 0 ? 'positive' : 'negative');

            // Re-inicializar iconos
            lucide.createIcons();
        }

        function actualizarTabla(datos) {
            const tbody = document.getElementById('tabla-pesos');

            if (datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 2rem; color: #64748b;">No hay registros</td></tr>';
                return;
            }

            let html = '';

            // Mostrar en orden inverso (más reciente primero)
            const datosReverso = [...datos].reverse();

            datosReverso.forEach((registro, index) => {
                const fecha = new Date(registro.fecha + 'T00:00:00');
                const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                let cambio = '--';
                let claseCambio = '';

                if (index < datosReverso.length - 1) {
                    const pesoActual = parseFloat(registro.peso);
                    const pesoAnterior = parseFloat(datosReverso[index + 1].peso);
                    const diferencia = pesoActual - pesoAnterior;

                    const icono = diferencia < 0 ? '↓' : '↑';
                    cambio = `${icono} ${(diferencia >= 0 ? '+' : '')}${diferencia.toFixed(1)} kg`;
                    claseCambio = diferencia < 0 ? 'style="color: #16a34a; font-weight: 600;"' : 'style="color: #dc2626; font-weight: 600;"';
                }

                html += `
                    <tr>
                        <td style="text-transform: capitalize;">${fechaFormateada}</td>
                        <td><strong>${parseFloat(registro.peso).toFixed(1)} kg</strong></td>
                        <td ${claseCambio}>${cambio}</td>
                        <td style="color: #64748b;">${registro.notas || '<span style="color: #cbd5e1;">Sin notas</span>'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }
    </script>
</body>
</html>
