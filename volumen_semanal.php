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

// Obtener volumen por grupo muscular
$sql_volumen = "SELECT
                    e.grupo_muscular,
                    SUM(e.sets_recomendados) as total_sets,
                    COUNT(DISTINCT e.dia_id) as dias_por_semana,
                    GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.dia_semana SEPARATOR ', ') as dias,
                    GROUP_CONCAT(DISTINCT e.nombre ORDER BY e.orden SEPARATOR '|') as ejercicios
                FROM ejercicios e
                JOIN dias_entrenamiento d ON e.dia_id = d.id
                WHERE e.grupo_muscular IS NOT NULL
                GROUP BY e.grupo_muscular
                ORDER BY total_sets DESC";

$resultado_volumen = $conn->query($sql_volumen);
$grupos = $resultado_volumen->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volumen Semanal - Gym Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .grupo-card {
            transition: transform 0.2s;
        }
        .grupo-card:hover {
            transform: translateY(-2px);
        }
        .volumen-badge {
            font-size: 1.5rem;
            padding: 0.5rem 1rem;
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
                <a class="nav-link" href="index.php" title="Dashboard Principal">🏠</a>
                <a class="nav-link" href="rutinas.php" title="Rutinas">🏋️</a>
                <a class="nav-link" href="gestionar_ejercicios.php" title="Gestionar Ejercicios">⚙️</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container py-4" style="max-width: 1400px;">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-info text-white">
                        <h3 class="mb-0">📊 Análisis de Volumen Semanal</h3>
                        <small>Distribución de series por grupo muscular</small>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <strong>📖 Guía de Volumen Recomendado (Evidencia Científica 2025):</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Principiantes:</strong> 10-12 series/semana por grupo muscular</li>
                                <li><strong>Intermedios:</strong> 12-18 series/semana por grupo muscular</li>
                                <li><strong>Avanzados:</strong> 18-20 series/semana por grupo muscular</li>
                                <li class="text-danger"><strong>⚠️ Límite:</strong> Más de 20 series/semana produce rendimientos decrecientes y mayor fatiga</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla resumen -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Resumen por Grupo Muscular</h5>
                            <button class="btn btn-info btn-sm" onclick="mostrarModalGlobal()">
                                ⚡ Aumento Global de Series
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Grupo Muscular</th>
                                        <th class="text-center">Series/Semana</th>
                                        <th class="text-center">Frecuencia Semanal</th>
                                        <th class="text-center">Series/Sesión</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grupos as $grupo):
                                        $sets = $grupo['total_sets'];
                                        $frecuencia = $grupo['dias_por_semana'];
                                        $sets_por_sesion = $frecuencia > 0 ? round($sets / $frecuencia, 1) : 0;

                                        // Estado del volumen (basado en evidencia científica)
                                        if ($sets < 10) {
                                            $estado = 'Bajo';
                                            $estado_badge = 'bg-warning text-dark';
                                            $estado_icon = '⚠️';
                                        } elseif ($sets <= 18) {
                                            $estado = 'Óptimo';
                                            $estado_badge = 'bg-success';
                                            $estado_icon = '✅';
                                        } elseif ($sets <= 20) {
                                            $estado = 'Límite Superior';
                                            $estado_badge = 'bg-info';
                                            $estado_icon = '💪';
                                        } else {
                                            $estado = 'Excesivo';
                                            $estado_badge = 'bg-danger';
                                            $estado_icon = '🚨';
                                        }

                                        // Badge de frecuencia
                                        if ($frecuencia == 1) {
                                            $freq_badge = 'bg-warning text-dark';
                                        } elseif ($frecuencia == 2) {
                                            $freq_badge = 'bg-success';
                                        } else {
                                            $freq_badge = 'bg-info';
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grupo['grupo_muscular']); ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary volumen-badge"><?php echo $sets; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $freq_badge; ?>"><?php echo $frecuencia; ?>x/semana</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?php echo $sets_por_sesion; ?> sets</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $estado_badge; ?>"><?php echo $estado_icon; ?> <?php echo $estado; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-success" style="padding: 0.15rem 0.4rem; font-size: 0.75rem;" onclick="mostrarModalAumento('<?php echo htmlspecialchars($grupo['grupo_muscular']); ?>')" title="Aumentar series">
                                                    ➕
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles por grupo -->
        <div class="row g-4">
            <?php foreach ($grupos as $grupo):
                $sets = $grupo['total_sets'];

                // Color según volumen
                if ($sets < 10) {
                    $card_color = 'border-warning';
                } elseif ($sets <= 18) {
                    $card_color = 'border-success';
                } elseif ($sets <= 20) {
                    $card_color = 'border-info';
                } else {
                    $card_color = 'border-danger';
                }
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card grupo-card shadow-sm <?php echo $card_color; ?>" style="border-width: 3px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><?php echo htmlspecialchars($grupo['grupo_muscular']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h2 class="text-primary"><?php echo $sets; ?></h2>
                                <small class="text-muted">series/semana</small>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Frecuencia:</strong> <?php echo $grupo['dias_por_semana']; ?>x por semana<br>
                                <small class="text-muted"><?php echo htmlspecialchars($grupo['dias']); ?></small>
                            </div>
                            <div>
                                <strong>Ejercicios:</strong><br>
                                <small class="text-muted">
                                    <?php
                                    $ejercicios = explode('|', $grupo['ejercicios']);
                                    echo count($ejercicios) . ' ejercicios';
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Info Frecuencia -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">ℹ️ Guía de Frecuencia de Entrenamiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-warning">
                                    <strong>1x/semana:</strong> Frecuencia baja. Puede ser suficiente para mantenimiento pero limitado para hipertrofia. Considera aumentar si buscas crecimiento.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success">
                                    <strong>2x/semana:</strong> Frecuencia óptima para la mayoría de grupos musculares. Permite buen volumen con recuperación adecuada.
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <strong>3+x/semana:</strong> Frecuencia alta. Útil para músculos pequeños o priorización de grupos específicos. Requiere gestión cuidadosa de la fatiga.
                                </div>
                            </div>
                        </div>
                        <p class="mb-0 text-muted">
                            <strong>Recomendación:</strong> Distribuir el volumen en 2 sesiones semanales por grupo muscular suele ser óptimo para hipertrofia, permitiendo mayor frecuencia de estímulo y mejor recuperación.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para aumento individual -->
    <div class="modal fade" id="modalAumento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Aumentar Series</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Aumentar series para: <strong id="grupoSeleccionado"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Cantidad de series a añadir al grupo:</label>
                        <input type="number" class="form-control" id="cantidadSeries" min="1" max="8" value="2">
                        <small class="text-muted">Se distribuirá inteligentemente entre los ejercicios del grupo</small>
                    </div>
                    <div class="alert alert-warning" id="alertVolumenAlto" style="display:none;">
                        <strong>⚠️ Advertencia:</strong> Este grupo ya tiene un volumen alto. Aumentar más podría generar fatiga excesiva.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarAumento()">Aumentar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para aumento global -->
    <div class="modal fade" id="modalGlobal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">⚡ Aumento Global de Series</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Aplicar a grupos con estado:</label>
                        <select class="form-select" id="estadoFiltro">
                            <option value="bajo">Solo grupos con volumen BAJO (< 10 series)</option>
                            <option value="optimo">Solo grupos con volumen ÓPTIMO (10-18 series)</option>
                            <option value="todos">Todos los grupos musculares</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad de series totales a añadir por grupo:</label>
                        <input type="number" class="form-control" id="cantidadSeriesGlobal" min="1" max="6" value="2">
                        <small class="text-muted">Se distribuirá entre los ejercicios de cada grupo seleccionado</small>
                    </div>
                    <div class="alert alert-info" id="infoSeleccion">
                        <small>Se aplicará a todos los grupos musculares</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" onclick="confirmarAumentoGlobal()">Aplicar Aumento Global</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let grupoActual = '';
        const modalAumento = new bootstrap.Modal(document.getElementById('modalAumento'));
        const modalGlobal = new bootstrap.Modal(document.getElementById('modalGlobal'));

        function mostrarModalAumento(grupoMuscular) {
            grupoActual = grupoMuscular;
            document.getElementById('grupoSeleccionado').textContent = grupoMuscular;
            document.getElementById('cantidadSeries').value = 1;
            modalAumento.show();
        }

        function mostrarModalGlobal() {
            document.getElementById('cantidadSeriesGlobal').value = 1;
            document.getElementById('estadoFiltro').value = 'todos';
            actualizarInfoSeleccion();
            modalGlobal.show();
        }

        // Actualizar info cuando cambia el filtro
        document.getElementById('estadoFiltro')?.addEventListener('change', actualizarInfoSeleccion);

        function actualizarInfoSeleccion() {
            const filtro = document.getElementById('estadoFiltro').value;
            const infoDiv = document.getElementById('infoSeleccion');

            switch(filtro) {
                case 'todos':
                    infoDiv.innerHTML = '<small>Se aplicará a <strong>todos</strong> los grupos musculares</small>';
                    infoDiv.className = 'alert alert-info';
                    break;
                case 'bajo':
                    infoDiv.innerHTML = '<small>Se aplicará solo a grupos con volumen <strong>BAJO</strong> (< 10 series/semana) ⚠️</small>';
                    infoDiv.className = 'alert alert-warning';
                    break;
                case 'optimo':
                    infoDiv.innerHTML = '<small>Se aplicará solo a grupos con volumen <strong>ÓPTIMO</strong> (10-18 series/semana) ✅</small>';
                    infoDiv.className = 'alert alert-success';
                    break;
            }
        }

        function confirmarAumento() {
            const cantidad = parseInt(document.getElementById('cantidadSeries').value);

            if (cantidad <= 0 || cantidad > 8) {
                alert('La cantidad debe ser entre 1 y 8 series');
                return;
            }

            aumentarVolumen(grupoActual, cantidad);
            modalAumento.hide();
        }

        function confirmarAumentoGlobal() {
            const cantidad = parseInt(document.getElementById('cantidadSeriesGlobal').value);
            const estado = document.getElementById('estadoFiltro').value;

            if (cantidad <= 0 || cantidad > 6) {
                alert('La cantidad debe ser entre 1 y 6 series');
                return;
            }

            let mensaje = '';
            switch(estado) {
                case 'todos':
                    mensaje = `¿Añadir ${cantidad} serie(s) totales a cada grupo muscular?\n\nSe distribuirán inteligentemente entre los ejercicios.`;
                    break;
                case 'bajo':
                    mensaje = `¿Añadir ${cantidad} serie(s) a cada grupo con volumen BAJO?\n\nSe distribuirán entre los ejercicios de cada grupo.`;
                    break;
                case 'optimo':
                    mensaje = `¿Añadir ${cantidad} serie(s) a cada grupo con volumen ÓPTIMO?\n\nSe distribuirán entre los ejercicios de cada grupo.`;
                    break;
            }

            if (!confirm(mensaje)) {
                return;
            }

            aumentarVolumenGlobal(cantidad, estado);
            modalGlobal.hide();
        }

        function aumentarVolumen(grupoMuscular, cantidadSeries) {
            fetch('api_volumen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accion: 'aumentar',
                    grupo_muscular: grupoMuscular,
                    cantidad: cantidadSeries
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Volumen aumentado correctamente!\n\n+${data.cantidad_agregada} series distribuidas entre ${data.total_ejercicios} ejercicios de ${grupoMuscular}.`);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('❌ Error al actualizar: ' + error.message);
            });
        }

        function aumentarVolumenGlobal(cantidadSeries, estado) {
            fetch('api_volumen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accion: 'aumentar_global',
                    cantidad: cantidadSeries,
                    estado: estado
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Aumento global aplicado!\n\n+${data.cantidad_agregada} series añadidas a ${data.grupos_afectados} grupos musculares.\nTotal de actualizaciones: ${data.ejercicios_actualizados}`);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('❌ Error al actualizar: ' + error.message);
            });
        }
    </script>
</body>
</html>
