<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

require_once 'connection.php';

$dia_id = isset($_GET['dia_id']) ? intval($_GET['dia_id']) : 0;

if (!$dia_id) {
    header('Location: rutinas.php');
    exit;
}

// Obtener info del día
$sql_dia = "SELECT d.*, r.nombre as rutina_nombre
            FROM dias_entrenamiento d
            JOIN rutinas r ON d.rutina_id = r.id
            WHERE d.id = ?";
$stmt = $conn->prepare($sql_dia);
$stmt->bind_param("i", $dia_id);
$stmt->execute();
$dia = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dia) {
    header('Location: rutinas.php');
    exit;
}

// Obtener ejercicios
$sql_ejercicios = "SELECT * FROM ejercicios WHERE dia_id = ? ORDER BY orden, nombre";
$stmt = $conn->prepare($sql_ejercicios);
$stmt->bind_param("i", $dia_id);
$stmt->execute();
$ejercicios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener historial para cada ejercicio
$historico = [];
foreach ($ejercicios as $ejercicio) {
    $sql_hist = "SELECT fecha, set_numero, peso, reps, rpe
                FROM registros_entrenamiento
                WHERE ejercicio_id = ? AND nombre = ? AND apellidos = ?
                ORDER BY fecha DESC, set_numero ASC
                LIMIT 20";
    $stmt = $conn->prepare($sql_hist);
    $stmt->bind_param("iss", $ejercicio['id'], $nombre, $apellidos);
    $stmt->execute();
    $historico[$ejercicio['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dia['tipo']); ?> - GYM</title>
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

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid #e5e5e5;
            padding: 1.5rem 1rem;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-meta {
            color: #666;
            font-size: 13px;
            margin-top: 0.25rem;
        }

        /* Exercises */
        .exercises {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        .exercise-block {
            background: white;
            border: 1px solid #e5e5e5;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .exercise-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .sets-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .sets-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .sets-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .set-input {
            width: 100%;
            border: 1px solid #e5e5e5;
            padding: 0.5rem;
            font-size: 14px;
            font-family: inherit;
        }

        .set-input:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        .btn {
            border: 1px solid #e5e5e5;
            background: white;
            padding: 0.75rem 1.5rem;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        .btn:hover {
            border-color: #1a1a1a;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .history-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e5e5;
        }

        .history-title {
            font-size: 13px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .history-item {
            font-size: 13px;
            color: #666;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f5f5;
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

            .header {
                padding: 2rem 1rem;
            }

            .exercise-block {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Top Nav - Desktop -->
    <nav class="top-nav">
        <div class="top-nav-links">
            <a href="dashboard.php">← Dashboard</a>
            <a href="gym_hub.php">GYM Hub</a>
            <a href="rutinas.php" class="active">Rutinas</a>
            <a href="analisis_progreso.php">Progreso</a>
            <a href="gestionar_ejercicios.php">Ejercicios</a>
            <a href="volumen_semanal.php">Volumen</a>
        </div>
        <a href="logout.php" style="color: #999;">Salir</a>
    </nav>

    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="rutinas.php" class="back-link">← Volver a rutinas</a>
            <h1><?php echo htmlspecialchars($dia['tipo']); ?></h1>
            <p class="header-meta"><?php echo count($ejercicios); ?> ejercicios</p>
        </div>
    </div>

    <!-- Exercises -->
    <div class="exercises">
        <?php foreach ($ejercicios as $ejercicio): ?>
            <div class="exercise-block">
                <div class="exercise-name"><?php echo htmlspecialchars($ejercicio['nombre']); ?></div>

                <form id="form-<?php echo $ejercicio['id']; ?>" onsubmit="return guardarSets(event, <?php echo $ejercicio['id']; ?>)">
                    <table class="sets-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Set</th>
                                <th>Peso (kg)</th>
                                <th>Reps</th>
                                <th>RPE</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="sets-<?php echo $ejercicio['id']; ?>">
                            <tr>
                                <td><strong>1</strong></td>
                                <td><input type="number" class="set-input" name="peso[]" step="0.5" min="0" placeholder="0"></td>
                                <td><input type="number" class="set-input" name="reps[]" min="0" placeholder="0"></td>
                                <td><input type="number" class="set-input" name="rpe[]" step="0.5" min="1" max="10" placeholder="-"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="actions">
                        <button type="button" class="btn btn-small" onclick="agregarSet(<?php echo $ejercicio['id']; ?>)">+ Set</button>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Guardar</button>
                    </div>
                </form>

                <?php if (!empty($historico[$ejercicio['id']])): ?>
                    <div class="history-section">
                        <div class="history-title">Última sesión</div>
                        <?php
                        $ultima_fecha = $historico[$ejercicio['id']][0]['fecha'];
                        $sets_ultima = array_filter($historico[$ejercicio['id']], function($h) use ($ultima_fecha) {
                            return $h['fecha'] == $ultima_fecha;
                        });
                        ?>
                        <div class="history-item">
                            <?php echo date('d/m/Y', strtotime($ultima_fecha)); ?>:
                            <?php foreach ($sets_ultima as $set): ?>
                                <?php echo $set['peso']; ?>kg × <?php echo $set['reps']; ?> •
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Nav - Mobile -->
    <nav class="bottom-nav">
        <a href="dashboard.php">
            <div>Inicio</div>
        </a>
        <a href="gym_hub.php">
            <div>GYM</div>
        </a>
        <a href="rutinas.php" class="active">
            <div>Rutinas</div>
        </a>
        <a href="analisis_progreso.php">
            <div>Progreso</div>
        </a>
    </nav>

    <script src="dia_entrenamiento.js"></script>
    <script>
        function agregarSet(ejercicioId) {
            const tbody = document.getElementById(`sets-${ejercicioId}`);
            const setNumber = tbody.querySelectorAll('tr').length + 1;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${setNumber}</strong></td>
                <td><input type="number" class="set-input" name="peso[]" step="0.5" min="0" placeholder="0"></td>
                <td><input type="number" class="set-input" name="reps[]" min="0" placeholder="0"></td>
                <td><input type="number" class="set-input" name="rpe[]" step="0.5" min="1" max="10" placeholder="-"></td>
                <td><button type="button" class="btn btn-small" onclick="this.closest('tr').remove(); renumerarSets(${ejercicioId})">×</button></td>
            `;
            tbody.appendChild(row);
        }

        function renumerarSets(ejercicioId) {
            const rows = document.querySelectorAll(`#sets-${ejercicioId} tr`);
            rows.forEach((row, index) => {
                row.querySelector('strong').textContent = index + 1;
            });
        }

        function guardarSets(event, ejercicioId) {
            event.preventDefault();
            const form = event.target;
            const pesos = Array.from(form.querySelectorAll('input[name="peso[]"]')).map(i => i.value);
            const reps = Array.from(form.querySelectorAll('input[name="reps[]"]')).map(i => i.value);
            const rpes = Array.from(form.querySelectorAll('input[name="rpe[]"]')).map(i => i.value);

            const sets = [];
            for (let i = 0; i < pesos.length; i++) {
                if (pesos[i] || reps[i]) {
                    sets.push({
                        peso: parseFloat(pesos[i]) || 0,
                        reps: parseInt(reps[i]) || 0,
                        rpe: rpes[i] ? parseFloat(rpes[i]) : null,
                        set_numero: i + 1
                    });
                }
            }

            if (sets.length === 0) {
                alert('Completa al menos un set');
                return false;
            }

            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            fetch('guardar_entrenamiento.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    ejercicio_id: ejercicioId,
                    fecha: new Date().toISOString().split('T')[0],
                    sets: sets
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.textContent = 'Guardado ✓';
                    setTimeout(() => {
                        btn.textContent = 'Guardar';
                        btn.disabled = false;
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Error');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                btn.textContent = 'Guardar';
                btn.disabled = false;
            });

            return false;
        }
    </script>
</body>
</html>
