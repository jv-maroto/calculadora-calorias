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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">

    <style>
        .ejercicio-item {
            transition: background-color 0.2s;
        }
        .ejercicio-item:hover {
            background-color: #f8fafc;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index_v0_design.php" class="navbar-brand-modern">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <span style="color: #64748b; margin-right: 1rem;">👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></span>
            <a href="index_v0_design.php" title="Calculadora">🧮</a>
            <a href="rutinas_v0.php" title="Rutinas">🏋️</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Header -->
        <div class="v0-card">
            <div class="v0-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="settings" style="color: #6366f1; width: 28px; height: 28px;"></i>
                    <div>
                        <h3>Gestionar Ejercicios</h3>
                        <p>Añade, edita o elimina ejercicios de tu rutina</p>
                    </div>
                </div>
                <a href="volumen_semanal.php" class="v0-btn v0-btn-secondary" style="margin-top: 0.5rem;">
                    <i data-lucide="bar-chart" style="width: 18px; height: 18px;"></i>
                    Ver Volumen Semanal
                </a>
            </div>
        </div>

        <!-- Días y ejercicios -->
        <?php foreach ($dias as $dia): ?>
            <div class="v0-card mt-3">
                <div class="v0-card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <h5 style="color: #1e293b; margin: 0;"><?php echo htmlspecialchars($dia['nombre']); ?> - <?php echo htmlspecialchars($dia['tipo']); ?></h5>
                    <button class="v0-btn v0-btn-success" style="margin-top: 0.5rem;" onclick="mostrarModalNuevo(<?php echo $dia['id']; ?>, '<?php echo htmlspecialchars($dia['nombre']); ?>')">
                        <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                        Añadir Ejercicio
                    </button>
                </div>
                <div class="v0-card-body">
                    <?php if (empty($ejercicios_por_dia[$dia['id']])): ?>
                        <p style="color: #94a3b8; margin: 0; text-align: center; padding: 2rem;">
                            <i data-lucide="inbox" style="width: 48px; height: 48px; display: inline-block; margin-bottom: 0.5rem;"></i><br>
                            No hay ejercicios. Añade el primero.
                        </p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="v0-table">
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
                                            <td><span class="v0-badge v0-badge-secondary"><?php echo htmlspecialchars($ejercicio['tipo_equipo'] ?? 'N/A'); ?></span></td>
                                            <td><?php echo htmlspecialchars($ejercicio['grupo_muscular'] ?? 'N/A'); ?></td>
                                            <td><?php echo ($ejercicio['sets_recomendados'] ?? 3); ?> x <?php echo ($ejercicio['reps_recomendadas'] ?? '8-12'); ?></td>
                                            <td>
                                                <button class="v0-btn v0-btn-warning" style="padding: 0.4rem 0.75rem; margin-right: 0.5rem;" onclick='editarEjercicio(<?php echo json_encode($ejercicio); ?>)'>
                                                    <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                                                </button>
                                                <button class="v0-btn v0-btn-danger" style="padding: 0.4rem 0.75rem;" onclick="eliminarEjercicio(<?php echo $ejercicio['id']; ?>, '<?php echo htmlspecialchars($ejercicio['nombre']); ?>')">
                                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
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
    <div class="modal" id="modalEjercicio">
        <div class="modal-content">
            <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin: 0; color: #1e293b; font-size: 1.25rem;" id="modalTitulo">Añadir Ejercicio</h5>
                <button type="button" style="background: none; border: none; cursor: pointer; color: #64748b;" onclick="cerrarModal()">
                    <i data-lucide="x" style="width: 24px; height: 24px;"></i>
                </button>
            </div>
            <form id="formEjercicio">
                <div style="padding: 1.5rem;">
                    <input type="hidden" id="ejercicio_id" name="ejercicio_id">
                    <input type="hidden" id="dia_id" name="dia_id">
                    <input type="hidden" id="accion" name="accion" value="crear">

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Nombre del Ejercicio <span style="color: #ef4444;">*</span></label>
                        <input type="text" class="v0-input" id="nombre" name="nombre" required>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Orden <span style="color: #ef4444;">*</span></label>
                        <input type="number" class="v0-input" id="orden" name="orden" min="1" required>
                    </div>

                    <div class="grid-2">
                        <div style="margin-bottom: 1rem;">
                            <label class="v0-label">Sets Recomendados <span style="color: #ef4444;">*</span></label>
                            <input type="number" class="v0-input" id="sets_recomendados" name="sets_recomendados" min="1" max="10" value="3" required>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label class="v0-label">Reps Recomendadas <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="v0-input" id="reps_recomendadas" name="reps_recomendadas" placeholder="8-12" required>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Tipo de Equipo <span style="color: #ef4444;">*</span></label>
                        <select class="v0-select" id="tipo_equipo" name="tipo_equipo" required>
                            <option value="Barra">Barra</option>
                            <option value="Mancuernas">Mancuernas</option>
                            <option value="Máquina">Máquina</option>
                            <option value="Cable">Cable</option>
                            <option value="Peso Corporal">Peso Corporal</option>
                            <option value="Kettlebell">Kettlebell</option>
                            <option value="Banda Elástica">Banda Elástica</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Grupo Muscular <span style="color: #ef4444;">*</span></label>
                        <input type="text" class="v0-input" id="grupo_muscular" name="grupo_muscular" placeholder="Ej: Pecho, Espalda, Cuádriceps..." required>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Notas (Opcional)</label>
                        <textarea class="v0-input" id="notas" name="notas" rows="2" placeholder="Técnica, consejos, variaciones..."></textarea>
                    </div>
                </div>
                <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="v0-btn v0-btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="v0-btn v0-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function cerrarModal() {
            document.getElementById('modalEjercicio').classList.remove('show');
        }

        function mostrarModalNuevo(diaId, diaNombre) {
            document.getElementById('modalTitulo').textContent = 'Añadir Ejercicio a ' + diaNombre;
            document.getElementById('accion').value = 'crear';
            document.getElementById('dia_id').value = diaId;
            document.getElementById('formEjercicio').reset();
            document.getElementById('modalEjercicio').classList.add('show');
            lucide.createIcons();
        }

        function editarEjercicio(ejercicio) {
            document.getElementById('modalTitulo').textContent = 'Editar Ejercicio';
            document.getElementById('accion').value = 'editar';
            document.getElementById('ejercicio_id').value = ejercicio.id;
            document.getElementById('dia_id').value = ejercicio.dia_id;
            document.getElementById('nombre').value = ejercicio.nombre;
            document.getElementById('orden').value = ejercicio.orden;
            document.getElementById('sets_recomendados').value = ejercicio.sets_recomendados || ejercicio.sets_objetivo || 3;
            document.getElementById('reps_recomendadas').value = ejercicio.reps_recomendadas || ejercicio.reps_objetivo || '8-12';
            document.getElementById('tipo_equipo').value = ejercicio.tipo_equipo || 'Barra';
            document.getElementById('grupo_muscular').value = ejercicio.grupo_muscular || '';
            document.getElementById('notas').value = ejercicio.notas || '';
            document.getElementById('modalEjercicio').classList.add('show');
            lucide.createIcons();
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modalEjercicio').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
    <script src="gestionar_ejercicios.js"></script>
</body>
</html>
