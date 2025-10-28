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

// Obtener ID del día
$dia_id = isset($_GET['dia_id']) ? intval($_GET['dia_id']) : 0;

if (!$dia_id) {
    header('Location: rutinas_v0.php');
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
    header('Location: rutinas_v0.php');
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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">

    <style>
        .ejercicio-card {
            margin-bottom: 1.5rem;
            border-left: 4px solid #6366f1;
        }
        .set-input {
            width: 80px;
        }
        .mejor-marca {
            background-color: #fef3c7;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="rutinas_v0.php" class="navbar-brand-modern">
            <i data-lucide="arrow-left" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
            Volver a Rutinas
        </a>
        <div class="navbar-links">
            <span style="color: #64748b;">👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></span>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Header del día -->
        <div class="v0-card">
            <div class="v0-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="dumbbell" style="color: #6366f1; width: 28px; height: 28px;"></i>
                    <div>
                        <h3><?php echo htmlspecialchars($dia['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($dia['rutina_nombre']); ?></p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <label style="color: #1e293b; font-weight: 600; margin: 0;">Fecha:</label>
                    <input type="date"
                           class="v0-input"
                           style="width: 160px; padding: 0.5rem 0.75rem;"
                           value="<?php echo $fecha; ?>"
                           onchange="window.location.href='?dia_id=<?php echo $dia_id; ?>&fecha=' + this.value">
                </div>
            </div>
        </div>

        <!-- Ejercicios -->
        <?php foreach ($ejercicios as $ejercicio): ?>
            <div class="v0-card ejercicio-card">
                <div class="v0-card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h5 style="color: #1e293b; margin-bottom: 0.5rem; font-size: 1.125rem;">
                                <?php echo $ejercicio['orden']; ?>. <?php echo htmlspecialchars($ejercicio['nombre']); ?>
                            </h5>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span class="v0-badge v0-badge-secondary"><?php echo htmlspecialchars($ejercicio['tipo_equipo']); ?></span>
                                <span class="v0-badge v0-badge-info"><?php echo htmlspecialchars($ejercicio['musculo_principal']); ?></span>
                                <span class="v0-badge" style="background: #f1f5f9; color: #334155;">
                                    Objetivo: <?php echo ($ejercicio['sets_recomendados'] ?? $ejercicio['sets_objetivo']); ?> series × <?php echo ($ejercicio['reps_recomendadas'] ?? $ejercicio['reps_objetivo']); ?> reps
                                </span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <?php if (!empty($historico[$ejercicio['id']])): ?>
                                <small style="color: #64748b;">
                                    <strong>Último:</strong> <?php
                                    $ultimo = $historico[$ejercicio['id']][0];
                                    echo $ultimo['peso'] . " kg × " . $ultimo['reps'] . " reps";
                                    ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="v0-card-body">
                    <form id="form-ejercicio-<?php echo $ejercicio['id']; ?>" onsubmit="return guardarSets(event, <?php echo $ejercicio['id']; ?>)">
                        <div style="overflow-x: auto;">
                            <table class="v0-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Serie</th>
                                        <th style="width: 100px;">Peso (kg)</th>
                                        <th style="width: 100px;">Reps</th>
                                        <th style="width: 100px;">RPE</th>
                                        <th style="width: 80px;">Acción</th>
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
                                                       class="v0-input set-input"
                                                       name="peso[]"
                                                       step="0.5"
                                                       min="0"
                                                       value="<?php echo $peso; ?>"
                                                       placeholder="kg"
                                                       style="padding: 0.4rem;">
                                            </td>
                                            <td>
                                                <input type="number"
                                                       class="v0-input set-input"
                                                       name="reps[]"
                                                       min="0"
                                                       value="<?php echo $reps; ?>"
                                                       placeholder="reps"
                                                       style="padding: 0.4rem;">
                                            </td>
                                            <td>
                                                <input type="number"
                                                       class="v0-input set-input"
                                                       name="rpe[]"
                                                       step="0.5"
                                                       min="1"
                                                       max="10"
                                                       value="<?php echo $rpe; ?>"
                                                       placeholder="1-10"
                                                       style="padding: 0.4rem;">
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="v0-btn v0-btn-danger"
                                                        style="padding: 0.4rem 0.75rem;"
                                                        onclick="eliminarSet(this)">
                                                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; gap: 1rem; flex-wrap: wrap;">
                            <button type="button"
                                    class="v0-btn v0-btn-secondary"
                                    onclick="agregarSet(<?php echo $ejercicio['id']; ?>)">
                                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                                Añadir Serie
                            </button>
                            <button type="submit" class="v0-btn v0-btn-primary">
                                <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                                Guardar
                            </button>
                        </div>
                    </form>

                    <!-- Histórico -->
                    <?php if (!empty($historico[$ejercicio['id']])): ?>
                        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                        <h6 style="color: #1e293b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="bar-chart-2" style="width: 18px; height: 18px; color: #6366f1;"></i>
                            Histórico
                        </h6>
                        <div style="overflow-x: auto;">
                            <table class="v0-table">
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
                                                <small style="color: #64748b;">(Vol: <?php echo $mejor_set['peso'] * $mejor_set['reps']; ?> kg)</small>
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

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    <script src="dia_entrenamiento.js"></script>
</body>
</html>
