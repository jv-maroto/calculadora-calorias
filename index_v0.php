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
    <title>Calculadora Nutricional | Planificación Personalizada</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos personalizados de v0.dev -->
    <link rel="stylesheet" href="assets/css/v0-styles.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Configuración de Tailwind inline */
        @layer base {
            * {
                @apply border-border;
            }
            body {
                @apply bg-background text-foreground;
            }
        }
    </style>
</head>
<body>
    <!-- Inputs ocultos para datos de sesión -->
    <input type="hidden" id="usuario_nombre" value="<?php echo htmlspecialchars($nombre); ?>">
    <input type="hidden" id="usuario_apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">

    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="header-logo">
                <div class="header-icon">
                    <i data-lucide="calculator" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h1 class="header-title">Calculadora Nutricional</h1>
                    <p class="header-subtitle">Planificación personalizada</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 0.875rem; color: var(--muted-foreground);">
                    👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?>
                </span>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="reverse_diet.php" title="Reverse Diet" style="padding: 0.5rem; color: var(--muted-foreground); text-decoration: none;">🔄</a>
                    <a href="rutinas.php" title="Rutinas" style="padding: 0.5rem; color: var(--muted-foreground); text-decoration: none;">🏋️</a>
                    <a href="introducir_peso.php" title="Introducir Peso" style="padding: 0.5rem; color: var(--muted-foreground); text-decoration: none;">⚖️</a>
                    <a href="seguimiento.php" title="Seguimiento" style="padding: 0.5rem; color: var(--muted-foreground); text-decoration: none;">📊</a>
                    <a href="logout.php" title="Cerrar Sesión" style="padding: 0.5rem; color: var(--muted-foreground); text-decoration: none;">🚪</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Container principal -->
    <div class="container">
        <div class="main-grid">
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="space-y-6" id="form-column">

                <!-- Card: Datos Personales -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
                            Datos Personales
                        </h2>
                        <p class="card-description">Información básica para calcular tu metabolismo basal</p>
                    </div>
                    <div class="card-content space-y-6">
                        <div class="grid-2">
                            <div class="form-group">
                                <label for="edad" class="form-label">Edad (años)</label>
                                <input type="number" class="form-input" id="edad" name="edad" min="15" max="100" value="25" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sexo</label>
                                <div class="radio-group">
                                    <label class="radio-item">
                                        <input type="radio" name="sexo" id="sexo-hombre" value="hombre" class="radio-input" checked>
                                        <span>Hombre</span>
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio" name="sexo" id="sexo-mujer" value="mujer" class="radio-input">
                                        <span>Mujer</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-input" id="peso" name="peso" min="40" max="200" step="0.1" value="75" required>
                            </div>
                            <div class="form-group">
                                <label for="altura" class="form-label">Altura (cm)</label>
                                <input type="number" class="form-input" id="altura" name="altura" min="140" max="220" value="175" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Nivel de Actividad -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i data-lucide="target" style="width: 20px; height: 20px;"></i>
                            Nivel de Actividad
                        </h2>
                        <p class="card-description">Describe tu actividad física semanal</p>
                    </div>
                    <div class="card-content space-y-6">
                        <!-- Días de gym -->
                        <div class="form-group">
                            <label for="dias-gym" class="form-label">
                                Días de entrenamiento/semana: <span id="dias-gym-value">4</span>
                            </label>
                            <input type="range" class="slider" id="dias-gym" min="0" max="7" value="4" step="1">
                        </div>

                        <!-- Horas de gym -->
                        <div class="form-group">
                            <label for="horas-gym" class="form-label">
                                Horas por sesión: <span id="horas-gym-value">1.5</span>h
                            </label>
                            <input type="range" class="slider" id="horas-gym" min="0" max="5" value="1.5" step="0.5">
                        </div>

                        <!-- Días de cardio -->
                        <div class="form-group">
                            <label for="dias-cardio" class="form-label">
                                Días de cardio/semana: <span id="dias-cardio-value">2</span>
                            </label>
                            <input type="range" class="slider" id="dias-cardio" min="0" max="7" value="2" step="1">
                        </div>

                        <!-- Horas de cardio -->
                        <div class="form-group">
                            <label for="horas-cardio" class="form-label">
                                Horas de cardio/sesión: <span id="horas-cardio-value">0.5</span>h
                            </label>
                            <input type="range" class="slider" id="horas-cardio" min="0" max="3" value="0.5" step="0.5">
                        </div>
                    </div>
                </div>

                <!-- Card: Estilo de Vida -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Estilo de Vida</h2>
                        <p class="card-description">Factores que afectan tu metabolismo</p>
                    </div>
                    <div class="card-content space-y-6">
                        <div class="grid-2">
                            <!-- Horas de sueño -->
                            <div class="form-group">
                                <label for="horas-sueno" class="form-label">
                                    Horas de sueño/noche: <span id="horas-sueno-value">7</span>h
                                </label>
                                <input type="range" class="slider" id="horas-sueno" min="4" max="10" value="7" step="0.5">
                            </div>

                            <!-- Tipo de trabajo -->
                            <div class="form-group">
                                <label for="tipo-trabajo" class="form-label">Tipo de trabajo</label>
                                <div class="radio-group">
                                    <label class="radio-item">
                                        <input type="radio" name="tipo-trabajo" value="sedentario" class="radio-input" checked>
                                        <span>Sedentario</span>
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio" name="tipo-trabajo" value="activo" class="radio-input">
                                        <span>Activo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Objetivos -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Objetivos</h2>
                        <p class="card-description">Define tu meta y ritmo de progreso</p>
                    </div>
                    <div class="card-content space-y-6">
                        <!-- Goal Cards -->
                        <div class="goal-cards">
                            <button type="button" class="goal-card" data-goal="deficit" onclick="selectGoal('deficit')">
                                <div class="goal-card-bg gradient-deficit"></div>
                                <div class="goal-card-content">
                                    <i data-lucide="trending-down" class="goal-card-icon"></i>
                                    <div class="goal-card-title">Déficit</div>
                                    <div class="goal-card-description">Perder grasa</div>
                                </div>
                            </button>

                            <button type="button" class="goal-card selected" data-goal="maintenance" onclick="selectGoal('maintenance')">
                                <div class="goal-card-bg gradient-maintenance"></div>
                                <div class="goal-card-content">
                                    <i data-lucide="minus" class="goal-card-icon"></i>
                                    <div class="goal-card-title">Mantenimiento</div>
                                    <div class="goal-card-description">Peso estable</div>
                                </div>
                            </button>

                            <button type="button" class="goal-card" data-goal="volume" onclick="selectGoal('volume')">
                                <div class="goal-card-bg gradient-volume"></div>
                                <div class="goal-card-content">
                                    <i data-lucide="trending-up" class="goal-card-icon"></i>
                                    <div class="goal-card-title">Volumen</div>
                                    <div class="goal-card-description">Ganar músculo</div>
                                </div>
                            </button>
                        </div>

                        <!-- Campos adicionales para déficit/volumen (ocultos por defecto) -->
                        <div id="goal-options" class="hidden">
                            <div class="form-group">
                                <label for="kg-objetivo" class="form-label">
                                    Cambio semanal objetivo: <span id="kg-objetivo-value">0.5</span> kg/semana
                                </label>
                                <input type="range" class="slider" id="kg-objetivo" min="0.25" max="1" value="0.5" step="0.25">
                                <p style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.5rem;">
                                    <span id="goal-recommendation">Recomendado: 0.5-0.75 kg/semana</span>
                                </p>
                            </div>

                            <div class="form-group">
                                <label for="semanas-objetivo" class="form-label">
                                    Duración del plan: <span id="semanas-objetivo-value">12</span> semanas
                                </label>
                                <input type="range" class="slider" id="semanas-objetivo" min="4" max="24" value="12" step="1">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Calcular -->
                <button type="button" class="btn btn-primary btn-lg" id="btn-calcular" onclick="calcularPlan()">
                    <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
                    Calcular Plan Nutricional
                </button>

                <!-- Botones adicionales (aparecen después de calcular) -->
                <div id="action-buttons" class="hidden" style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline" onclick="guardarPlan()" style="flex: 1;">
                        <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                        Guardar
                    </button>
                    <button type="button" class="btn btn-outline" onclick="descargarPDF()" style="flex: 1;">
                        <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                        PDF
                    </button>
                </div>
            </div>

            <!-- COLUMNA DERECHA: RESULTADOS -->
            <div class="results-sidebar">
                <div id="results-container">
                    <!-- Estado inicial: vacío -->
                    <div class="card info-card">
                        <div class="card-content">
                            <div class="empty-state">
                                <i data-lucide="calculator" class="empty-state-icon"></i>
                                <h3 class="empty-state-title">Completa el formulario</h3>
                                <p class="empty-state-description">
                                    Rellena todos los campos y haz clic en calcular para ver tu plan personalizado
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Inicializar Lucide icons
        lucide.createIcons();

        // Variables globales
        let currentGoal = 'maintenance';
        let results = null;

        // Actualizar valores de sliders
        document.querySelectorAll('input[type="range"]').forEach(slider => {
            slider.addEventListener('input', (e) => {
                const valueSpan = document.getElementById(e.target.id + '-value');
                if (valueSpan) {
                    valueSpan.textContent = e.target.value;
                }
            });
        });

        // Función para seleccionar objetivo
        function selectGoal(goal) {
            currentGoal = goal;

            // Actualizar UI
            document.querySelectorAll('.goal-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-goal="${goal}"]`).classList.add('selected');

            // Mostrar/ocultar opciones adicionales
            const goalOptions = document.getElementById('goal-options');
            const goalRecommendation = document.getElementById('goal-recommendation');

            if (goal === 'maintenance') {
                goalOptions.classList.add('hidden');
            } else {
                goalOptions.classList.remove('hidden');
                if (goal === 'deficit') {
                    goalRecommendation.textContent = 'Recomendado: 0.5-0.75 kg/semana para preservar músculo';
                } else {
                    goalRecommendation.textContent = 'Recomendado: 0.25-0.5 kg/semana para minimizar grasa';
                }
            }
        }

        // Función para calcular el plan
        function calcularPlan() {
            // Obtener valores del formulario
            const edad = parseInt(document.getElementById('edad').value);
            const sexo = document.querySelector('input[name="sexo"]:checked').value;
            const peso = parseFloat(document.getElementById('peso').value);
            const altura = parseInt(document.getElementById('altura').value);
            const diasGym = parseInt(document.getElementById('dias-gym').value);
            const horasGym = parseFloat(document.getElementById('horas-gym').value);
            const diasCardio = parseInt(document.getElementById('dias-cardio').value);
            const horasCardio = parseFloat(document.getElementById('horas-cardio').value);
            const horasSueno = parseFloat(document.getElementById('horas-sueno').value);
            const tipoTrabajo = document.querySelector('input[name="tipo-trabajo"]:checked').value;

            // Calcular TMB (Mifflin-St Jeor)
            let tmb;
            if (sexo === 'hombre') {
                tmb = (10 * peso) + (6.25 * altura) - (5 * edad) + 5;
            } else {
                tmb = (10 * peso) + (6.25 * altura) - (5 * edad) - 161;
            }

            // Calcular TDEE
            let tdee = tmb * 1.2; // Factor base sedentario

            // Ajustar por actividad física
            tdee += diasGym * horasGym * 100; // ~100 cal por hora de gym
            tdee += diasCardio * horasCardio * 200; // ~200 cal por hora de cardio

            // Ajustar por tipo de trabajo
            if (tipoTrabajo === 'activo') {
                tdee += 200;
            }

            // Ajustar por sueño (penalización si duerme poco)
            if (horasSueno < 7) {
                tdee *= 0.95;
            }

            // Calcular calorías objetivo
            let targetCalories = tdee;
            let weeklyChange = 0;

            if (currentGoal === 'deficit' || currentGoal === 'volume') {
                weeklyChange = parseFloat(document.getElementById('kg-objetivo').value);
                const calorieAdjustment = weeklyChange * 1100; // ~1100 cal por kg

                if (currentGoal === 'deficit') {
                    targetCalories = tdee - calorieAdjustment;
                } else {
                    targetCalories = tdee + calorieAdjustment;
                }
            }

            // Calcular macros
            const protein = peso * 2.2; // 2.2g por kg
            const fats = peso * 1; // 1g por kg
            const proteinCals = protein * 4;
            const fatCals = fats * 9;
            const carbCals = targetCalories - proteinCals - fatCals;
            const carbs = carbCals / 4;

            // Crear fases
            const weeksPerPhase = Math.ceil((document.getElementById('semanas-objetivo')?.value || 12) / 3);
            const phases = [];

            if (currentGoal === 'deficit') {
                phases.push(
                    {
                        name: 'Fase 1: Adaptación',
                        weeks: weeksPerPhase,
                        calories: Math.round(tdee - (weeklyChange * 1100 * 0.5)),
                        description: 'Reducción gradual para adaptación metabólica'
                    },
                    {
                        name: 'Fase 2: Déficit Activo',
                        weeks: weeksPerPhase,
                        calories: Math.round(targetCalories),
                        description: 'Déficit completo para pérdida de grasa óptima'
                    },
                    {
                        name: 'Fase 3: Finalización',
                        weeks: weeksPerPhase,
                        calories: Math.round(targetCalories + (weeklyChange * 1100 * 0.25)),
                        description: 'Ajuste fino para los últimos kilos'
                    }
                );
            } else if (currentGoal === 'volume') {
                phases.push(
                    {
                        name: 'Fase 1: Construcción',
                        weeks: weeksPerPhase,
                        calories: Math.round(targetCalories),
                        description: 'Superávit controlado para ganancia muscular'
                    },
                    {
                        name: 'Fase 2: Aceleración',
                        weeks: weeksPerPhase,
                        calories: Math.round(targetCalories + (weeklyChange * 1100 * 0.2)),
                        description: 'Aumento progresivo para máximo crecimiento'
                    },
                    {
                        name: 'Fase 3: Consolidación',
                        weeks: weeksPerPhase,
                        calories: Math.round(targetCalories),
                        description: 'Estabilización de ganancias'
                    }
                );
            } else {
                phases.push({
                    name: 'Mantenimiento',
                    weeks: 12,
                    calories: Math.round(targetCalories),
                    description: 'Calorías de mantenimiento para composición estable'
                });
            }

            // Guardar resultados
            results = {
                tmb: Math.round(tmb),
                tdee: Math.round(tdee),
                targetCalories: Math.round(targetCalories),
                protein: Math.round(protein),
                fats: Math.round(fats),
                carbs: Math.round(carbs),
                phases: phases
            };

            // Mostrar resultados
            mostrarResultados(results);

            // Mostrar botones de acción
            document.getElementById('action-buttons').classList.remove('hidden');
        }

        // Función para mostrar resultados
        function mostrarResultados(data) {
            const goalConfig = {
                deficit: { gradient: 'gradient-deficit', icon: 'trending-down', label: 'Déficit Calórico' },
                volume: { gradient: 'gradient-volume', icon: 'trending-up', label: 'Volumen' },
                maintenance: { gradient: 'gradient-maintenance', icon: 'minus', label: 'Mantenimiento' }
            };

            const config = goalConfig[currentGoal];

            const html = `
                <!-- Card principal con gradient -->
                <div class="card result-card-gradient ${config.gradient}" style="margin-bottom: 1rem;">
                    <div class="card-header" style="color: white; border-bottom-color: rgba(255,255,255,0.2);">
                        <h2 class="card-title" style="color: white;">
                            <i data-lucide="${config.icon}" style="width: 20px; height: 20px;"></i>
                            ${config.label}
                        </h2>
                        <p class="card-description" style="color: rgba(255,255,255,0.8);">Tu plan personalizado</p>
                    </div>
                    <div class="card-content">
                        <div class="result-metrics">
                            <div class="metric-box">
                                <div class="metric-label">TMB</div>
                                <div class="metric-value">${data.tmb}</div>
                                <div class="metric-unit">kcal/día</div>
                            </div>
                            <div class="metric-box">
                                <div class="metric-label">TDEE</div>
                                <div class="metric-value">${data.tdee}</div>
                                <div class="metric-unit">kcal/día</div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="target-calories">
                            <div style="font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.5rem;">Calorías Objetivo</div>
                            <div class="target-value">${data.targetCalories}</div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">kcal/día</div>
                        </div>
                    </div>
                </div>

                <!-- Card de Macros -->
                <div class="card" style="margin-bottom: 1rem;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 1.125rem;">Distribución de Macros</h3>
                    </div>
                    <div class="card-content">
                        <div class="macro-item">
                            <div>
                                <div class="macro-name">Proteína</div>
                                <div class="macro-percentage">${Math.round((data.protein * 4 / data.targetCalories) * 100)}% calorías</div>
                            </div>
                            <div class="text-right">
                                <div class="macro-value">${data.protein}g</div>
                                <div class="macro-calories">${data.protein * 4} kcal</div>
                            </div>
                        </div>
                        <div class="macro-item">
                            <div>
                                <div class="macro-name">Grasas</div>
                                <div class="macro-percentage">${Math.round((data.fats * 9 / data.targetCalories) * 100)}% calorías</div>
                            </div>
                            <div class="text-right">
                                <div class="macro-value">${data.fats}g</div>
                                <div class="macro-calories">${data.fats * 9} kcal</div>
                            </div>
                        </div>
                        <div class="macro-item">
                            <div>
                                <div class="macro-name">Carbohidratos</div>
                                <div class="macro-percentage">${Math.round((data.carbs * 4 / data.targetCalories) * 100)}% calorías</div>
                            </div>
                            <div class="text-right">
                                <div class="macro-value">${data.carbs}g</div>
                                <div class="macro-calories">${data.carbs * 4} kcal</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Fases -->
                <div class="card" style="margin-bottom: 1rem;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 1.125rem;">Fases del Plan</h3>
                    </div>
                    <div class="card-content">
                        ${data.phases.map((phase, index) => `
                            <div class="accordion-item">
                                <button type="button" class="accordion-trigger" onclick="toggleAccordion(${index})">
                                    <div style="text-align: left;">
                                        <div style="font-weight: 600;">${phase.name}</div>
                                        <div style="font-size: 0.75rem; color: var(--muted-foreground);">
                                            ${phase.weeks} semanas · ${phase.calories} kcal/día
                                        </div>
                                    </div>
                                    <i data-lucide="chevron-down" style="width: 16px; height: 16px;" id="accordion-icon-${index}"></i>
                                </button>
                                <div class="accordion-content" id="accordion-content-${index}">
                                    <p>${phase.description}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card info-card">
                    <div class="card-content">
                        <div class="info-content">
                            <i data-lucide="info" class="info-icon"></i>
                            <div class="info-text">
                                <p style="margin-bottom: 0.5rem;">Este plan es una estimación basada en ecuaciones científicas. Ajusta según tu progreso real.</p>
                                <p style="font-size: 0.75rem;">Consulta con un profesional de la salud antes de iniciar cualquier plan nutricional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('results-container').innerHTML = html;
            lucide.createIcons(); // Reinicializar iconos
        }

        // Función para toggle de accordion
        function toggleAccordion(index) {
            const content = document.getElementById(`accordion-content-${index}`);
            const icon = document.getElementById(`accordion-icon-${index}`);

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                icon.style.transform = 'rotate(0deg)';
            } else {
                // Cerrar todos los demás
                document.querySelectorAll('.accordion-content').forEach(c => c.classList.remove('open'));
                document.querySelectorAll('[id^="accordion-icon-"]').forEach(i => i.style.transform = 'rotate(0deg)');

                // Abrir este
                content.classList.add('open');
                icon.style.transform = 'rotate(180deg)';
            }
        }

        // Función para guardar plan
        function guardarPlan() {
            if (!results) {
                alert('Primero calcula un plan');
                return;
            }

            const nombre = document.getElementById('usuario_nombre').value;
            const apellidos = document.getElementById('usuario_apellidos').value;

            // Aquí puedes hacer una llamada AJAX a guardar.php
            // Por ahora solo mostramos un alert
            alert('Función de guardado - integrar con tu guardar.php existente');
        }

        // Función para descargar PDF
        function descargarPDF() {
            if (!results) {
                alert('Primero calcula un plan');
                return;
            }

            // Aquí puedes hacer una llamada a generar_pdf.php
            alert('Función de PDF - integrar con tu generar_pdf.php existente');
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
