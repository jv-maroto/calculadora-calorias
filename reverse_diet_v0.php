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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">

    <style>
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .progress-bar-custom {
            height: 30px;
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            border-radius: 8px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .progress-container {
            background: #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .tooltip-icon {
            cursor: help;
            color: #6366f1;
            margin-left: 5px;
            display: inline-block;
        }
        .result-section { display: none; }
    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index_v0_design.php" class="navbar-brand-modern">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <span style="color: #64748b; margin-right: 1rem;">👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></span>
            <a href="index_v0_design.php" title="Calculadora">🧮</a>
            <a href="reverse_diet_v0.php" title="Reverse Diet" style="color: #6366f1;">🔄</a>
            <a href="rutinas_v0.php" title="Rutinas">🏋️</a>
            <a href="introducir_peso_v0.php" title="Registrar Peso">⚖️</a>
            <a href="grafica_v0.php" title="Progreso">📊</a>
            <a href="seguimiento_v0.php" title="Ajuste de Calorías">📈</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <!-- Contenido -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem 2rem;">

        <div class="v0-card">
            <div class="v0-card-header">
                <i data-lucide="repeat" style="color: #6366f1; width: 24px; height: 24px;"></i>
                <div>
                    <h3>Reverse Diet: Transición de Déficit a Volumen</h3>
                    <p>Plan personalizado para aumentar calorías sin acumular grasa y prepararte para el bulk</p>
                </div>
            </div>
            <div class="v0-card-body">

                <!-- Barra de progreso -->
                <div class="progress-container">
                    <div class="progress-bar-custom" id="progress-bar" style="width: 14%;">
                        Paso 1 de 7
                    </div>
                </div>

                <form id="reverse-diet-form">

                    <!-- PASO 1: Datos Personales Básicos -->
                    <div class="wizard-step active" id="step-1">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="user" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 1: Datos Personales Básicos
                        </h4>

                        <div class="grid-2">
                            <div>
                                <label for="edad" class="v0-label">Edad (años) <span style="color: #ef4444;">*</span></label>
                                <input type="number" class="v0-input" id="edad" min="15" max="80" required>
                            </div>
                            <div>
                                <label for="sexo" class="v0-label">Sexo <span style="color: #ef4444;">*</span></label>
                                <select class="v0-select" id="sexo" required>
                                    <option value="hombre">Hombre</option>
                                    <option value="mujer">Mujer</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2 mt-2">
                            <div>
                                <label for="peso_actual" class="v0-label">
                                    Peso actual (kg) <span style="color: #ef4444;">*</span>
                                    <span class="tooltip-icon" title="Tu peso actual tras el déficit">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="peso_actual" min="40" max="200" step="0.1" required>
                            </div>
                            <div>
                                <label for="altura" class="v0-label">Altura (cm) <span style="color: #ef4444;">*</span></label>
                                <input type="number" class="v0-input" id="altura" min="140" max="220" required>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(1)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 2: Actividad Física -->
                    <div class="wizard-step" id="step-2">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="dumbbell" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 2: Actividad Física
                        </h4>

                        <div class="grid-2">
                            <div>
                                <label for="dias_gym" class="v0-label">Días de gym por semana <span style="color: #ef4444;">*</span></label>
                                <input type="number" class="v0-input" id="dias_gym" min="0" max="7" value="4" required>
                            </div>
                            <div>
                                <label for="horas_gym" class="v0-label">Horas por sesión de gym</label>
                                <input type="number" class="v0-input" id="horas_gym" min="0.5" max="4" step="0.5" value="1.5">
                            </div>
                        </div>

                        <div class="grid-3 mt-2">
                            <div>
                                <label for="dias_cardio" class="v0-label">Días de cardio por semana</label>
                                <input type="number" class="v0-input" id="dias_cardio" min="0" max="7" value="0">
                            </div>
                            <div>
                                <label for="horas_cardio" class="v0-label">Horas por sesión de cardio</label>
                                <input type="number" class="v0-input" id="horas_cardio" min="0" max="3" step="0.25" value="0">
                            </div>
                            <div>
                                <label for="tipo_cardio" class="v0-label">Tipo de cardio</label>
                                <select class="v0-select" id="tipo_cardio">
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

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(2)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(2)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 3: Estilo de Vida -->
                    <div class="wizard-step" id="step-3">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="briefcase" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 3: Estilo de Vida
                        </h4>

                        <div class="grid-3">
                            <div>
                                <label for="tipo_trabajo" class="v0-label">Tipo de trabajo <span style="color: #ef4444;">*</span></label>
                                <select class="v0-select" id="tipo_trabajo" required>
                                    <option value="sedentario">Sedentario (oficina/estudio)</option>
                                    <option value="activo">Activo (de pie, moviéndome)</option>
                                    <option value="muy_activo">Muy activo (trabajo físico)</option>
                                </select>
                            </div>
                            <div>
                                <label for="horas_trabajo" class="v0-label">Horas de trabajo al día</label>
                                <input type="number" class="v0-input" id="horas_trabajo" min="0" max="16" step="0.5" value="8">
                            </div>
                            <div>
                                <label for="horas_sueno" class="v0-label">
                                    Horas de sueño por noche
                                    <span class="tooltip-icon" title="Recomendado: 7-9 horas">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="horas_sueno" min="4" max="12" step="0.5" value="7">
                                <small class="v0-helper" id="sueno-warning" style="display:none; color: #dc3545;">⚠️ Se recomienda dormir 7-9 horas</small>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(3)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(3)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 4: Historial de Déficit -->
                    <div class="wizard-step" id="step-4">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="trending-down" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 4: Historial de Déficit Calórico
                        </h4>

                        <div class="v0-alert v0-alert-warning">
                            <strong>⚠️ Sección crítica:</strong> Esta información es fundamental para calcular tu adaptación metabólica.
                        </div>

                        <div class="grid-2 mt-2">
                            <div>
                                <label for="tiempo_deficit" class="v0-label">
                                    ¿Cuánto tiempo llevas en déficit calórico? <span style="color: #ef4444;">*</span>
                                    <span class="tooltip-icon" title="Tiempo total en déficit continuado">ℹ️</span>
                                </label>
                                <select class="v0-select" id="tiempo_deficit" required>
                                    <option value="0-1">Menos de 1 mes</option>
                                    <option value="1-2">1-2 meses</option>
                                    <option value="2-3">2-3 meses</option>
                                    <option value="3-6">3-6 meses</option>
                                    <option value="6+">Más de 6 meses</option>
                                </select>
                            </div>
                            <div>
                                <label for="calorias_actuales" class="v0-label">
                                    Calorías actuales que consumes (kcal/día) <span style="color: #ef4444;">*</span>
                                    <span class="tooltip-icon" title="Promedio de lo que comes actualmente">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="calorias_actuales" min="1000" max="4000" step="50" required>
                            </div>
                        </div>

                        <div class="grid-2 mt-2">
                            <div>
                                <label for="peso_perdido" class="v0-label">
                                    ¿Cuánto peso has perdido en total? (kg) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" class="v0-input" id="peso_perdido" min="0" max="100" step="0.5" required>
                                <small class="v0-helper">Ejemplo: Has perdido 15 kg en los últimos 6 meses</small>
                            </div>
                            <div>
                                <label for="peso_maximo" class="v0-label">
                                    Peso máximo anterior (kg) - Opcional
                                    <span class="tooltip-icon" title="Tu peso más alto antes del déficit">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="peso_maximo" min="40" max="250" step="0.1">
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(4)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(4)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 5: Nivel de Entrenamiento -->
                    <div class="wizard-step" id="step-5">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="zap" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 5: Nivel de Entrenamiento
                        </h4>

                        <div>
                            <label for="anos_entrenando" class="v0-label">
                                Años entrenando con pesas <span style="color: #ef4444;">*</span>
                                <span class="tooltip-icon" title="Tiempo de entrenamiento consistente con pesas">ℹ️</span>
                            </label>
                            <select class="v0-select" id="anos_entrenando" required>
                                <option value="novato">Novato (0-1 año)</option>
                                <option value="principiante">Principiante (1-2 años)</option>
                                <option value="intermedio">Intermedio (2-4 años)</option>
                                <option value="avanzado">Avanzado (4+ años)</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(5)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(5)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 6: Composición Corporal -->
                    <div class="wizard-step" id="step-6">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="activity" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 6: Composición Corporal
                        </h4>

                        <div class="v0-alert v0-alert-info">
                            <strong>ℹ️ Opcional pero recomendado:</strong> Esta información permite cálculos más precisos.
                        </div>

                        <div class="mt-2">
                            <label for="grasa_corporal" class="v0-label">
                                % de grasa corporal estimado (si lo sabes)
                                <span class="tooltip-icon" title="Deja vacío si no lo sabes">ℹ️</span>
                            </label>
                            <input type="number" class="v0-input" id="grasa_corporal" min="5" max="50" step="0.1">
                        </div>

                        <h5 style="margin: 1.5rem 0 1rem; color: #1e293b; font-size: 1.125rem;">O proporciona medidas para calcularlo (Método Navy):</h5>

                        <div class="grid-3">
                            <div>
                                <label for="circunferencia_cintura" class="v0-label">
                                    Circunferencia de cintura (cm)
                                    <span class="tooltip-icon" title="A nivel del ombligo">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="circunferencia_cintura" min="50" max="200" step="0.1">
                            </div>
                            <div>
                                <label for="circunferencia_cuello" class="v0-label">
                                    Circunferencia de cuello (cm)
                                    <span class="tooltip-icon" title="Debajo de la nuez">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="circunferencia_cuello" min="20" max="60" step="0.1">
                            </div>
                            <div id="campo-cadera" style="display:none;">
                                <label for="circunferencia_cadera" class="v0-label">
                                    Circunferencia de cadera (cm)
                                    <span class="tooltip-icon" title="Solo mujeres - parte más ancha">ℹ️</span>
                                </label>
                                <input type="number" class="v0-input" id="circunferencia_cadera" min="60" max="200" step="0.1">
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(6)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-primary" onclick="nextStep(6)">
                                Siguiente
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 7: Objetivo del Bulk -->
                    <div class="wizard-step" id="step-7">
                        <h4 style="margin-bottom: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="target" style="width: 24px; height: 24px; color: #6366f1;"></i>
                            Paso 7: Objetivo del Bulk
                        </h4>

                        <div class="grid-2">
                            <div>
                                <label for="tipo_bulk" class="v0-label">
                                    Tipo de bulk deseado <span style="color: #ef4444;">*</span>
                                </label>
                                <select class="v0-select" id="tipo_bulk" required>
                                    <option value="ultra_limpio">Ultra Limpio (8-10% superávit)</option>
                                    <option value="limpio" selected>Lean Bulk Óptimo ⭐ (10-12% superávit)</option>
                                    <option value="balanceado">Balanceado (13-17% superávit)</option>
                                    <option value="agresivo">Agresivo (20%+ superávit)</option>
                                </select>
                                <small class="v0-helper">Limpio = menos grasa, más lento. Agresivo = más rápido, más grasa.</small>
                            </div>
                            <div>
                                <label for="duracion_bulk" class="v0-label">
                                    Duración planeada del bulk (meses) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" class="v0-input" id="duracion_bulk" min="3" max="12" value="6" required>
                                <small class="v0-helper">Recomendado: 4-8 meses</small>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <button type="button" class="v0-btn v0-btn-secondary" onclick="prevStep(7)">
                                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                Anterior
                            </button>
                            <button type="button" class="v0-btn v0-btn-success" style="padding: 0.75rem 1.5rem; font-size: 1.125rem;" onclick="calcularReverseDiet()">
                                <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
                                Calcular Mi Plan
                            </button>
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

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
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
    <script src="reverse_diet.js"></script>
</body>
</html>
