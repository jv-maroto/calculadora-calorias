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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">💪 Calculadora de Calorías</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="seguimiento.php">📊 Ajuste de Calorías (Progreso Real)</a>
            </div>
        </div>
    </nav>

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
                                <label for="somatotipo" class="form-label">Tipo corporal</label>
                                <select class="form-select" id="somatotipo">
                                    <option value="">No especificar</option>
                                    <option value="ectomorfo">Ectomorfo (Delgado, cuesta ganar peso)</option>
                                    <option value="mesomorfo">Mesomorfo (Atlético, gana músculo fácil)</option>
                                    <option value="endomorfo">Endomorfo (Robusto, gana peso fácil)</option>
                                </select>
                                <small class="text-muted">Tu tendencia natural de complexión</small>
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
                                        <option value="saludable" selected>Saludable y sostenible</option>
                                        <option value="rapido">Lo más rápido posible</option>
                                        <option value="conservador">Muy conservador (preservar músculo al máximo)</option>
                                    </select>
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
                                    <label for="kg_ganar" class="form-label">¿Cuántos kg de MÚSCULO quieres ganar?</label>
                                    <input type="number" class="form-control" id="kg_ganar" name="kg_ganar" min="1" max="30" step="0.5" placeholder="Ej: 10">
                                    <small class="text-muted">⚠️ Solo músculo, no peso total (ganarás más por la grasa inevitable)</small>
                                </div>
                                <div class="mb-3">
                                    <label for="meses_objetivo_volumen" class="form-label">¿En cuántos meses? (opcional)</label>
                                    <input type="number" class="form-control" id="meses_objetivo_volumen" min="1" max="60" placeholder="Deja vacío para cálculo automático">
                                    <small class="text-muted">Si tienes una fecha límite (ej: para verano)</small>
                                </div>
                                <div class="mb-3">
                                    <label for="preferencia_volumen" class="form-label">Preferencia</label>
                                    <select class="form-select" id="preferencia_volumen">
                                        <option value="optimo" selected>Lo más realista y saludable</option>
                                        <option value="rapido">Lo más rápido posible</option>
                                        <option value="limpio">Lo más limpio posible (menos grasa)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Calcular Plan Personalizado</button>
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
