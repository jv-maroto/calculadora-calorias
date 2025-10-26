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
    <title>Calculadora de Calorías - Mifflin-St Jeor</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- CSS original (para compatibilidad) -->
    <link rel="stylesheet" href="styles.css">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
        }

        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* Cards modernas estilo v0 */
        .v0-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .v0-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .v0-card-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .v0-card-header p {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }

        /* Inputs estilo v0 */
        .v0-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .v0-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Sliders estilo v0 */
        .v0-slider-container {
            margin-top: 0.5rem;
        }

        .v0-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(to right, var(--primary) 0%, var(--primary) 50%, #e2e8f0 50%, #e2e8f0 100%);
            outline: none;
            transition: all 0.2s;
        }

        .v0-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--primary);
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .v0-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }

        .v0-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--primary);
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .slider-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-align: right;
        }

        /* Radio buttons estilo v0 */
        .v0-radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 0.75rem;
        }

        .v0-radio-card {
            position: relative;
            cursor: pointer;
        }

        .v0-radio-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .v0-radio-label {
            display: block;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .v0-radio-card input[type="radio"]:checked + .v0-radio-label {
            border-color: var(--primary);
            background: #eef2ff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .v0-radio-card:hover .v0-radio-label {
            border-color: var(--primary);
        }

        /* Select estilo v0 */
        .v0-select {
            width: 100%;
            padding: 0.625rem 2.5rem 0.625rem 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9375rem;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 0.875rem center;
            background-size: 12px;
            appearance: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .v0-select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Labels */
        .v0-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .v0-helper {
            display: block;
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.375rem;
        }

        /* Botones estilo v0 */
        .v0-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .v0-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .v0-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }

        .v0-btn-secondary {
            background: white;
            color: #6366f1;
            border: 2px solid #e2e8f0;
        }

        .v0-btn-secondary:hover {
            border-color: #6366f1;
            background: #f8fafc;
        }

        /* Navbar */
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
        }

        .navbar-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .navbar-links a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 1.5rem;
        }

        .navbar-links a:hover {
            color: var(--primary);
        }

        /* Tab buttons */
        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f1f5f9;
            border-radius: 16px;
            margin-bottom: 1.5rem;
        }

        .tab-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 12px;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Grid de 2 columnas */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>
    <!-- Navbar moderna -->
    <div class="navbar-modern">
        <a href="index.php" class="navbar-brand">💪 Calculadora de Calorías</a>
        <div class="navbar-links">
            <span style="color: #1e293b; font-weight: 500; margin-right: 1rem;">
                👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?>
            </span>
            <a href="reverse_diet.php" title="Reverse Diet">🔄</a>
            <a href="rutinas.php" title="Rutinas">🏋️</a>
            <a href="introducir_peso.php" title="Introducir Peso">⚖️</a>
            <a href="seguimiento.php" title="Ajuste de Calorías">📊</a>
            <a href="logout.php" title="Cerrar Sesión">🚪</a>
        </div>
    </div>

    <input type="hidden" id="usuario_nombre" value="<?php echo htmlspecialchars($nombre); ?>">
    <input type="hidden" id="usuario_apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem 2rem;">
        <form id="calculadoraForm">

            <!-- Datos Personales -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="user" style="color: #6366f1;"></i>
                    <div>
                        <h3>Datos Personales</h3>
                        <p>Información básica para calcular tu metabolismo basal</p>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label class="v0-label">Edad (años)</label>
                        <input type="number" class="v0-input" id="edad" name="edad" required min="15" max="100" placeholder="25">
                    </div>

                    <div>
                        <label class="v0-label">Sexo</label>
                        <!-- Campo hidden para compatibilidad con script.js (SIN required porque se valida con radio) -->
                        <select id="sexo" name="sexo" style="display: none;">
                            <option value="">Seleccionar...</option>
                            <option value="hombre">Hombre</option>
                            <option value="mujer">Mujer</option>
                        </select>
                        <div class="v0-radio-group">
                            <div class="v0-radio-card">
                                <input type="radio" id="sexo-hombre" name="sexo-visual" value="hombre" required>
                                <label for="sexo-hombre" class="v0-radio-label">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">♂️</div>
                                    <div style="font-weight: 600;">Hombre</div>
                                </label>
                            </div>
                            <div class="v0-radio-card">
                                <input type="radio" id="sexo-mujer" name="sexo-visual" value="mujer" required>
                                <label for="sexo-mujer" class="v0-radio-label">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">♀️</div>
                                    <div style="font-weight: 600;">Mujer</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 1rem;">
                    <div>
                        <label class="v0-label">Peso (kg)</label>
                        <input type="number" class="v0-input" id="peso" name="peso" required min="30" max="300" step="0.1" placeholder="84">
                    </div>

                    <div>
                        <label class="v0-label">Altura (cm)</label>
                        <input type="number" class="v0-input" id="altura" name="altura" required min="100" max="250" placeholder="186">
                    </div>
                </div>
            </div>

            <!-- Datos Avanzados (Colapsable) -->
            <div class="v0-card">
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('datos-avanzados')">
                    <i data-lucide="flask-conical" style="color: #6366f1;"></i>
                    <div style="flex: 1;">
                        <h3>Datos Avanzados (Opcional)</h3>
                        <p>Para cálculos más precisos</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-datos-avanzados" style="color: #64748b;"></i>
                </div>

                <div id="datos-avanzados" style="display: none;">
                    <div class="grid-2">
                        <div>
                            <label class="v0-label">Años entrenando con pesas</label>
                            <select class="v0-select" id="anos_entrenando">
                                <option value="">No especificar</option>
                                <option value="0">Menos de 1 año (Novato)</option>
                                <option value="1">1-2 años (Principiante)</option>
                                <option value="2">2-3 años (Intermedio bajo)</option>
                                <option value="3">3-5 años (Intermedio)</option>
                                <option value="5">5-8 años (Avanzado)</option>
                                <option value="8">Más de 8 años (Muy avanzado)</option>
                            </select>
                            <small class="v0-helper">Años de entrenamiento constante (no esporádico)</small>
                        </div>

                        <div>
                            <label class="v0-label">Historial de dietas</label>
                            <select class="v0-select" id="historial_dietas">
                                <option value="">No especificar</option>
                                <option value="ninguna">Primera dieta seria</option>
                                <option value="pocas">1-2 dietas previas</option>
                                <option value="varias">3-5 dietas previas</option>
                                <option value="muchas">Más de 5 dietas (efecto yoyo)</option>
                            </select>
                            <small class="v0-helper">Dietas restrictivas pasadas (puede afectar metabolismo)</small>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <p style="font-weight: 600; color: #334155; margin-bottom: 0.75rem;">Circunferencias (opcional, para cálculo de grasa corporal)</p>
                        <div class="grid-2" style="margin-top: 0.75rem;">
                            <div>
                                <label class="v0-label">Cintura (cm)</label>
                                <input type="number" class="v0-input" id="circunferencia_cintura" min="50" max="200" step="0.1" placeholder="A nivel del ombligo">
                            </div>
                            <div>
                                <label class="v0-label">Cuello (cm)</label>
                                <input type="number" class="v0-input" id="circunferencia_cuello" min="20" max="60" step="0.1" placeholder="Debajo de la nuez">
                            </div>
                        </div>

                        <div id="campo-circunferencia-cadera" style="display: none; margin-top: 1rem;">
                            <label class="v0-label">Cadera (cm) - Solo mujeres</label>
                            <input type="number" class="v0-input" id="circunferencia_cadera" min="60" max="200" step="0.1" placeholder="En la parte más ancha">
                        </div>
                    </div>

                    <div id="campo-ciclo-menstrual" style="display: none; margin-top: 1rem;">
                        <label class="v0-label">Ciclo menstrual regular</label>
                        <select class="v0-select" id="ciclo_regular">
                            <option value="">No especificar</option>
                            <option value="si">Sí, ciclo regular</option>
                            <option value="no">No, irregular</option>
                            <option value="menopausia">Menopausia</option>
                            <option value="anticonceptivos">Uso anticonceptivos hormonales</option>
                        </select>
                        <small class="v0-helper">Afecta retención de líquidos y peso fluctuante</small>
                    </div>
                </div>
            </div>

            <!-- Actividad Física -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="dumbbell" style="color: #6366f1;"></i>
                    <div>
                        <h3>Actividad Física</h3>
                        <p>Describe tu rutina de entrenamiento semanal</p>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label class="v0-label">Días de gym/semana</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="dias_entreno" name="dias_entreno" min="0" max="7" step="1" value="5" oninput="updateSliderValue(this, 'dias_entreno_val')">
                        </div>
                        <div class="slider-value" id="dias_entreno_val">5</div>
                    </div>

                    <div>
                        <label class="v0-label">Horas por sesión</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="horas_gym" name="horas_gym" min="0" max="5" step="0.05" value="1.5" oninput="updateSliderValue(this, 'horas_gym_val', 'h')">
                        </div>
                        <div class="slider-value" id="horas_gym_val">1.5h</div>
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 1.5rem;">
                    <div>
                        <label class="v0-label">Días de cardio/semana</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="dias_cardio" name="dias_cardio" min="0" max="7" step="1" value="7" oninput="updateSliderValue(this, 'dias_cardio_val')">
                        </div>
                        <div class="slider-value" id="dias_cardio_val">7</div>
                    </div>

                    <div>
                        <label class="v0-label">Horas de cardio</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="horas_cardio" name="horas_cardio" min="0" max="3" step="0.05" value="1" oninput="updateSliderValue(this, 'horas_cardio_val', 'h')">
                        </div>
                        <div class="slider-value" id="horas_cardio_val">1h</div>
                    </div>
                </div>

                <div id="campo-tipo-cardio" style="display: block; margin-top: 1rem;">
                    <label class="v0-label">Tipo de cardio</label>
                    <select class="v0-select" id="tipo_cardio">
                        <option value="">Seleccionar tipo...</option>
                        <option value="caminar">🚶 Caminar (baja intensidad)</option>
                        <option value="caminar_rapido">🚶‍♂️ Caminar rápido (intensidad moderada)</option>
                        <option value="correr_ligero">🏃 Correr ligero (intensidad moderada-alta)</option>
                        <option value="correr_intenso">🏃‍♂️ Correr intenso (alta intensidad)</option>
                        <option value="bicicleta">🚴 Bicicleta (intensidad moderada)</option>
                        <option value="natacion">🏊 Natación (intensidad moderada-alta)</option>
                        <option value="eliptica">🏃‍♀️ Elíptica (intensidad moderada)</option>
                        <option value="otro">💪 Otro tipo de cardio</option>
                    </select>
                </div>
            </div>

            <!-- Estilo de Vida -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="briefcase" style="color: #6366f1;"></i>
                    <div>
                        <h3>Estilo de Vida</h3>
                        <p>Factores que afectan tu gasto energético diario</p>
                    </div>
                </div>

                <div>
                    <label class="v0-label">Tipo de trabajo</label>
                    <!-- Select hidden para compatibilidad con script.js (SIN required porque se valida con radio) -->
                    <select id="tipo_trabajo" name="tipo_trabajo" style="display: none;">
                        <option value="">Seleccionar...</option>
                        <option value="sedentario">Oficina/Sedentario</option>
                        <option value="activo">Activo/Te mueves</option>
                    </select>
                    <div class="v0-radio-group">
                        <div class="v0-radio-card">
                            <input type="radio" id="trabajo-sedentario" name="trabajo-visual" value="sedentario" required>
                            <label for="trabajo-sedentario" class="v0-radio-label">
                                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💼</div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">Sedentario</div>
                                <div style="font-size: 0.75rem; color: #64748b;">Oficina, sentado</div>
                            </label>
                        </div>
                        <div class="v0-radio-card">
                            <input type="radio" id="trabajo-activo" name="trabajo-visual" value="activo" required>
                            <label for="trabajo-activo" class="v0-radio-label">
                                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📈</div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">Activo</div>
                                <div style="font-size: 0.75rem; color: #64748b;">De pie, físico</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 1.5rem;">
                    <div>
                        <label class="v0-label">Horas de trabajo/día</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="horas_trabajo" name="horas_trabajo" min="0" max="16" step="0.5" value="7" oninput="updateSliderValue(this, 'horas_trabajo_val', 'h')">
                        </div>
                        <div class="slider-value" id="horas_trabajo_val">7h</div>
                    </div>

                    <div>
                        <label class="v0-label">Horas de sueño/noche</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="horas_sueno" name="horas_sueno" min="4" max="12" step="0.5" value="7" oninput="updateSliderValue(this, 'horas_sueno_val', 'h')">
                        </div>
                        <div class="slider-value" id="horas_sueno_val">7h</div>
                    </div>
                </div>
            </div>

            <!-- Objetivos -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="target" style="color: #6366f1;"></i>
                    <div>
                        <h3>Objetivos</h3>
                        <p>Define tu meta nutricional y parámetros específicos</p>
                    </div>
                </div>

                <!-- Tabs de objetivo -->
                <div class="tab-buttons">
                    <button type="button" class="tab-btn active" onclick="cambiarObjetivo('deficit')" id="btn-deficit">
                        <i data-lucide="trending-down" style="width: 18px; height: 18px;"></i>
                        DÉFICIT
                    </button>
                    <button type="button" class="tab-btn" onclick="cambiarObjetivo('mantenimiento')" id="btn-mantenimiento">
                        <i data-lucide="minus" style="width: 18px; height: 18px;"></i>
                        MANTENIMIENTO
                    </button>
                    <button type="button" class="tab-btn" onclick="cambiarObjetivo('volumen')" id="btn-volumen">
                        <i data-lucide="trending-up" style="width: 18px; height: 18px;"></i>
                        VOLUMEN
                    </button>
                </div>

                <input type="hidden" id="objetivo" name="objetivo" value="deficit" required>

                <!-- Campos de Déficit -->
                <div id="campos-deficit">
                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">¿Vienes de una etapa de volumen?</label>
                        <select class="v0-select" id="vengo_de_volumen">
                            <option value="no" selected>No, vengo de mantenimiento o déficit</option>
                            <option value="si">Sí, vengo de volumen (metabolismo acelerado)</option>
                        </select>
                        <small class="v0-helper">Esto afecta tu TDEE y límites de déficit</small>
                    </div>

                    <div id="campo-calorias-volumen" style="display: none; margin-bottom: 1rem;">
                        <label class="v0-label">¿De cuántas calorías vienes?</label>
                        <input type="number" class="v0-input" id="calorias_volumen" min="1500" max="6000" step="50" placeholder="Ej: 3500">
                        <small class="v0-helper">Las calorías que consumías en volumen</small>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="v0-label">¿Cuántos kg quieres perder?</label>
                            <input type="number" class="v0-input" id="kg_perder" name="kg_perder" min="1" max="50" step="0.5" placeholder="10">
                        </div>
                        <div>
                            <label class="v0-label">¿En cuántas semanas? (opcional)</label>
                            <input type="number" class="v0-input" id="semanas_objetivo_deficit" min="1" max="100" placeholder="Automático">
                            <small class="v0-helper">Si tienes una fecha límite</small>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <label class="v0-label">Preferencia de déficit</label>
                        <select class="v0-select" id="preferencia_deficit">
                            <option value="conservador">Muy conservador (preservar músculo al máximo)</option>
                            <option value="saludable" selected>Saludable y sostenible</option>
                            <option value="rapido">Rápido (requiere disciplina)</option>
                            <option value="agresivo">⚠️ Agresivo (bajo mi responsabilidad)</option>
                        </select>
                        <small class="v0-helper">La opción agresiva puede afectar músculo y salud</small>
                    </div>
                </div>

                <!-- Campos de Volumen -->
                <div id="campos-volumen" style="display: none;">
                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Nivel de entrenamiento</label>
                        <!-- Select hidden para compatibilidad con script.js -->
                        <select id="nivel_gym" name="nivel_gym" style="display: none;">
                            <option value="principiante">Principiante (0-1 año)</option>
                            <option value="intermedio" selected>Intermedio (1-3 años)</option>
                            <option value="avanzado">Avanzado (3+ años)</option>
                        </select>
                        <div class="v0-radio-group">
                            <div class="v0-radio-card">
                                <input type="radio" id="nivel-principiante" name="nivel-visual" value="principiante">
                                <label for="nivel-principiante" class="v0-radio-label">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Principiante</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">0-18 meses</div>
                                </label>
                            </div>
                            <div class="v0-radio-card">
                                <input type="radio" id="nivel-intermedio" name="nivel-visual" value="intermedio" checked>
                                <label for="nivel-intermedio" class="v0-radio-label">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Intermedio</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">1.5-3 años</div>
                                </label>
                            </div>
                            <div class="v0-radio-card">
                                <input type="radio" id="nivel-avanzado" name="nivel-visual" value="avanzado">
                                <label for="nivel-avanzado" class="v0-radio-label">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Avanzado</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">3+ años</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label class="v0-label">Meses de volumen</label>
                        <div class="v0-slider-container">
                            <input type="range" class="v0-slider" id="meses_volumen" name="meses_volumen" min="1" max="24" step="1" value="24" oninput="updateSliderValue(this, 'meses_volumen_val', ' meses')">
                        </div>
                        <div class="slider-value" id="meses_volumen_val">24 meses</div>
                        <small class="v0-helper">💡 Los profesionales planifican por tiempo, no por kg exactos de músculo</small>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">Tipo de volumen (% de superávit sobre TDEE)</label>
                        <select class="v0-select" id="preferencia_volumen">
                            <option value="ultra_limpio">Ultra Limpio - 8-10% superávit (200-250 kcal)</option>
                            <option value="limpio" selected>Lean Bulk Óptimo ⭐ - 10-12% superávit (300-350 kcal)</option>
                            <option value="balanceado">Balanceado - 13-17% superávit (400-500 kcal)</option>
                            <option value="agresivo">Agresivo - 20%+ superávit (600+ kcal)</option>
                        </select>
                        <small class="v0-helper">El superávit determina la velocidad de ganancia y ratio músculo/grasa</small>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="v0-label">¿Incluir mini-cuts?</label>
                        <select class="v0-select" id="incluir_minicuts">
                            <option value="si" selected>Sí, incluir mini-cuts (controlar grasa acumulada)</option>
                            <option value="no">No, solo volumen continuo</option>
                        </select>
                        <small class="v0-helper">Mini-cuts = 2-3 semanas de déficit para perder grasa acumulada sin perder músculo</small>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div style="display: grid; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="v0-btn v0-btn-primary">
                    <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
                    Calcular Plan Personalizado
                </button>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <button type="button" class="v0-btn v0-btn-secondary" id="btn-cargar" onclick="mostrarPlanesGuardados()">
                        <i data-lucide="folder-open" style="width: 18px; height: 18px;"></i>
                        Cargar
                    </button>
                    <button type="button" class="v0-btn v0-btn-secondary" id="btn-guardar" style="display: none;">
                        <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                        Guardar
                    </button>
                    <button type="button" class="v0-btn v0-btn-secondary" id="btn-pdf" style="display: none;" disabled>
                        <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                        PDF
                    </button>
                </div>
            </div>
        </form>

        <!-- Resultados (mantiene la estructura original) -->
        <div id="resultados" style="display: none; margin-top: 2rem;">
            <!-- Aquí van los resultados originales de tu código -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">📊 Resultados Básicos</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-info">
                                <strong>TMB (Metabolismo Basal)</strong>
                                <h3 class="mb-0" id="tmb">0 kcal/día</h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning">
                                <strong>TDEE (Gasto Total Diario)</strong>
                                <h3 class="mb-0" id="tdee">0 kcal/día</h3>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card text-white bg-danger">
                                <div class="card-header">🔽 Déficit</div>
                                <div class="card-body">
                                    <h4 class="card-title" id="deficit">0 kcal/día</h4>
                                    <p class="card-text small mb-0">Pérdida de peso</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-header">⚖️ Mantenimiento</div>
                                <div class="card-body">
                                    <h4 class="card-title" id="mantenimiento">0 kcal/día</h4>
                                    <p class="card-text small mb-0">Mantener peso</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-header">🔼 Volumen</div>
                                <div class="card-body">
                                    <h4 class="card-title" id="volumen">0 kcal/día</h4>
                                    <p class="card-text small mb-0">Ganancia muscular</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan de Déficit -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">📉 Plan de Déficit (Pérdida de Grasa)</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>🎯 Calorías por Fase</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Inicio (Semana 1-4):</strong></td>
                                    <td id="deficit-inicio" class="text-end">0 kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Medio (Semana 5-8):</strong></td>
                                    <td id="deficit-medio" class="text-end">0 kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Final (Semana 9-12):</strong></td>
                                    <td id="deficit-final" class="text-end">0 kcal</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>🔄 Ajustes Recomendados</h5>
                            <div id="deficit-ajustes">
                                <div class="alert alert-warning small mb-2">
                                    <strong>Refeed:</strong> Cada 7 días, come a mantenimiento
                                </div>
                                <div class="alert alert-info small mb-2">
                                    <strong>Cardio:</strong> <span id="deficit-cardio-ajuste"></span>
                                </div>
                                <div class="alert alert-secondary small mb-0">
                                    <strong>Entrenamiento:</strong> Mantén intensidad alta
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5>🍽️ Distribución de Macros</h5>
                        <div class="row" id="deficit-macros"></div>
                    </div>
                </div>
            </div>

            <!-- Plan de Volumen -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📈 Plan de Volumen (Ganancia Muscular)</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>🎯 Calorías por Fase</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Inicio (Semana 1-4):</strong></td>
                                    <td id="volumen-inicio" class="text-end">0 kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Medio (Semana 5-8):</strong></td>
                                    <td id="volumen-medio" class="text-end">0 kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Avanzado (Semana 9+):</strong></td>
                                    <td id="volumen-avanzado" class="text-end">0 kcal</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>🔄 Ajustes Recomendados</h5>
                            <div id="volumen-ajustes">
                                <div class="alert alert-warning small mb-2">
                                    <strong>Mini-cut:</strong> <span id="volumen-minicut"></span>
                                </div>
                                <div class="alert alert-info small mb-2">
                                    <strong>Cardio:</strong> <span id="volumen-cardio-ajuste"></span>
                                </div>
                                <div class="alert alert-success small mb-0">
                                    <strong>Entrenamiento:</strong> <span id="volumen-entreno-ajuste"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h5>🍽️ Distribución de Macros</h5>
                        <div class="row" id="volumen-macros"></div>
                    </div>
                </div>
            </div>

            <!-- Recomendaciones Nutricionales -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">🥗 Recomendaciones Nutricionales</h4>
                </div>
                <div class="card-body" id="recomendaciones-nutricion">
                </div>
            </div>
        </div>

        <!-- Mensaje inicial -->
        <div id="mensaje-inicial" class="text-center py-5">
            <div class="v0-card" style="text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📊</div>
                <h2 style="color: #1e293b; margin-bottom: 0.5rem;">Completa el formulario</h2>
                <p style="color: #64748b;">Introduce tus datos para obtener tu plan personalizado de nutrición</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        // Función para actualizar valores de sliders
        function updateSliderValue(slider, targetId, suffix = '') {
            const value = parseFloat(slider.value);
            const displayValue = value % 1 === 0 ? value : value.toFixed(2);
            document.getElementById(targetId).textContent = displayValue + suffix;

            // Actualizar gradiente del slider
            const percent = ((value - slider.min) / (slider.max - slider.min)) * 100;
            slider.style.background = `linear-gradient(to right, #6366f1 0%, #6366f1 ${percent}%, #e2e8f0 ${percent}%, #e2e8f0 100%)`;
        }

        // Inicializar todos los sliders al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            // Días de entreno
            updateSliderValue(document.getElementById('dias_entreno'), 'dias_entreno_val', '');

            // Horas gym
            updateSliderValue(document.getElementById('horas_gym'), 'horas_gym_val', 'h');

            // Días cardio
            updateSliderValue(document.getElementById('dias_cardio'), 'dias_cardio_val', '');

            // Horas cardio
            updateSliderValue(document.getElementById('horas_cardio'), 'horas_cardio_val', 'h');

            // Horas trabajo
            updateSliderValue(document.getElementById('horas_trabajo'), 'horas_trabajo_val', 'h');

            // Horas sueño
            updateSliderValue(document.getElementById('horas_sueno'), 'horas_sueno_val', 'h');

            // Meses volumen
            updateSliderValue(document.getElementById('meses_volumen'), 'meses_volumen_val', ' meses');
        });

        // Función para toggle de secciones
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            if (section.style.display === 'none') {
                section.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                section.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Función para cambiar objetivo
        function cambiarObjetivo(objetivo) {
            // Actualizar tabs
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + objetivo).classList.add('active');

            // Actualizar campo hidden
            document.getElementById('objetivo').value = objetivo;

            // Mostrar/ocultar campos
            document.getElementById('campos-deficit').style.display = objetivo === 'deficit' ? 'block' : 'none';
            document.getElementById('campos-volumen').style.display = objetivo === 'volumen' ? 'block' : 'none';

            // Reinicializar iconos
            lucide.createIcons();
        }

        // Sincronizar radio buttons visuales con select hidden de sexo
        document.querySelectorAll('input[name="sexo-visual"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Actualizar select hidden para compatibilidad con script.js
                document.getElementById('sexo').value = this.value;

                // Trigger change event en el select para que script.js lo detecte
                const event = new Event('change');
                document.getElementById('sexo').dispatchEvent(event);
            });

            // Si está checked al cargar, sincronizar inmediatamente
            if (radio.checked) {
                document.getElementById('sexo').value = radio.value;
            }
        });

        // Mostrar campo de calorías si viene de volumen
        document.getElementById('vengo_de_volumen').addEventListener('change', function() {
            const campoCalorias = document.getElementById('campo-calorias-volumen');
            campoCalorias.style.display = this.value === 'si' ? 'block' : 'none';
        });

        // Mostrar/ocultar campo tipo cardio
        document.getElementById('dias_cardio').addEventListener('input', function() {
            const campoTipoCardio = document.getElementById('campo-tipo-cardio');
            campoTipoCardio.style.display = this.value > 0 ? 'block' : 'none';
        });

        // Sincronizar radio buttons de tipo de trabajo con select hidden
        document.querySelectorAll('input[name="trabajo-visual"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('tipo_trabajo').value = this.value;
            });

            // Si está checked al cargar, sincronizar inmediatamente
            if (radio.checked) {
                document.getElementById('tipo_trabajo').value = radio.value;
            }
        });

        // Sincronizar radio buttons de nivel gym con select hidden
        document.querySelectorAll('input[name="nivel-visual"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('nivel_gym').value = this.value;
            });

            // Si está checked al cargar, sincronizar inmediatamente
            if (radio.checked) {
                document.getElementById('nivel_gym').value = radio.value;
            }
        });

        // Sincronizar selects hidden antes de enviar el formulario
        document.getElementById('calculadoraForm').addEventListener('submit', function(e) {
            // Sexo
            const sexoRadioChecked = document.querySelector('input[name="sexo-visual"]:checked');
            if (sexoRadioChecked) {
                document.getElementById('sexo').value = sexoRadioChecked.value;
            }

            // Tipo de trabajo
            const trabajoRadioChecked = document.querySelector('input[name="trabajo-visual"]:checked');
            if (trabajoRadioChecked) {
                document.getElementById('tipo_trabajo').value = trabajoRadioChecked.value;
            }

            // Nivel gym
            const nivelRadioChecked = document.querySelector('input[name="nivel-visual"]:checked');
            if (nivelRadioChecked) {
                document.getElementById('nivel_gym').value = nivelRadioChecked.value;
            }
        });
    </script>

    <!-- Scripts originales SIN CAMBIOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="limites_reales.js"></script>
    <script src="script.js"></script>
</body>
</html>
