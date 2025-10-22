<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverse Diet - Transición a Volumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .progress-bar-custom { height: 30px; font-size: 14px; }
        .tooltip-icon { cursor: help; color: #0d6efd; margin-left: 5px; }
        .result-section { display: none; }
        .week-row { transition: all 0.3s; }
        .week-row:hover { background-color: #f8f9fa; }
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
                <a class="nav-link" href="index.php" title="Calculadora Principal">🏠</a>
                <a class="nav-link" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">🔄 Reverse Diet: Transición de Déficit a Volumen</h3>
                        <p class="mb-0 mt-2"><small>Plan personalizado para aumentar calorías sin acumular grasa y prepararte para el bulk</small></p>
                    </div>
                    <div class="card-body">
                        <!-- Barra de progreso -->
                        <div class="mb-4">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar"
                                     role="progressbar" style="width: 14%;" aria-valuenow="14" aria-valuemin="0" aria-valuemax="100">
                                    Paso 1 de 7
                                </div>
                            </div>
                        </div>

                        <form id="reverse-diet-form">
                            <!-- PASO 1: Datos Personales Básicos -->
                            <div class="wizard-step active" id="step-1">
                                <h4 class="mb-4">📋 Paso 1: Datos Personales Básicos</h4>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="edad" class="form-label">Edad (años) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edad" min="15" max="80" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                        <select class="form-select" id="sexo" required>
                                            <option value="hombre">Hombre</option>
                                            <option value="mujer">Mujer</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="peso_actual" class="form-label">
                                            Peso actual (kg) <span class="text-danger">*</span>
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Tu peso actual tras el déficit">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="peso_actual" min="40" max="200" step="0.1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="altura" class="form-label">Altura (cm) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="altura" min="140" max="220" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary" onclick="nextStep(1)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 2: Actividad Física -->
                            <div class="wizard-step" id="step-2">
                                <h4 class="mb-4">🏋️ Paso 2: Actividad Física</h4>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="dias_gym" class="form-label">
                                            Días de gym por semana <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="dias_gym" min="0" max="7" value="4" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="horas_gym" class="form-label">Horas por sesión de gym</label>
                                        <input type="number" class="form-control" id="horas_gym" min="0.5" max="4" step="0.5" value="1.5">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dias_cardio" class="form-label">Días de cardio por semana</label>
                                        <input type="number" class="form-control" id="dias_cardio" min="0" max="7" value="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="horas_cardio" class="form-label">Horas por sesión de cardio</label>
                                        <input type="number" class="form-control" id="horas_cardio" min="0" max="3" step="0.25" value="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="tipo_cardio" class="form-label">Tipo de cardio</label>
                                        <select class="form-select" id="tipo_cardio">
                                            <option value="ninguno">Ninguno</option>
                                            <option value="caminar_ligero">Caminar ligero</option>
                                            <option value="caminar_rapido">Caminar rápido</option>
                                            <option value="correr_moderado">Correr moderado</option>
                                            <option value="correr_intenso">Correr intenso</option>
                                            <option value="bicicleta">Bicicleta</option>
                                            <option value="natacion">Natación</option>
                                            <option value="eliptica">Elíptica</option>
                                            <option value="hiit">HIIT</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">← Anterior</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 3: Estilo de Vida -->
                            <div class="wizard-step" id="step-3">
                                <h4 class="mb-4">🏢 Paso 3: Estilo de Vida</h4>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tipo_trabajo" class="form-label">
                                            Tipo de trabajo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="tipo_trabajo" required>
                                            <option value="sedentario">Sedentario (oficina/estudio)</option>
                                            <option value="activo">Activo (de pie, moviéndome)</option>
                                            <option value="muy_activo">Muy activo (trabajo físico)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="horas_trabajo" class="form-label">Horas de trabajo al día</label>
                                        <input type="number" class="form-control" id="horas_trabajo" min="0" max="16" step="0.5" value="8">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="horas_sueno" class="form-label">
                                            Horas de sueño por noche
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Recomendado: 7-9 horas">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="horas_sueno" min="4" max="12" step="0.5" value="7">
                                        <small class="text-muted" id="sueno-warning" style="display:none; color: #dc3545;">⚠️ Se recomienda dormir 7-9 horas</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">← Anterior</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 4: Historial de Déficit (CRÍTICO) -->
                            <div class="wizard-step" id="step-4">
                                <h4 class="mb-4">📉 Paso 4: Historial de Déficit Calórico</h4>
                                <div class="alert alert-warning">
                                    <strong>⚠️ Sección crítica:</strong> Esta información es fundamental para calcular tu adaptación metabólica.
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tiempo_deficit" class="form-label">
                                            ¿Cuánto tiempo llevas en déficit calórico? <span class="text-danger">*</span>
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Tiempo total en déficit continuado">ℹ️</span>
                                        </label>
                                        <select class="form-select" id="tiempo_deficit" required>
                                            <option value="0-1">Menos de 1 mes</option>
                                            <option value="1-2">1-2 meses</option>
                                            <option value="2-3">2-3 meses</option>
                                            <option value="3-6">3-6 meses</option>
                                            <option value="6+">Más de 6 meses</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="calorias_actuales" class="form-label">
                                            Calorías actuales que consumes (kcal/día) <span class="text-danger">*</span>
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Promedio de lo que comes actualmente">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="calorias_actuales" min="1000" max="4000" step="50" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="peso_perdido" class="form-label">
                                            ¿Cuánto peso has perdido en total? (kg) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="peso_perdido" min="0" max="100" step="0.5" required>
                                        <small class="text-muted">Ejemplo: Has perdido 15 kg en los últimos 6 meses</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="peso_maximo" class="form-label">
                                            Peso máximo anterior (kg) - Opcional
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Tu peso más alto antes del déficit">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="peso_maximo" min="40" max="250" step="0.1">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(4)">← Anterior</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 5: Nivel de Entrenamiento -->
                            <div class="wizard-step" id="step-5">
                                <h4 class="mb-4">💪 Paso 5: Nivel de Entrenamiento</h4>

                                <div class="mb-3">
                                    <label for="anos_entrenando" class="form-label">
                                        Años entrenando con pesas <span class="text-danger">*</span>
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" title="Tiempo de entrenamiento consistente con pesas">ℹ️</span>
                                    </label>
                                    <select class="form-select" id="anos_entrenando" required>
                                        <option value="novato">Novato (0-1 año)</option>
                                        <option value="principiante">Principiante (1-2 años)</option>
                                        <option value="intermedio">Intermedio (2-4 años)</option>
                                        <option value="avanzado">Avanzado (4+ años)</option>
                                    </select>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(5)">← Anterior</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(5)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 6: Composición Corporal (Opcional) -->
                            <div class="wizard-step" id="step-6">
                                <h4 class="mb-4">📊 Paso 6: Composición Corporal</h4>
                                <div class="alert alert-info">
                                    <strong>ℹ️ Opcional pero recomendado:</strong> Esta información permite cálculos más precisos.
                                </div>

                                <div class="mb-3">
                                    <label for="grasa_corporal" class="form-label">
                                        % de grasa corporal estimado (si lo sabes)
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" title="Deja vacío si no lo sabes">ℹ️</span>
                                    </label>
                                    <input type="number" class="form-control" id="grasa_corporal" min="5" max="50" step="0.1">
                                </div>

                                <h5 class="mt-4 mb-3">O proporciona medidas para calcularlo (Método Navy):</h5>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="circunferencia_cintura" class="form-label">
                                            Circunferencia de cintura (cm)
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="A nivel del ombligo">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="circunferencia_cintura" min="50" max="200" step="0.1">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="circunferencia_cuello" class="form-label">
                                            Circunferencia de cuello (cm)
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Debajo de la nuez">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="circunferencia_cuello" min="20" max="60" step="0.1">
                                    </div>
                                    <div class="col-md-4 mb-3" id="campo-cadera" style="display:none;">
                                        <label for="circunferencia_cadera" class="form-label">
                                            Circunferencia de cadera (cm)
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" title="Solo mujeres - parte más ancha">ℹ️</span>
                                        </label>
                                        <input type="number" class="form-control" id="circunferencia_cadera" min="60" max="200" step="0.1">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(6)">← Anterior</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(6)">Siguiente →</button>
                                </div>
                            </div>

                            <!-- PASO 7: Objetivo del Bulk -->
                            <div class="wizard-step" id="step-7">
                                <h4 class="mb-4">🎯 Paso 7: Objetivo del Bulk</h4>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo_bulk" class="form-label">
                                            Tipo de bulk deseado <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="tipo_bulk" required>
                                            <option value="ultra_limpio">Ultra Limpio (8-10% superávit)</option>
                                            <option value="limpio" selected>Lean Bulk Óptimo ⭐ (10-12% superávit)</option>
                                            <option value="balanceado">Balanceado (13-17% superávit)</option>
                                            <option value="agresivo">Agresivo (20%+ superávit)</option>
                                        </select>
                                        <small class="text-muted">Limpio = menos grasa, más lento. Agresivo = más rápido, más grasa.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="duracion_bulk" class="form-label">
                                            Duración planeada del bulk (meses) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="duracion_bulk" min="3" max="12" value="6" required>
                                        <small class="text-muted">Recomendado: 4-8 meses</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(7)">← Anterior</button>
                                    <button type="button" class="btn btn-success btn-lg" onclick="calcularReverseDiet()">🔄 Calcular Mi Plan</button>
                                </div>
                            </div>
                        </form>

                        <!-- Sección de Resultados -->
                        <div id="resultados" class="result-section mt-4">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="reverse_diet.js"></script>
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Mostrar campo cadera solo para mujeres
        document.getElementById('sexo').addEventListener('change', function() {
            const campoCadera = document.getElementById('campo-cadera');
            if (this.value === 'mujer') {
                campoCadera.style.display = 'block';
            } else {
                campoCadera.style.display = 'none';
            }
        });

        // Advertencia de horas de sueño
        document.getElementById('horas_sueno').addEventListener('input', function() {
            const warning = document.getElementById('sueno-warning');
            if (this.value < 6 || this.value > 10) {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        });
    </script>
</body>
</html>
