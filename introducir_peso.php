<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introducir Peso - Calculadora de Calorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">💪 Calculadora de Calorías</a>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link" href="index.php" title="Calculadora Principal">🏠</a>
                <a class="nav-link" href="reverse_diet.php" title="Reverse Diet">🔄</a>
                <a class="nav-link" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link active" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container py-3 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">⚖️ Registrar Peso Diario</h3>
                    </div>
                    <div class="card-body">
                        <div id="mensaje-resultado" class="alert" style="display: none;"></div>

                        <form id="formPeso">
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>

                            <div class="mb-3">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" id="peso" name="peso" step="0.1" min="30" max="300" required>
                            </div>

                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas (opcional)</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Ej: Me sentí bien hoy, día de trampa, etc."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Peso</button>
                                <a href="grafica.php" class="btn btn-outline-secondary">📈 Ver mi progreso</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de pesos recientes -->
                <div class="card shadow-lg mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0">📋 Registros Recientes</h4>
                    </div>
                    <div class="card-body">
                        <div id="historial-pesos">
                            <p class="text-muted text-center">Cargando...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Establecer fecha de hoy por defecto
        document.getElementById('fecha').valueAsDate = new Date();

        // Cargar historial de pesos al cargar la página
        cargarHistorial();

        // Manejar el envío del formulario
        document.getElementById('formPeso').addEventListener('submit', async (e) => {
            e.preventDefault();

            const fecha = document.getElementById('fecha').value;
            const peso = parseFloat(document.getElementById('peso').value);
            const notas = document.getElementById('notas').value;

            try {
                const response = await fetch('api_peso.php?action=guardar_peso', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        fecha: fecha,
                        peso: peso,
                        notas: notas
                    })
                });

                const resultado = await response.json();

                const mensaje = document.getElementById('mensaje-resultado');

                if (resultado.success) {
                    mensaje.className = 'alert alert-success';
                    mensaje.textContent = '✅ Peso guardado correctamente';
                    mensaje.style.display = 'block';

                    // Limpiar notas pero mantener la fecha
                    document.getElementById('notas').value = '';
                    document.getElementById('peso').value = '';

                    // Recargar historial
                    cargarHistorial();

                    // Ocultar mensaje después de 3 segundos
                    setTimeout(() => {
                        mensaje.style.display = 'none';
                    }, 3000);
                } else {
                    mensaje.className = 'alert alert-danger';
                    mensaje.textContent = '❌ Error: ' + resultado.error;
                    mensaje.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                const mensaje = document.getElementById('mensaje-resultado');
                mensaje.className = 'alert alert-danger';
                mensaje.textContent = '❌ Error al guardar el peso';
                mensaje.style.display = 'block';
            }
        });

        // Función para cargar historial
        async function cargarHistorial() {
            try {
                const response = await fetch('api_peso.php?action=obtener_pesos&dias=7');
                const resultado = await response.json();

                const contenedor = document.getElementById('historial-pesos');

                if (resultado.success && resultado.data.length > 0) {
                    let html = '<div class="list-group">';

                    // Ordenar por fecha descendente (más reciente primero)
                    resultado.data.reverse().forEach(registro => {
                        const fechaObj = new Date(registro.fecha + 'T00:00:00');
                        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
                            weekday: 'short',
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        html += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${fechaFormateada}</h6>
                                        <p class="mb-1"><strong>${registro.peso} kg</strong></p>
                                        ${registro.notas ? `<small class="text-muted">${registro.notas}</small>` : ''}
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarPeso(${registro.id})">🗑️</button>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    contenedor.innerHTML = html;
                } else {
                    contenedor.innerHTML = '<p class="text-muted text-center">No hay registros todavía. ¡Añade tu primer peso!</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('historial-pesos').innerHTML = '<p class="text-danger text-center">Error al cargar el historial</p>';
            }
        }

        // Función para eliminar un peso
        async function eliminarPeso(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este registro?')) {
                return;
            }

            try {
                const response = await fetch(`api_peso.php?action=eliminar_peso&id=${id}`, {
                    method: 'DELETE'
                });

                const resultado = await response.json();

                if (resultado.success) {
                    cargarHistorial();
                } else {
                    alert('Error al eliminar: ' + resultado.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el registro');
            }
        }
    </script>
</body>
</html>
