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

// Obtener rutina activa
$sql_rutina = "SELECT * FROM rutinas WHERE activa = TRUE LIMIT 1";
$rutina = $conn->query($sql_rutina)->fetch_assoc();

// Obtener días de entrenamiento
$sql_dias = "SELECT * FROM dias_entrenamiento WHERE rutina_id = ? AND es_descanso = FALSE ORDER BY dia_semana";
$stmt = $conn->prepare($sql_dias);
$stmt->bind_param("i", $rutina['id']);
$stmt->execute();
$dias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener todos los ejercicios agrupados por día
$ejercicios_por_dia = [];
foreach ($dias as $dia) {
    $sql_ej = "SELECT * FROM ejercicios WHERE dia_id = ? ORDER BY orden";
    $stmt = $conn->prepare($sql_ej);
    $stmt->bind_param("i", $dia['id']);
    $stmt->execute();
    $ejercicios_por_dia[$dia['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Ejercicios - Gym Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .ejercicio-item {
            transition: background-color 0.2s;
        }
        .ejercicio-item:hover {
            background-color: #f8f9fa;
        }
        .drag-handle {
            cursor: move;
            color: #6c757d;
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
                <a class="nav-link" href="rutinas.php" title="Volver a Rutinas">🏋️</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container py-4" style="max-width: 1400px;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">⚙️ Gestionar Ejercicios</h3>
                                <small>Añade, edita o elimina ejercicios de tu rutina</small>
                            </div>
                            <a href="volumen_semanal.php" class="btn btn-light btn-sm">
                                📊 Ver Volumen Semanal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach ($dias as $dia): ?>
            <div class="card mb-4 shadow">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($dia['nombre']); ?> - <?php echo htmlspecialchars($dia['tipo']); ?></h5>
                        <button class="btn btn-success btn-sm" onclick="mostrarModalNuevo(<?php echo $dia['id']; ?>, '<?php echo htmlspecialchars($dia['nombre']); ?>')">
                            ➕ Añadir Ejercicio
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($ejercicios_por_dia[$dia['id']])): ?>
                        <p class="text-muted mb-0">No hay ejercicios. Añade el primero.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Ejercicio</th>
                                        <th>Equipo</th>
                                        <th>Grupo Muscular</th>
                                        <th>Sets x Reps</th>
                                        <th style="width: 150px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ejercicios_por_dia[$dia['id']] as $ejercicio): ?>
                                        <tr class="ejercicio-item">
                                            <td><strong><?php echo $ejercicio['orden']; ?></strong></td>
                                            <td><strong><?php echo htmlspecialchars($ejercicio['nombre']); ?></strong></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ejercicio['tipo_equipo'] ?? 'N/A'); ?></span></td>
                                            <td><?php echo htmlspecialchars($ejercicio['grupo_muscular'] ?? 'N/A'); ?></td>
                                            <td><?php echo ($ejercicio['sets_recomendados'] ?? 3); ?> x <?php echo ($ejercicio['reps_recomendadas'] ?? '8-12'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick='editarEjercicio(<?php echo json_encode($ejercicio); ?>)'>
                                                    ✏️
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="eliminarEjercicio(<?php echo $ejercicio['id']; ?>, '<?php echo htmlspecialchars($ejercicio['nombre']); ?>')">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal para añadir/editar ejercicio -->
    <div class="modal fade" id="modalEjercicio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Añadir Ejercicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEjercicio">
                    <div class="modal-body">
                        <input type="hidden" id="ejercicio_id" name="ejercicio_id">
                        <input type="hidden" id="dia_id" name="dia_id">
                        <input type="hidden" id="accion" name="accion" value="crear">

                        <div class="mb-3">
                            <label class="form-label">Nombre del Ejercicio *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Orden *</label>
                            <input type="number" class="form-control" id="orden" name="orden" min="1" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sets Recomendados *</label>
                                <input type="number" class="form-control" id="sets_recomendados" name="sets_recomendados" min="1" max="10" value="3" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reps Recomendadas *</label>
                                <input type="text" class="form-control" id="reps_recomendadas" name="reps_recomendadas" placeholder="8-12" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Equipo *</label>
                            <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                                <option value="Barra">Barra</option>
                                <option value="Mancuernas">Mancuernas</option>
                                <option value="Máquina">Máquina</option>
                                <option value="Cable">Cable</option>
                                <option value="Peso Corporal">Peso Corporal</option>
                                <option value="Kettlebell">Kettlebell</option>
                                <option value="Banda Elástica">Banda Elástica</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Grupo Muscular *</label>
                            <input type="text" class="form-control" id="grupo_muscular" name="grupo_muscular" placeholder="Ej: Pecho, Espalda, Cuádriceps..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas (Opcional)</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2" placeholder="Técnica, consejos, variaciones..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="gestionar_ejercicios.js"></script>
</body>
</html>
