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
    <title>Gestionar Ejercicios</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            padding-bottom: 80px;
        }

        .v0-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .section-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 1rem;
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e5e5;
            margin-bottom: 1rem;
        }

        .day-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 8px 16px;
            border: 1px solid #e5e5e5;
            background: white;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        .btn-primary {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        .btn-primary:hover {
            background: #000;
        }

        .btn-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-danger:hover {
            background: #ef4444;
            color: white;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #999;
            border-bottom: 1px solid #e5e5e5;
            white-space: nowrap;
        }

        tbody td {
            padding: 12px;
            font-size: 14px;
            color: #1a1a1a;
            border-bottom: 1px solid #f5f5f5;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            font-size: 11px;
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 1rem;
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
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            background: white;
            font-size: 14px;
            color: #1a1a1a;
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 80px !important;
            }
            nav:first-of-type {
                display: none !important;
            }
            nav:nth-of-type(2) {
                display: flex !important;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }

            .mobile-back-btn {
                display: inline-block !important;
                width: 100%;
                text-align: center;
            }

            .v0-card {
                padding: 1rem;
            }

            .section-title {
                font-size: 16px;
            }

            .section-description {
                font-size: 12px;
            }

            .day-title {
                font-size: 14px;
            }

            .btn {
                font-size: 12px;
                padding: 6px 12px;
            }

            .exercise-item {
                padding: 0.75rem;
            }

            .exercise-name {
                font-size: 13px;
            }

            .exercise-details {
                font-size: 11px;
            }

            input, select, textarea {
                font-size: 14px;
            }

            label {
                font-size: 12px;
            }

            .table-container {
                border: 1px solid #e5e5e5;
                border-radius: 4px;
                margin: 0 -0.5rem;
            }

            table {
                min-width: 600px;
                font-size: 12px;
            }

            thead th {
                padding: 8px;
                font-size: 10px;
            }

            tbody td {
                padding: 8px;
                font-size: 12px;
            }

            .badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .btn svg {
                width: 14px;
                height: 14px;
            }
        }

        @media (min-width: 769px) {
            body {
                padding-bottom: 2rem !important;
            }
            nav:first-of-type {
                display: flex !important;
            }
            nav:nth-of-type(2) {
                display: none !important;
            }

            .mobile-back-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Top Nav - Desktop -->
    <nav style="display: none; background: white; border-bottom: 1px solid #e5e5e5; padding: 0 2rem; height: 60px; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; gap: 2rem;">
            <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">← Dashboard</a>
            <a href="gym_hub.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">GYM Hub</a>
            <a href="gestionar_ejercicios.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Ejercicios</a>
            <a href="volumen_semanal.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Volumen</a>
        </div>
        <a href="logout.php" style="color: #999; text-decoration: none; font-size: 14px; font-weight: 500;">Salir</a>
    </nav>

    <!-- Bottom Nav - Mobile -->
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e5e5; display: flex; justify-content: space-around; padding: 12px 0; z-index: 100;">
        <a href="dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Inicio</div>
        </a>
        <a href="gym_hub.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>GYM</div>
        </a>
        <a href="gestionar_ejercicios.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #1a1a1a; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Ejercicios</div>
        </a>
        <a href="volumen_semanal.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Volumen</div>
        </a>
    </nav>

    <div style="max-width: 1400px; margin: 0 auto; padding: 1rem 1rem 2rem;">

        <!-- Botón volver - Solo móvil -->
        <a href="dashboard.php" class="mobile-back-btn" style="display: inline-block; padding: 0.75rem 1.5rem; background: white; border: 1px solid #e5e5e5; color: #666; text-decoration: none; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem;">
            ← Volver
        </a>

        <!-- Header -->
        <div class="v0-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div class="section-title">Gestionar Ejercicios</div>
                    <div class="section-description">Añade, edita o elimina ejercicios de tu rutina</div>
                </div>
                <a href="mapa_muscular.php" class="btn" style="text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Mapa Muscular
                </a>
            </div>
        </div>

        <!-- Días y ejercicios -->
        <?php foreach ($dias as $dia): ?>
            <div class="v0-card">
                <div class="day-header">
                    <div class="day-title"><?php echo htmlspecialchars($dia['nombre']); ?> - <?php echo htmlspecialchars($dia['tipo']); ?></div>
                    <button class="btn btn-primary" onclick="mostrarModalNuevo(<?php echo $dia['id']; ?>, '<?php echo htmlspecialchars($dia['nombre']); ?>')">
                        + Añadir Ejercicio
                    </button>
                </div>
                <div>
                    <?php if (empty($ejercicios_por_dia[$dia['id']])): ?>
                        <div class="empty-state">
                            <div>No hay ejercicios. Añade el primero.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
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
                                        <tr>
                                            <td><strong><?php echo $ejercicio['orden']; ?></strong></td>
                                            <td><strong><?php echo htmlspecialchars($ejercicio['nombre']); ?></strong></td>
                                            <td><span class="badge"><?php echo htmlspecialchars($ejercicio['tipo_equipo'] ?? 'N/A'); ?></span></td>
                                            <td><?php echo htmlspecialchars($ejercicio['musculo_principal'] ?? 'N/A'); ?></td>
                                            <td><?php echo ($ejercicio['sets_recomendados'] ?? 3); ?> x <?php echo ($ejercicio['reps_recomendadas'] ?? '8-12'); ?></td>
                                            <td>
                                                <button class="btn" style="padding: 6px 10px; margin-right: 0.5rem;" onclick='editarEjercicio(<?php echo json_encode($ejercicio); ?>)' title="Editar">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </button>
                                                <button class="btn btn-danger" style="padding: 6px 10px;" onclick="eliminarEjercicio(<?php echo $ejercicio['id']; ?>, '<?php echo htmlspecialchars($ejercicio['nombre']); ?>')" title="Eliminar">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
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
            <div class="modal-header">
                <div class="modal-title" id="modalTitulo">Añadir Ejercicio</div>
                <button type="button" style="background: none; border: none; cursor: pointer; color: #666; font-size: 24px;" onclick="cerrarModal()">
                    ×
                </button>
            </div>
            <form id="formEjercicio">
                <div class="modal-body">
                    <input type="hidden" id="ejercicio_id" name="ejercicio_id">
                    <input type="hidden" id="dia_id" name="dia_id">
                    <input type="hidden" id="accion" name="accion" value="crear">

                    <div class="form-group">
                        <label class="form-label">Nombre del Ejercicio *</label>
                        <input type="text" class="form-input" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Orden *</label>
                        <input type="number" class="form-input" id="orden" name="orden" min="1" required>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Sets Recomendados *</label>
                            <input type="number" class="form-input" id="sets_recomendados" name="sets_recomendados" min="1" max="10" value="3" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reps Recomendadas *</label>
                            <input type="text" class="form-input" id="reps_recomendadas" name="reps_recomendadas" placeholder="8-12" required>
                        </div>
                    </div>

                    <div class="form-group">
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

                    <div class="form-group">
                        <label class="form-label">Grupo Muscular *</label>
                        <input type="text" class="form-input" id="grupo_muscular" name="grupo_muscular" placeholder="Ej: Pecho, Espalda, Cuádriceps..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas (Opcional)</label>
                        <textarea class="form-textarea" id="notas" name="notas" rows="2" placeholder="Técnica, consejos, variaciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cerrarModal() {
            document.getElementById('modalEjercicio').classList.remove('show');
        }

        function mostrarModalNuevo(diaId, diaNombre) {
            document.getElementById('modalTitulo').textContent = 'Añadir Ejercicio a ' + diaNombre;
            document.getElementById('accion').value = 'crear';
            document.getElementById('dia_id').value = diaId;
            document.getElementById('formEjercicio').reset();
            document.getElementById('dia_id').value = diaId; // Restaurar después de reset
            document.getElementById('modalEjercicio').classList.add('show');
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
            document.getElementById('grupo_muscular').value = ejercicio.musculo_principal || ejercicio.grupo_muscular || '';
            document.getElementById('notas').value = ejercicio.notas || '';
            document.getElementById('modalEjercicio').classList.add('show');
        }

        function eliminarEjercicio(id, nombre) {
            if (confirm('¿Estás seguro de eliminar "' + nombre + '"?')) {
                // Aquí iría la lógica de eliminación
                alert('Funcionalidad de eliminación pendiente de implementar');
            }
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modalEjercicio').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Manejo del formulario
        document.getElementById('formEjercicio').addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí iría la lógica de guardar
            alert('Funcionalidad de guardar pendiente de implementar');
            cerrarModal();
        });
    </script>
</body>
</html>
