<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Progreso - Ajuste de Calorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">💪 Calculadora de Calorías</a>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link" href="index.php" title="Calculadora Principal">🏠</a>
                <a class="nav-link" href="reverse_diet.php" title="Reverse Diet">🔄</a>
                <a class="nav-link" href="grafica.php" title="Ver Gráfica">📈</a>
                <a class="nav-link" href="introducir_peso.php" title="Introducir Peso">⚖️</a>
                <a class="nav-link active" href="seguimiento.php" title="Ajuste de Calorías">📊</a>
                <a class="nav-link" href="logout.php" title="Cerrar Sesión">🚪</a>
            </div>
        </div>
    </nav>

    <div class="container mt-3 mt-md-4">
        <div class="row">
            <!-- Formulario de entrada de datos -->
            <div class="col-12 col-lg-5">
                <div class="card shadow-lg sticky-top">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📝 Registra tu Progreso</h4>
                    </div>
                    <div class="card-body">
                        <form id="seguimientoForm">
                            <!-- Datos del plan actual -->
                            <h5 class="mb-3">📋 Tu Plan Actual</h5>

                            <div class="mb-3">
                                <label for="objetivo" class="form-label">Objetivo</label>
                                <select class="form-select" id="objetivo" required>
                                    <option value="">Selecciona...</option>
                                    <option value="deficit">Déficit (Perder grasa)</option>
                                    <option value="volumen">Volumen (Ganar músculo)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="calorias_actuales" class="form-label">Calorías que consumes ahora</label>
                                <input type="number" class="form-control" id="calorias_actuales" min="1000" max="5000" required placeholder="Ej: 2500">
                            </div>

                            <div class="mb-3">
                                <label for="semanas_en_plan" class="form-label">¿Cuántas semanas llevas con estas calorías?</label>
                                <input type="number" class="form-control" id="semanas_en_plan" min="1" max="52" required placeholder="Ej: 3">
                            </div>

                            <hr>

                            <h5 class="mb-3">⚖️ Tu Progreso Real</h5>

                            <div class="mb-3">
                                <label for="peso_inicial" class="form-label">Peso al inicio (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="peso_inicial" min="30" max="250" required placeholder="Ej: 80.5">
                            </div>

                            <div class="mb-3">
                                <label for="peso_actual" class="form-label">Peso actual (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="peso_actual" min="30" max="250" required placeholder="Ej: 78.2">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">¿Cómo te sientes?</label>
                                <select class="form-select" id="sensacion_energia">
                                    <option value="normal">Con energía normal</option>
                                    <option value="cansado">Muy cansado/sin energía</option>
                                    <option value="hambriento">Mucha hambre constantemente</option>
                                    <option value="perfecto">Perfecto, sin problemas</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rendimiento en el gym</label>
                                <select class="form-select" id="rendimiento_gym">
                                    <option value="igual">Igual que antes</option>
                                    <option value="mejor">Mejorando, subiendo pesos</option>
                                    <option value="peor">Bajando rendimiento</option>
                                    <option value="estancado">Estancado, sin progresos</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                🔍 Analizar mi Progreso
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resultados y recomendaciones -->
            <div class="col-12 col-lg-7 mt-3 mt-lg-0">
                <div id="mensaje-inicial" class="card shadow-lg">
                    <div class="card-body text-center py-5">
                        <h3 class="text-muted">📊 Sistema Inteligente de Ajuste</h3>
                        <p class="lead">Introduce tus datos de progreso real para obtener un análisis personalizado y ajustes precisos en tus calorías</p>
                        <hr>
                        <h5>¿Por qué necesitas esto?</h5>
                        <p>Cada persona responde diferente a las calorías. Puede que necesites más o menos de lo calculado inicialmente. Este sistema analiza tu progreso REAL y te dice exactamente qué ajustar.</p>
                    </div>
                </div>

                <div id="resultados" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="seguimiento.js"></script>
</body>
</html>
