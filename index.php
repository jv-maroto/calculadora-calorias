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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">💪 Calculadora de Calorías</a>
            <span class="navbar-text text-white me-3">
                👤 <?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?>
            </span>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link" href="reverse_diet.php" title="Reverse Diet">🔄</a>
                <a class="nav-link" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>
    <input type="hidden" id="usuario_nombre" value="<?php echo htmlspecialchars($nombre); ?>">
    <input type="hidden" id="usuario_apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">

    <div class="container-fluid py-4">
        <div class="row">
            <!-- FORMULARIO - IZQUIERDA -->
            <div class="col-lg-4">
                <div class="card shadow-lg sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">🧮 Calculadora</h3>
                        <p class="mb-0 small">Sistema Mifflin-St Jeor</p>
                    </div>
                    <div class="card-body">
                        <form id="calculadoraForm">
                            <!-- Datos Personales -->
                            <div class="section-title">
                                <h5>📋 Datos Personales</h5>
                            </div>

                            <div class="mb-3">
                                <label for="edad" class="form-label">Edad (años)</label>
                                <input type="number" class="form-control" id="edad" name="edad" required min="15" max="100">
                            </div>

                            <div class="mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="hombre">Hombre</option>
                                    <option value="mujer">Mujer</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" id="peso" name="peso" required min="30" max="300" step="0.1">
                            </div>

                            <div class="mb-3">
                                <label for="altura" class="form-label">Altura (cm)</label>
                                <input type="number" class="form-control" id="altura" name="altura" required min="100" max="250">
                            </div>

                            <!-- Datos Avanzados (Opcionales) -->
                            <div class="section-title">
                                <h5>🔬 Datos Avanzados (Opcional)</h5>
                                <small class="text-muted">Para cálculos más precisos</small>
                            </div>

                            <div class="mb-3">
                                <label for="anos_entrenando" class="form-label">Años entrenando con pesas</label>
                                <select class="form-select" id="anos_entrenando">
                                    <option value="">No especificar</option>
                                    <option value="0">Menos de 1 año (Novato)</option>
                                    <option value="1">1-2 años (Principiante)</option>
                                    <option value="2">2-3 años (Intermedio bajo)</option>
                                    <option value="3">3-5 años (Intermedio)</option>
                                    <option value="5">5-8 años (Avanzado)</option>
                                    <option value="8">Más de 8 años (Muy avanzado)</option>
                                </select>
                                <small class="text-muted">Años de entrenamiento constante (no esporádico)</small>
                            </div>

                            <div class="mb-3">
                                <label for="circunferencia_cintura" class="form-label">Circunferencia de cintura (cm) - Opcional</label>
                                <input type="number" class="form-control" id="circunferencia_cintura" min="50" max="200" step="0.1" placeholder="A nivel del ombligo">
                                <small class="text-muted">Para estimación de grasa corporal (Método Navy)</small>
                            </div>

                            <div class="mb-3">
                                <label for="circunferencia_cuello" class="form-label">Circunferencia de cuello (cm) - Opcional</label>
                                <input type="number" class="form-control" id="circunferencia_cuello" min="20" max="60" step="0.1" placeholder="Debajo de la nuez">
                                <small class="text-muted">Para estimación de grasa corporal (Método Navy)</small>
                            </div>

                            <div class="mb-3" id="campo-circunferencia-cadera" style="display: none;">
                                <label for="circunferencia_cadera" class="form-label">Circunferencia de cadera (cm) - Opcional</label>
                                <input type="number" class="form-control" id="circunferencia_cadera" min="60" max="200" step="0.1" placeholder="En la parte más ancha">
                                <small class="text-muted">Solo para mujeres - estimación de grasa corporal</small>
                            </div>

                            <div class="mb-3">
                                <label for="historial_dietas" class="form-label">Historial de dietas</label>
                                <select class="form-select" id="historial_dietas">
                                    <option value="">No especificar</option>
                                    <option value="ninguna">Primera dieta seria</option>
                                    <option value="pocas">1-2 dietas previas</option>
                                    <option value="varias">3-5 dietas previas</option>
                                    <option value="muchas">Más de 5 dietas (efecto yoyo)</option>
                                </select>
                                <small class="text-muted">Dietas restrictivas pasadas (puede afectar metabolismo)</small>
                            </div>

                            <div class="mb-3" id="campo-ciclo-menstrual" style="display: none;">
                                <label class="form-label">Ciclo menstrual regular</label>
                                <select class="form-select" id="ciclo_regular">
                                    <option value="">No especificar</option>
                                    <option value="si">Sí, ciclo regular</option>
                                    <option value="no">No, irregular</option>
                                    <option value="menopausia">Menopausia</option>
                                    <option value="anticonceptivos">Uso anticonceptivos hormonales</option>
                                </select>
                                <small class="text-muted">Afecta retención de líquidos y peso fluctuante</small>
                            </div>

                            <!-- Actividad Física -->
                            <div class="section-title">
                                <h5>💪 Actividad Física</h5>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="dias_entreno" class="form-label">Días gym/sem</label>
                                    <input type="number" class="form-control" id="dias_entreno" name="dias_entreno" min="0" max="7" value="0">
                                </div>
                                <div class="col-6">
                                    <label for="horas_gym" class="form-label">Horas/sesión</label>
                                    <input type="number" class="form-control" id="horas_gym" name="horas_gym" min="0" max="5" step="0.01" value="0">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="dias_cardio" class="form-label">Días cardio/sem</label>
                                    <input type="number" class="form-control" id="dias_cardio" name="dias_cardio" min="0" max="7" value="0">
                                </div>
                                <div class="col-6">
                                    <label for="horas_cardio" class="form-label">Horas/sesión</label>
                                    <input type="number" class="form-control" id="horas_cardio" name="horas_cardio" min="0" max="3" step="0.01" value="0">
                                </div>
                            </div>

                            <div class="mb-3" id="campo-tipo-cardio" style="display: none;">
                                <label for="tipo_cardio" class="form-label">Tipo de cardio</label>
                                <select class="form-select" id="tipo_cardio">
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
                                <small class="text-muted">El tipo de cardio afecta el cálculo del factor de actividad</small>
                            </div>

                            <!-- Estilo de Vida -->
                            <div class="section-title">
                                <h5>🏢 Estilo de Vida</h5>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_trabajo" class="form-label">Tipo de trabajo</label>
                                <select class="form-select" id="tipo_trabajo" name="tipo_trabajo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="sedentario">Oficina/Sedentario</option>
                                    <option value="activo">Activo/Te mueves</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="horas_trabajo" class="form-label">Horas trabajo/día</label>
                                <input type="number" class="form-control" id="horas_trabajo" name="horas_trabajo" min="0" max="16" step="0.01" value="8">
                            </div>

                            <div class="mb-3">
                                <label for="horas_sueno" class="form-label">Horas sueño/noche</label>
                                <input type="number" class="form-control" id="horas_sueno" name="horas_sueno" min="4" max="12" step="0.01" value="8">
                            </div>

                            <!-- Objetivos -->
                            <div class="section-title">
                                <h5>🎯 Objetivos</h5>
                            </div>

                            <div class="mb-3">
                                <label for="objetivo" class="form-label">¿Cuál es tu objetivo?</label>
                                <select class="form-select" id="objetivo" name="objetivo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="deficit">Perder grasa (Déficit)</option>
                                    <option value="volumen">Ganar músculo (Volumen)</option>
                                    <option value="mantenimiento">Mantener peso</option>
                                </select>
                            </div>

                            <div id="campos-deficit" style="display: none;">
                                <div class="mb-3">
                                    <label for="vengo_de_volumen" class="form-label">¿Vienes de una etapa de volumen?</label>
                                    <select class="form-select" id="vengo_de_volumen">
                                        <option value="no" selected>No, vengo de mantenimiento o déficit</option>
                                        <option value="si">Sí, vengo de volumen (metabolismo acelerado)</option>
                                    </select>
                                    <small class="text-muted">Esto afecta tu TDEE y límites de déficit</small>
                                </div>

                                <div class="mb-3" id="campo-calorias-volumen" style="display: none;">
                                    <label for="calorias_volumen" class="form-label">¿De cuántas calorías vienes?</label>
                                    <input type="number" class="form-control" id="calorias_volumen" min="1500" max="6000" step="50" placeholder="Ej: 3500">
                                    <small class="text-muted">Las calorías que consumías en volumen</small>
                                </div>

                                <div class="mb-3">
                                    <label for="kg_perder" class="form-label">¿Cuántos kg quieres perder?</label>
                                    <input type="number" class="form-control" id="kg_perder" name="kg_perder" min="1" max="50" step="0.5" placeholder="Ej: 10">
                                </div>
                                <div class="mb-3">
                                    <label for="semanas_objetivo_deficit" class="form-label">¿En cuántas semanas? (opcional)</label>
                                    <input type="number" class="form-control" id="semanas_objetivo_deficit" min="1" max="100" placeholder="Deja vacío para cálculo automático">
                                    <small class="text-muted">Si tienes una fecha límite</small>
                                </div>
                                <div class="mb-3">
                                    <label for="preferencia_deficit" class="form-label">Preferencia</label>
                                    <select class="form-select" id="preferencia_deficit">
                                        <option value="conservador">Muy conservador (preservar músculo al máximo)</option>
                                        <option value="saludable" selected>Saludable y sostenible</option>
                                        <option value="rapido">Rápido (requiere disciplina)</option>
                                        <option value="agresivo">⚠️ Agresivo (bajo mi responsabilidad)</option>
                                    </select>
                                    <small class="text-muted">La opción agresiva puede afectar músculo y salud</small>
                                </div>
                            </div>

                            <div id="campos-volumen" style="display: none;">
                                <div class="mb-3">
                                    <label for="nivel_gym" class="form-label">Nivel en el gimnasio</label>
                                    <select class="form-select" id="nivel_gym" name="nivel_gym">
                                        <option value="principiante">Principiante (0-1 año)</option>
                                        <option value="intermedio" selected>Intermedio (1-3 años)</option>
                                        <option value="avanzado">Avanzado (3+ años)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="meses_volumen" class="form-label">¿Cuántos meses de volumen?</label>
                                    <input type="number" class="form-control" id="meses_volumen" name="meses_volumen" min="1" max="24" value="6" placeholder="Ej: 6">
                                    <small class="text-muted">💡 Los profesionales planifican por tiempo, no por kg exactos de músculo</small>
                                </div>
                                <div class="mb-3">
                                    <label for="preferencia_volumen" class="form-label">Tipo de volumen (% de superávit sobre TDEE)</label>
                                    <select class="form-select" id="preferencia_volumen">
                                        <option value="ultra_limpio">Ultra Limpio - 8-10% superávit (200-250 kcal)</option>
                                        <option value="limpio" selected>Lean Bulk Óptimo ⭐ - 10-12% superávit (300-350 kcal)</option>
                                        <option value="balanceado">Balanceado - 13-17% superávit (400-500 kcal)</option>
                                        <option value="agresivo">Agresivo - 20%+ superávit (600+ kcal)</option>
                                    </select>
                                    <small class="text-muted">El superávit determina la velocidad de ganancia y ratio músculo/grasa</small>
                                </div>
                                <div class="mb-3">
                                    <label for="incluir_minicuts" class="form-label">¿Incluir mini-cuts?</label>
                                    <select class="form-select" id="incluir_minicuts">
                                        <option value="si" selected>Sí, incluir mini-cuts (controlar grasa acumulada)</option>
                                        <option value="no">No, solo volumen continuo</option>
                                    </select>
                                    <small class="text-muted">Mini-cuts = 2-3 semanas de déficit para perder grasa acumulada sin perder músculo</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Calcular Plan Personalizado</button>
                                <button type="button" class="btn btn-info" id="btn-cargar" onclick="mostrarPlanesGuardados()">📂 Cargar Plan Anterior</button>
                                <button type="button" class="btn btn-success" id="btn-guardar" style="display: none;">💾 Guardar Plan</button>
                                <button type="button" class="btn btn-danger" id="btn-pdf" style="display: none;" disabled>📄 Descargar PDF</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RESULTADOS - DERECHA -->
            <div class="col-lg-8">
                <div id="resultados" style="display: none;">
                    <!-- Resultados Básicos -->
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
                    <div class="card shadow-lg">
                        <div class="card-body py-5">
                            <h2>👈 Completa el formulario</h2>
                            <p class="text-muted">Introduce tus datos personales y de actividad física para obtener tu plan personalizado de nutrición</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="limites_reales.js"></script>
    <script src="script.js"></script>
</body>
</html>
