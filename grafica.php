<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Progreso - Calculadora de Calorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">💪 Calculadora de Calorías</a>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link active" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <h3 class="mb-0">📈 Evolución de Peso</h3>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-light" onclick="cargarGrafica(7, event)">7d</button>
                                <button class="btn btn-light" onclick="cargarGrafica(30, event)">30d</button>
                                <button class="btn btn-light active" onclick="cargarGrafica(90, event)">90d</button>
                                <button class="btn btn-light" onclick="cargarGrafica(365, event)">1a</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="grafica-peso" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 g-2">
            <!-- Estadísticas -->
            <div class="col-6 col-md-3">
                <div class="card shadow">
                    <div class="card-body text-center p-2 p-md-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;">Peso Actual</h6>
                        <h3 id="peso-actual" class="mb-0" style="font-size: 1.3rem;">-- kg</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow">
                    <div class="card-body text-center p-2 p-md-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;">Peso Inicial</h6>
                        <h3 id="peso-inicial" class="mb-0" style="font-size: 1.3rem;">-- kg</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow">
                    <div class="card-body text-center p-2 p-md-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;">Cambio Total</h6>
                        <h3 id="cambio-total" class="mb-0" style="font-size: 1.3rem;">-- kg</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow">
                    <div class="card-body text-center p-2 p-md-3">
                        <h6 class="text-muted mb-1" style="font-size: 0.8rem;">Prom. Semanal</h6>
                        <h3 id="promedio-semanal" class="mb-0" style="font-size: 1.3rem;">-- kg/sem</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0">📊 Tabla de Registros</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
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
                                        <td colspan="4" class="text-center text-muted">Cargando datos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let grafica = null;
        let diasActuales = 90;

        // Cargar la gráfica al inicio
        window.addEventListener('load', () => {
            cargarGrafica(90);
        });

        async function cargarGrafica(dias, clickEvent = null) {
            diasActuales = dias;

            // Actualizar botones activos
            document.querySelectorAll('.btn-group button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Si hay un evento de click, marcar el botón como activo
            if (clickEvent && clickEvent.target) {
                clickEvent.target.classList.add('active');
            } else {
                // Si no hay evento (carga inicial), marcar el botón correspondiente
                const botones = document.querySelectorAll('.btn-group button');
                if (dias === 7) botones[0].classList.add('active');
                else if (dias === 30) botones[1].classList.add('active');
                else if (dias === 90) botones[2].classList.add('active');
                else if (dias === 365) botones[3].classList.add('active');
            }

            try {
                const response = await fetch(`api_peso.php?action=obtener_pesos&dias=${dias}`);
                const resultado = await response.json();

                console.log('Respuesta completa:', resultado);
                console.log('Datos recibidos:', resultado.data);

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
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.parsed.y.toFixed(1) + ' kg';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' kg';
                                        }
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
                    console.log('No hay datos o error:', resultado);
                    const container = document.getElementById('grafica-peso').parentElement;
                    container.innerHTML = '<p class="text-center text-muted py-5">No hay datos para mostrar. <a href="introducir_peso.php">¡Añade tu primer registro!</a></p>';

                    // También actualizar la tabla
                    document.getElementById('tabla-pesos').innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay registros todavía</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('grafica-peso').parentElement.innerHTML =
                    '<p class="text-center text-danger py-5">Error al cargar los datos</p>';
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

            document.getElementById('peso-actual').textContent = pesoActual.toFixed(1) + ' kg';
            document.getElementById('peso-inicial').textContent = pesoInicial.toFixed(1) + ' kg';

            const cambioElement = document.getElementById('cambio-total');
            cambioElement.textContent = (cambioTotal >= 0 ? '+' : '') + cambioTotal.toFixed(1) + ' kg';
            cambioElement.className = cambioTotal < 0 ? 'mb-0 text-success' : 'mb-0 text-warning';

            const promedioElement = document.getElementById('promedio-semanal');
            promedioElement.textContent = (promedioSemanal >= 0 ? '+' : '') + promedioSemanal.toFixed(2) + ' kg/sem';
            promedioElement.className = promedioSemanal < 0 ? 'mb-0 text-success' : 'mb-0 text-warning';
        }

        function actualizarTabla(datos) {
            const tbody = document.getElementById('tabla-pesos');

            if (datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay registros</td></tr>';
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
                let colorCambio = '';

                if (index < datosReverso.length - 1) {
                    const pesoActual = parseFloat(registro.peso);
                    const pesoAnterior = parseFloat(datosReverso[index + 1].peso);
                    const diferencia = pesoActual - pesoAnterior;

                    cambio = (diferencia >= 0 ? '+' : '') + diferencia.toFixed(1) + ' kg';
                    colorCambio = diferencia < 0 ? 'text-success' : 'text-warning';
                }

                html += `
                    <tr>
                        <td>${fechaFormateada}</td>
                        <td><strong>${parseFloat(registro.peso).toFixed(1)} kg</strong></td>
                        <td class="${colorCambio}">${cambio}</td>
                        <td>${registro.notas || '<span class="text-muted">Sin notas</span>'}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }
    </script>
</body>
</html>
