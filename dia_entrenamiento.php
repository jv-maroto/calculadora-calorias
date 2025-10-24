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

// Obtener ID del día
$dia_id = isset($_GET['dia_id']) ? intval($_GET['dia_id']) : 0;

if (!$dia_id) {
    header('Location: rutinas.php');
    exit;
}

// Obtener información del día
$sql_dia = "SELECT d.*, r.nombre as rutina_nombre
            FROM dias_entrenamiento d
            JOIN rutinas r ON d.rutina_id = r.id
            WHERE d.id = ?";
$stmt = $conn->prepare($sql_dia);
$stmt->bind_param("i", $dia_id);
$stmt->execute();
$dia = $stmt->get_result()->fetch_assoc();

if (!$dia) {
    header('Location: rutinas.php');
    exit;
}

// Obtener ejercicios del día
$sql_ejercicios = "SELECT * FROM ejercicios WHERE dia_id = ? ORDER BY orden";
$stmt = $conn->prepare($sql_ejercicios);
$stmt->bind_param("i", $dia_id);
$stmt->execute();
$ejercicios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener fecha seleccionada (hoy por defecto)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener registros del día seleccionado
$registros = [];
foreach ($ejercicios as $ejercicio) {
    $sql_reg = "SELECT * FROM registros_entrenamiento
                WHERE ejercicio_id = ? AND nombre = ? AND apellidos = ? AND fecha = ?
                ORDER BY set_numero";
    $stmt = $conn->prepare($sql_reg);
    $stmt->bind_param("isss", $ejercicio['id'], $nombre, $apellidos, $fecha);
    $stmt->execute();
    $registros[$ejercicio['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Obtener histórico del ejercicio (últimos 5 entrenamientos)
$historico = [];
foreach ($ejercicios as $ejercicio) {
    $sql_hist = "SELECT fecha, set_numero, peso, reps
                 FROM registros_entrenamiento
                 WHERE ejercicio_id = ? AND nombre = ? AND apellidos = ?
                 ORDER BY fecha DESC, set_numero
                 LIMIT 15";
    $stmt = $conn->prepare($sql_hist);
    $stmt->bind_param("iss", $ejercicio['id'], $nombre, $apellidos);
    $stmt->execute();
    $historico[$ejercicio['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dia['nombre']); ?> - Gym Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .ejercicio-card {
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        .set-input {
            width: 80px;
        }
        .btn-add-set {
            width: 100%;
        }
        .historico-badge {
            font-size: 0.75rem;
        }
        .mejor-marca {
            background-color: #ffd700;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="rutinas.php">← Volver a Rutinas</a>
            <span class="navbar-text text-white">
                👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?>
            </span>
        </div>
    </nav>

    <div class="container py-4" style="max-width: 1400px;">
        <!-- Header del día -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0"><?php echo htmlspecialchars($dia['nombre']); ?></h3>
                        <small><?php echo htmlspecialchars($dia['rutina_nombre']); ?></small>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <label class="text-white mb-0">Fecha:</label>
                            <input type="date"
                                   class="form-control"
                                   style="width: 160px;"
                                   value="<?php echo $fecha; ?>"
                                   onchange="window.location.href='?dia_id=<?php echo $dia_id; ?>&fecha=' + this.value">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ejercicios -->
        <?php foreach ($ejercicios as $ejercicio): ?>
            <div class="card ejercicio-card shadow">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <?php echo $ejercicio['orden']; ?>.
                                <?php echo htmlspecialchars($ejercicio['nombre']); ?>
                            </h5>
                            <div class="mt-1">
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($ejercicio['tipo_equipo']); ?></span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($ejercicio['musculo_principal']); ?></span>
                                <span class="badge bg-light text-dark">
                                    Objetivo: <?php echo ($ejercicio['sets_recomendados'] ?? $ejercicio['sets_objetivo']); ?> series × <?php echo ($ejercicio['reps_recomendadas'] ?? $ejercicio['reps_objetivo']); ?> reps
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if (!empty($historico[$ejercicio['id']])): ?>
                                <small class="text-muted">
                                    Último: <?php
                                    $ultimo = $historico[$ejercicio['id']][0];
                                    echo $ultimo['peso'] . " kg × " . $ultimo['reps'] . " reps";
                                    ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="form-ejercicio-<?php echo $ejercicio['id']; ?>" onsubmit="return guardarSets(event, <?php echo $ejercicio['id']; ?>)">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Serie</th>
                                        <th>Peso (kg)</th>
                                        <th>Reps</th>
                                        <th>RPE</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="sets-container-<?php echo $ejercicio['id']; ?>">
                                    <?php
                                    $sets_registrados = $registros[$ejercicio['id']];
                                    $num_sets = max(count($sets_registrados), 1);

                                    $sets_objetivo = $ejercicio['sets_recomendados'] ?? $ejercicio['sets_objetivo'];
                                    for ($i = 1; $i <= max($num_sets, $sets_objetivo); $i++):
                                        $set = isset($sets_registrados[$i-1]) ? $sets_registrados[$i-1] : null;
                                        $peso = $set ? $set['peso'] : '';
                                        $reps = $set ? $set['reps'] : '';
                                        $rpe = $set ? $set['rpe'] : '';
                                    ?>
                                        <tr>
                                            <td><strong><?php echo $i; ?></strong></td>
                                            <td>
                                                <input type="number"
                                                       class="form-control form-control-sm set-input"
                                                       name="peso[]"
                                                       step="0.5"
                                                       min="0"
                                                       value="<?php echo $peso; ?>"
                                                       placeholder="kg">
                                            </td>
                                            <td>
                                                <input type="number"
                                                       class="form-control form-control-sm set-input"
                                                       name="reps[]"
                                                       min="0"
                                                       value="<?php echo $reps; ?>"
                                                       placeholder="reps">
                                            </td>
                                            <td>
                                                <input type="number"
                                                       class="form-control form-control-sm set-input"
                                                       name="rpe[]"
                                                       step="0.5"
                                                       min="1"
                                                       max="10"
                                                       value="<?php echo $rpe; ?>"
                                                       placeholder="1-10">
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="eliminarSet(this)">
                                                    ×
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        onclick="agregarSet(<?php echo $ejercicio['id']; ?>)">
                                    + Añadir Serie
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    💾 Guardar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Histórico -->
                    <?php if (!empty($historico[$ejercicio['id']])): ?>
                        <hr>
                        <h6>📊 Histórico</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Sets</th>
                                        <th>Mejor Serie</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $fechas_agrupadas = [];
                                    foreach ($historico[$ejercicio['id']] as $reg) {
                                        $fechas_agrupadas[$reg['fecha']][] = $reg;
                                    }

                                    foreach (array_slice($fechas_agrupadas, 0, 5) as $fecha_hist => $sets):
                                        $mejor_set = array_reduce($sets, function($max, $set) {
                                            return (!$max || ($set['peso'] * $set['reps']) > ($max['peso'] * $max['reps'])) ? $set : $max;
                                        });
                                    ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($fecha_hist)); ?></td>
                                            <td><?php echo count($sets); ?> series</td>
                                            <td>
                                                <strong><?php echo $mejor_set['peso']; ?> kg × <?php echo $mejor_set['reps']; ?> reps</strong>
                                                <small class="text-muted">(Vol: <?php echo $mejor_set['peso'] * $mejor_set['reps']; ?> kg)</small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dia_entrenamiento.js"></script>
</body>
</html>
