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
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding-bottom: 80px;
        }

        /* Cards modernas estilo v0 */
        .v0-card {
            background: white;
            padding: 2rem;
            border: 1px solid #e5e5e5;
            margin-bottom: 1.5rem;
        }

        .v0-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .v0-card-header i[id^="icon-"] {
            transition: transform 0.2s ease;
        }

        .v0-card-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }

        .v0-card-header p {
            font-size: 0.875rem;
            color: #666;
            margin: 0;
        }

        /* Inputs estilo v0 */
        .v0-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #e5e5e5;
            font-size: 0.9375rem;
            transition: all 0.15s;
            background: white;
        }

        .v0-input:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        /* Sliders estilo v0 */
        .v0-slider-container {
            margin-top: 0.5rem;
            padding: 0.5rem 0;
        }

        .v0-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #1a1a1a 0%, #1a1a1a 50%, #e5e5e5 50%, #e5e5e5 100%);
            outline: none;
            transition: all 0.15s;
        }

        .v0-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #1a1a1a;
            cursor: pointer;
            transition: all 0.15s;
        }

        .v0-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }

        .v0-slider::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #1a1a1a;
            cursor: pointer;
            border: none;
        }

        .slider-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            text-align: right;
        }

        /* Radio buttons estilo v0 */
        .v0-radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        @media (max-width: 768px) {
            .v0-radio-group {
                grid-template-columns: 1fr;
            }
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
            border: 1px solid #e5e5e5;
            text-align: center;
            transition: all 0.15s;
            background: white;
        }

        .v0-radio-card input[type="radio"]:checked + .v0-radio-label {
            border-color: #1a1a1a;
            background: #fafafa;
        }

        .v0-radio-card:hover .v0-radio-label {
            border-color: #1a1a1a;
        }

        /* Select estilo v0 */
        .v0-select {
            width: 100%;
            padding: 0.625rem 2.5rem 0.625rem 0.875rem;
            border: 1px solid #e5e5e5;
            font-size: 0.9375rem;
            background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 0.875rem center;
            background-size: 12px;
            appearance: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .v0-select:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        /* Labels */
        .v0-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .v0-helper {
            display: block;
            font-size: 0.8125rem;
            color: #666;
            margin-top: 0.375rem;
        }

        /* Botones estilo v0 */
        .v0-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border: 1px solid #e5e5e5;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            color: #1a1a1a;
        }

        .v0-btn-primary {
            background: #1a1a1a;
            color: white;
            border-color: #1a1a1a;
        }

        .v0-btn-primary:hover {
            background: #000;
        }

        .v0-btn-secondary {
            background: white;
            color: #666;
            border: 1px solid #e5e5e5;
        }

        .v0-btn-secondary:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
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

            .v0-card {
                padding: 1rem;
            }

            .v0-card-header {
                margin-bottom: 1rem;
            }

            .v0-card-header h3 {
                font-size: 1rem;
            }

            .v0-card-header p {
                font-size: 0.75rem;
            }
        }

        @media (min-width: 768px) {
            body {
                padding-bottom: 2rem !important;
            }
            nav:first-of-type {
                display: flex !important;
            }
            nav:nth-of-type(2) {
                display: none !important;
            }
        }

        /* Tab buttons */
        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .tab-btn {
            flex: 1;
            padding: 0.75rem 0.5rem;
            border: none;
            background: transparent;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-bottom: 2px solid transparent;
            font-size: 0.875rem;
        }

        .tab-btn.active {
            color: #1a1a1a;
            border-bottom-color: #1a1a1a;
        }

        @media (max-width: 768px) {
            .tab-btn {
                flex-direction: column;
                gap: 0.25rem;
                padding: 0.75rem 0.25rem;
                font-size: 0.75rem;
            }

            .tab-btn svg, .tab-btn i {
                width: 16px !important;
                height: 16px !important;
            }
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

            /* Ajustes para resultados en móvil */
            #resultados .grid-2 > div {
                padding: 1rem !important;
            }

            #resultados .grid-2 > div > div:first-child {
                font-size: 0.75rem !important;
            }

            #resultados .grid-2 > div > div:nth-child(2) {
                font-size: 1.5rem !important;
            }

            /* Ajustar cards de déficit/mantenimiento/volumen */
            #resultados div[style*="repeat(auto-fit"] {
                grid-template-columns: 1fr !important;
            }

            #resultados div[style*="repeat(auto-fit"] > div {
                padding: 1rem !important;
            }

            #resultados div[style*="repeat(auto-fit"] > div > div:nth-child(2) {
                font-size: 1.25rem !important;
            }

            /* Tablas Bootstrap en móvil */
            .table {
                font-size: 0.875rem;
            }

            .table td {
                padding: 0.5rem 0.25rem;
            }

            h5 {
                font-size: 1rem !important;
            }

            .alert {
                padding: 0.5rem !important;
                font-size: 0.75rem !important;
                margin-bottom: 0.5rem !important;
            }

            .row {
                margin: 0 !important;
            }

            .col-md-6 {
                padding: 0 !important;
                margin-bottom: 1rem;
            }

            /* Tabla de fases en móvil - hacerla responsive */
            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
            }

            table thead,
            table tbody,
            table tr,
            table th,
            table td {
                display: table-cell;
            }

            table th,
            table td {
                font-size: 0.75rem !important;
                padding: 0.5rem 0.25rem !important;
            }

            table th:first-child,
            table td:first-child {
                position: sticky;
                left: 0;
                background: white;
                z-index: 1;
            }

            table thead th:first-child {
                background: #fafafa;
                z-index: 2;
            }

            table tbody tr[style*="background: #fafafa"] td:first-child {
                background: #fafafa;
            }

            /* Scroll hint */
            table::after {
                content: '← Desliza →';
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.75rem;
                color: #999;
                padding: 0.25rem 0.5rem;
                background: white;
                border-left: 1px solid #e5e5e5;
                pointer-events: none;
            }
        }

    </style>
</head>
<body>
    <!-- Top Nav - Desktop -->
    <nav style="display: none; background: white; border-bottom: 1px solid #e5e5e5; padding: 0 2rem; height: 60px; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; gap: 2rem;">
            <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">← Dashboard</a>
            <a href="diet_hub.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">DIET Hub</a>
            <a href="calculatorkcal.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Calculadora</a>
            <a href="introducir_peso_v0.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Peso</a>
            <a href="grafica_v0.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Gráfica</a>
            <a href="reverse_diet_v0.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Reverse Diet</a>
        </div>
        <a href="logout.php" style="color: #999; text-decoration: none; font-size: 14px; font-weight: 500;">Salir</a>
    </nav>

    <!-- Bottom Nav - Mobile -->
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e5e5e5; display: flex; justify-content: space-around; padding: 12px 0; z-index: 100;">
        <a href="dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Inicio</div>
        </a>
        <a href="diet_hub.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>DIET</div>
        </a>
        <a href="calculatorkcal.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #1a1a1a; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Calculadora</div>
        </a>
        <a href="grafica_v0.php" style="display: flex; flex-direction: column; align-items: center; gap: 4px; color: #999; text-decoration: none; font-size: 11px; font-weight: 500;">
            <div>Gráfica</div>
        </a>
    </nav>

    <input type="hidden" id="usuario_nombre" value="<?php echo htmlspecialchars($nombre); ?>">
    <input type="hidden" id="usuario_apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem 2rem;">
        <form id="calculadoraForm">

            <!-- Datos Personales -->
            <div class="v0-card">
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('datos-personales')">
                    <i data-lucide="user" style="color: #1a1a1a;"></i>
                    <div style="flex: 1;">
                        <h3>Datos Personales</h3>
                        <p>Información básica para calcular tu metabolismo basal</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-datos-personales" style="color: #666;"></i>
                </div>

                <div id="datos-personales">

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
                                    <div style="font-weight: 600;">Hombre</div>
                                </label>
                            </div>
                            <div class="v0-radio-card">
                                <input type="radio" id="sexo-mujer" name="sexo-visual" value="mujer" required>
                                <label for="sexo-mujer" class="v0-radio-label">
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
            </div>

            <!-- Datos Avanzados (Colapsable) -->
            <div class="v0-card">
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('datos-avanzados')">
                    <i data-lucide="flask-conical" style="color: #1a1a1a;"></i>
                    <div style="flex: 1;">
                        <h3>Datos Avanzados (Opcional)</h3>
                        <p>Para cálculos más precisos</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-datos-avanzados" style="color: #666;"></i>
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

                    <!-- PORCENTAJE DE GRASA CORPORAL -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e5e5;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <i data-lucide="percent" style="color: #1a1a1a; width: 20px; height: 20px;"></i>
                            <p style="font-weight: 600; color: #1a1a1a; margin: 0;">Porcentaje de Grasa Corporal</p>
                        </div>

                        <!-- Input manual -->
                        <div style="margin-bottom: 1rem;">
                            <label class="v0-label">¿Ya conoces tu % de grasa corporal?</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="number" class="v0-input" id="porcentaje_grasa_input" min="5" max="50" step="0.1" placeholder="Ej: 15.5" style="flex: 1;">
                                <div style="padding: 0.625rem 1rem; border: 1px solid #e5e5e5; background: #fafafa; display: flex; align-items: center; font-weight: 600; color: #1a1a1a;">%</div>
                            </div>
                            <small class="v0-helper">Si lo conoces, introdúcelo aquí. Si no, usa el botón de abajo.</small>
                        </div>

                        <!-- Botón calculadora Jackson-Pollock -->
                        <div style="text-align: center; margin: 1.5rem 0;">
                            <button type="button" class="v0-btn-secondary" onclick="abrirCalculadoraGrasa()" style="width: 100%; max-width: 500px;">
                                <i data-lucide="calculator" style="width: 18px; height: 18px;"></i>
                                Calcular con Método Jackson-Pollock (Pliegues)
                            </button>
                            <small class="v0-helper" style="display: block; margin-top: 0.5rem; text-align: center;">
                                Método científico más preciso (±3.5% error)
                            </small>
                        </div>

                        <!-- Separador -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0;">
                            <div style="flex: 1; height: 1px; background: #e5e5e5;"></div>
                            <span style="font-size: 0.8125rem; color: #666; font-weight: 500;">O usa circunferencias (menos preciso)</span>
                            <div style="flex: 1; height: 1px; background: #e5e5e5;"></div>
                        </div>

                        <!-- Circunferencias (Método Navy) -->
                        <div class="grid-2" style="margin-top: 1rem;">
                            <div>
                                <label class="v0-label">Cintura (cm)</label>
                                <input type="number" class="v0-input" id="circunferencia_cintura" min="50" max="200" step="0.1" placeholder="A nivel del ombligo">
                                <small class="v0-helper">Método Navy</small>
                            </div>
                            <div>
                                <label class="v0-label">Cuello (cm)</label>
                                <input type="number" class="v0-input" id="circunferencia_cuello" min="20" max="60" step="0.1" placeholder="Debajo de la nuez">
                                <small class="v0-helper">Método Navy</small>
                            </div>
                        </div>

                        <div id="campo-circunferencia-cadera" style="display: none; margin-top: 1rem;">
                            <label class="v0-label">Cadera (cm) - Solo mujeres</label>
                            <input type="number" class="v0-input" id="circunferencia_cadera" min="60" max="200" step="0.1" placeholder="En la parte más ancha">
                            <small class="v0-helper">Método Navy</small>
                        </div>

                        <!-- Resultado Navy -->
                        <div id="resultado-grasa-navy" style="display: none; margin-top: 1rem; padding: 1rem; border: 1px solid #dbeafe; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="info" style="width: 16px; height: 16px; color: #1e40af;"></i>
                                <strong style="color: #1e40af; font-size: 0.875rem;">% Grasa (Método Navy):</strong>
                                <span id="valor-grasa-navy" style="color: #1e3a8a; font-weight: 600; font-size: 0.875rem;"></span>
                            </div>
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
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('actividad-fisica')">
                    <i data-lucide="dumbbell" style="color: #1a1a1a;"></i>
                    <div style="flex: 1;">
                        <h3>Actividad Física</h3>
                        <p>Describe tu rutina de entrenamiento semanal</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-actividad-fisica" style="color: #666;"></i>
                </div>

                <div id="actividad-fisica" style="display: none;">

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
                        <option value="otro">Otro tipo de cardio</option>
                    </select>
                </div>
                </div>
            </div>

            <!-- Estilo de Vida -->
            <div class="v0-card">
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('estilo-vida')">
                    <i data-lucide="briefcase" style="color: #1a1a1a;"></i>
                    <div style="flex: 1;">
                        <h3>Estilo de Vida</h3>
                        <p>Factores que afectan tu gasto energético diario</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-estilo-vida" style="color: #666;"></i>
                </div>

                <div id="estilo-vida" style="display: none;">

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
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">Sedentario</div>
                                <div style="font-size: 0.75rem; color: #666;">Oficina, sentado</div>
                            </label>
                        </div>
                        <div class="v0-radio-card">
                            <input type="radio" id="trabajo-activo" name="trabajo-visual" value="activo" required>
                            <label for="trabajo-activo" class="v0-radio-label">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">Activo</div>
                                <div style="font-size: 0.75rem; color: #666;">De pie, físico</div>
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
            </div>

            <!-- Objetivos -->
            <div class="v0-card">
                <div class="v0-card-header" style="cursor: pointer;" onclick="toggleSection('objetivos')">
                    <i data-lucide="target" style="color: #1a1a1a;"></i>
                    <div style="flex: 1;">
                        <h3>Objetivos</h3>
                        <p>Define tu meta nutricional y parámetros específicos</p>
                    </div>
                    <i data-lucide="chevron-down" id="icon-objetivos" style="color: #666;"></i>
                </div>

                <div id="objetivos" style="display: none;">

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
                            <option value="recomendado" selected>🎯 Recomendado según tus datos (automático)</option>
                            <option value="ultra_limpio">Ultra Limpio - 8-10% superávit (200-250 kcal)</option>
                            <option value="limpio">Lean Bulk Óptimo ⭐ - 10-12% superávit (300-350 kcal)</option>
                            <option value="balanceado">Balanceado - 13-17% superávit (400-500 kcal)</option>
                            <option value="agresivo">Agresivo - 20%+ superávit (600+ kcal)</option>
                        </select>
                        <small class="v0-helper">El superávit determina la velocidad de ganancia y ratio músculo/grasa</small>
                        <div id="recomendacion-volumen" style="display: none; margin-top: 0.75rem; padding: 1rem; background: #eff6ff; border: 1px solid #3b82f6; font-size: 0.875rem; color: #1a1a1a;"></div>
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
            </div>

            <!-- Botones de acción -->
            <div style="display: grid; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="v0-btn v0-btn-primary">
                    <i data-lucide="calculator" style="width: 20px; height: 20px;"></i>
                    Calcular Plan Personalizado
                </button>
                <div style="display: flex; justify-content: center;">
                    <button type="button" class="v0-btn v0-btn-secondary" id="btn-cargar" onclick="mostrarPlanesGuardados()" style="max-width: 300px;">
                        <i data-lucide="folder-open" style="width: 18px; height: 18px;"></i>
                        Cargar Plan Guardado
                    </button>
                </div>
                <button type="button" class="v0-btn v0-btn-secondary" id="btn-guardar" style="display: none;">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Guardar
                </button>
                <button type="button" class="v0-btn v0-btn-secondary" id="btn-pdf" style="display: none;" disabled>
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                    PDF
                </button>
            </div>
        </form>

        <!-- Resultados (estilo minimalista) -->
        <div id="resultados" style="display: none; margin-top: 2rem;">
            <!-- Resultados Básicos -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="bar-chart-3" style="color: #1a1a1a;"></i>
                    <div>
                        <h3>Resultados Básicos</h3>
                        <p>Tus métricas calculadas</p>
                    </div>
                </div>
                <div style="padding-top: 1rem;">
                    <div class="grid-2" style="gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">TMB (Metabolismo Basal)</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a;" id="tmb">0 kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">TDEE (Gasto Total Diario)</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a;" id="tdee">0 kcal/día</div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Déficit</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;" id="deficit">0 kcal/día</div>
                            <div style="font-size: 0.75rem; color: #666;">Pérdida de peso</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Mantenimiento</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;" id="mantenimiento">0 kcal/día</div>
                            <div style="font-size: 0.75rem; color: #666;">Mantener peso</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Volumen</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;" id="volumen">0 kcal/día</div>
                            <div style="font-size: 0.75rem; color: #666;">Ganancia muscular</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan de Déficit -->
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="trending-down" style="color: #1a1a1a;"></i>
                    <div>
                        <h3>Plan de Déficit (Pérdida de Grasa)</h3>
                        <p>Calorías y macros para perder peso</p>
                    </div>
                </div>
                <div style="padding-top: 1rem;">
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
        <div id="mensaje-inicial" style="display: none;"></div>
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
            slider.style.background = `linear-gradient(to right, #1a1a1a 0%, #1a1a1a ${percent}%, #e5e5e5 ${percent}%, #e5e5e5 100%)`;
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

        // Auto-desplegar siguiente sección al completar campos
        function checkSectionComplete(sectionId, nextSectionId) {
            const section = document.getElementById(sectionId);
            const inputs = section.querySelectorAll('input[required], select[required]');
            let allFilled = true;

            inputs.forEach(input => {
                if (input.type === 'radio') {
                    const radioGroup = section.querySelectorAll(`input[name="${input.name}"]`);
                    const isChecked = Array.from(radioGroup).some(r => r.checked);
                    if (!isChecked) allFilled = false;
                } else if (!input.value) {
                    allFilled = false;
                }
            });

            if (allFilled && nextSectionId) {
                const nextSection = document.getElementById(nextSectionId);
                const nextIcon = document.getElementById('icon-' + nextSectionId);
                if (nextSection && nextSection.style.display === 'none') {
                    nextSection.style.display = 'block';
                    if (nextIcon) nextIcon.style.transform = 'rotate(180deg)';

                    // Scroll suave a la siguiente sección
                    setTimeout(() => {
                        nextSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                }
            }
        }

        // Escuchar cambios en Datos Personales
        document.addEventListener('DOMContentLoaded', () => {
            const datosPersonales = document.getElementById('datos-personales');
            datosPersonales.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('change', () => checkSectionComplete('datos-personales', 'datos-avanzados'));
                input.addEventListener('input', () => checkSectionComplete('datos-personales', 'datos-avanzados'));
            });

            // Datos Avanzados -> Actividad Física (no required, se despliega al hacer scroll o click)
            const btnActividad = document.querySelector('[onclick*="actividad-fisica"]');
            if (btnActividad) {
                btnActividad.addEventListener('click', () => {
                    checkSectionComplete('datos-personales', 'actividad-fisica');
                });
            }

            // Actividad Física -> Estilo de Vida
            const actividadFisica = document.getElementById('actividad-fisica');
            actividadFisica.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('change', () => checkSectionComplete('actividad-fisica', 'estilo-vida'));
                input.addEventListener('input', () => checkSectionComplete('actividad-fisica', 'estilo-vida'));
            });

            // Estilo de Vida -> Objetivos
            const estiloVida = document.getElementById('estilo-vida');
            estiloVida.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('change', () => checkSectionComplete('estilo-vida', 'objetivos'));
                input.addEventListener('input', () => checkSectionComplete('estilo-vida', 'objetivos'));
            });
        });
    </script>

    <!-- MODAL: Calculadora Jackson-Pollock -->
    <div class="modal fade" id="modalCalculadoraGrasa" tabindex="-1" aria-labelledby="modalCalculadoraGrasaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid #e5e5e5; box-shadow: 0 20px 60px rgba(0,0,0,0.1);">
                <!-- Header estilo v0 -->
                <div class="modal-header" style="background: #1a1a1a; color: white; padding: 1.5rem;">
                    <div>
                        <h5 class="modal-title" id="modalCalculadoraGrasaLabel" style="font-weight: 700; font-size: 1.5rem; display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
                            Calculadora de Grasa Corporal
                        </h5>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #e5e5e5;">Método Jackson-Pollock (Pliegues Cutáneos)</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body" style="padding: 2rem;">
                    <!-- Selector de método -->
                    <div class="mb-4">
                        <label class="v0-label">Selecciona el método:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="metodo-jp" id="metodo-jp-3" value="3" checked>
                            <label class="btn btn-outline-dark" for="metodo-jp-3" style="flex: 1; padding: 1rem;">
                                <strong>3 Pliegues</strong><br>
                                <small>Rápido y preciso</small>
                            </label>

                            <input type="radio" class="btn-check" name="metodo-jp" id="metodo-jp-7" value="7">
                            <label class="btn btn-outline-dark" for="metodo-jp-7" style="flex: 1; padding: 1rem;">
                                <strong>7 Pliegues</strong><br>
                                <small>Máxima precisión</small>
                            </label>
                        </div>
                    </div>

                    <!-- Formulario 3 Pliegues -->
                    <div id="form-jp-3" class="pliegues-form">
                        <div style="padding: 1rem; background: #eff6ff; border: 1px solid #dbeafe; margin-bottom: 1rem;">
                            <strong style="color: #1e40af;">📍 Pliegues para 3 sitios:</strong><br>
                            <span id="sitios-3-texto" style="color: #1e3a8a; font-size: 0.875rem;"></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="pliegue_1" class="v0-label" id="label-pliegue-1"></label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_1" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper" id="desc-pliegue-1"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_2" class="v0-label" id="label-pliegue-2"></label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_2" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper" id="desc-pliegue-2"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_3" class="v0-label" id="label-pliegue-3"></label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_3" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper" id="desc-pliegue-3"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario 7 Pliegues -->
                    <div id="form-jp-7" class="pliegues-form" style="display: none;">
                        <div style="padding: 1rem; background: #eff6ff; border: 1px solid #dbeafe; margin-bottom: 1rem;">
                            <strong style="color: #1e40af;">📍 Pliegues para 7 sitios:</strong> Pecho, Abdomen, Muslo, Tríceps, Subescapular, Suprailiaco, Axilar Media
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="pliegue_pecho" class="v0-label">Pecho</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_pecho" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Diagonal entre axila y pezón</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_abdomen" class="v0-label">Abdomen</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_abdomen" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Vertical, 2cm al lado del ombligo</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_muslo" class="v0-label">Muslo</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_muslo" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Vertical, parte frontal del muslo</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label for="pliegue_triceps" class="v0-label">Tríceps</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_triceps" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Vertical, parte trasera del brazo</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_subescapular" class="v0-label">Subescapular</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_subescapular" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Diagonal, bajo el omóplato</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pliegue_suprailiaco" class="v0-label">Suprailiaco</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_suprailiaco" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Diagonal, sobre cresta ilíaca</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label for="pliegue_axilar" class="v0-label">Axilar Media</label>
                                <div class="input-group">
                                    <input type="number" class="v0-input" id="pliegue_axilar" min="1" max="50" step="0.1" placeholder="mm">
                                    <span class="input-group-text">mm</span>
                                </div>
                                <small class="v0-helper">Horizontal, línea axilar media</small>
                            </div>
                        </div>
                    </div>

                    <!-- Resultado -->
                    <div id="resultado-jp" class="mt-4" style="display: none;">
                        <div style="border: 2px solid #1a1a1a; background: #fafafa; padding: 2rem; text-align: center;">
                            <h3 style="color: #1a1a1a; font-weight: 700; font-size: 2.5rem; margin: 0;">
                                <span id="resultado-jp-valor"></span>%
                            </h3>
                            <p style="color: #666; font-size: 1.125rem; margin: 0.5rem 0 0 0;">Porcentaje de Grasa Corporal</p>
                            <small style="color: #666; display: block; margin-top: 0.5rem;">
                                Método: <strong id="resultado-jp-metodo"></strong>
                            </small>
                        </div>

                        <div style="padding: 1rem; background: #fef3c7; border: 1px solid #fde68a; margin-top: 1rem;">
                            <strong style="color: #92400e;">⚠️ Importante:</strong>
                            <span style="color: #78350f; font-size: 0.875rem;"> Este resultado tiene un margen de error de ±3.5%. Para mayor precisión, realiza las mediciones 3 veces y promedia los valores.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer" style="padding: 1.5rem; background: #fafafa; border-top: 1px solid #e5e5e5;">
                    <button type="button" class="v0-btn-primary" onclick="calcularJacksonPollock()" style="flex: 1;">
                        <i data-lucide="calculator" style="width: 18px; height: 18px;"></i>
                        Calcular % Grasa
                    </button>
                    <button type="button" class="btn btn-success" id="btn-usar-resultado-jp" onclick="usarResultadoJP()" style="display: none; flex: 1; padding: 0.875rem; font-weight: 600;">
                        <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                        Usar este resultado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts originales SIN CAMBIOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="calculadora_grasa.js"></script>
    <script src="limites_reales.js"></script>
    <script src="script.js"></script>
</body>
</html>
