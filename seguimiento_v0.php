<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Progreso - Ajuste de Calorías</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- V0 Theme -->
    <link rel="stylesheet" href="assets/css/v0-theme.css">
</head>
<body>
                                                                                                                                                            
    <nav style="display: none; background: white; border-bottom: 1px solid #e5e5e5; padding: 0 2rem; height: 60px; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
        <div style="display: flex; gap: 2rem;">
            <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">← Dashboard</a>
            <a href="diet_hub.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">DIET Hub</a>
            <a href="calculatorkcal.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Calculadora</a>
            <a href="introducir_peso_v0.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Peso</a>
            <a href="grafica_v0.php" style="color: #666; text-decoration: none; font-size: 14px; font-weight: 500;">Gráfica</a>
            <a href="reverse_diet_v0.php" style="color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500;">Reverse Diet</a>
        </div>
        <a href="logout.php" style="color: #999; text-decoration: none; font-size: 14px; font-weight: 500;">Salir</a>
    </nav>

    <!-- Contenido -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem 2rem;">

        <div class="grid-2" style="align-items: flex-start; gap: 1.5rem;">

            <!-- Formulario de entrada de datos -->
            <div>
                <div class="v0-card sticky-card">
                    <div class="v0-card-header">
                        <i data-lucide="clipboard-list" style="color: #6366f1; width: 24px; height: 24px;"></i>
                        <div>
                            <h3>Registra tu Progreso</h3>
                            <p>Ingresa tus datos reales para obtener recomendaciones personalizadas</p>
                        </div>
                    </div>
                    <div class="v0-card-body">
                        <form id="seguimientoForm">

                            <!-- Datos del plan actual -->
                            <h5 style="margin-bottom: 1rem; color: #1e293b; font-size: 1.125rem;">
                                <i data-lucide="target" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"></i>
                                Tu Plan Actual
                            </h5>

                            <div style="margin-bottom: 1rem;">
                                <label for="objetivo" class="v0-label">Objetivo</label>
                                <select class="v0-select" id="objetivo" required>
                                    <option value="">Selecciona...</option>
                                    <option value="deficit">Déficit (Perder grasa)</option>
                                    <option value="volumen">Volumen (Ganar músculo)</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label for="calorias_actuales" class="v0-label">Calorías que consumes ahora</label>
                                <input type="number" class="v0-input" id="calorias_actuales" min="1000" max="5000" required placeholder="Ej: 2500">
                                <small class="v0-helper">Ingresa tu consumo calórico diario actual</small>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label for="semanas_en_plan" class="v0-label">¿Cuántas semanas llevas con estas calorías?</label>
                                <input type="number" class="v0-input" id="semanas_en_plan" min="1" max="52" required placeholder="Ej: 3">
                            </div>

                            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">

                            <h5 style="margin-bottom: 1rem; color: #1e293b; font-size: 1.125rem;">
                                <i data-lucide="scale" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"></i>
                                Tu Progreso Real
                            </h5>

                            <div style="margin-bottom: 1rem;">
                                <label for="peso_inicial" class="v0-label">Peso al inicio (kg)</label>
                                <input type="number" step="0.1" class="v0-input" id="peso_inicial" min="30" max="250" required placeholder="Ej: 80.5">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label for="peso_actual" class="v0-label">Peso actual (kg)</label>
                                <input type="number" step="0.1" class="v0-input" id="peso_actual" min="30" max="250" required placeholder="Ej: 78.2">
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <label class="v0-label">¿Cómo te sientes?</label>
                                <select class="v0-select" id="sensacion_energia">
                                    <option value="normal">Con energía normal</option>
                                    <option value="cansado">Muy cansado/sin energía</option>
                                    <option value="hambriento">Mucha hambre constantemente</option>
                                    <option value="perfecto">Perfecto, sin problemas</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label class="v0-label">Rendimiento en el gym</label>
                                <select class="v0-select" id="rendimiento_gym">
                                    <option value="igual">Igual que antes</option>
                                    <option value="mejor">Mejorando, subiendo pesos</option>
                                    <option value="peor">Bajando rendimiento</option>
                                    <option value="estancado">Estancado, sin progresos</option>
                                </select>
                            </div>

                            <button type="submit" class="v0-btn v0-btn-primary" style="width: 100%;">
                                <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                                Analizar mi Progreso
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resultados y recomendaciones -->
            <div>
                <div id="mensaje-inicial" class="v0-card">
                    <div class="v0-card-body" style="text-align: center; padding: 3rem 2rem;">
                        <i data-lucide="trending-up" style="width: 64px; height: 64px; color: #6366f1; margin: 0 auto 1.5rem; display: block;"></i>
                        <h3 style="color: #1e293b; margin-bottom: 1rem;">Sistema Inteligente de Ajuste</h3>
                        <p style="color: #64748b; font-size: 1.125rem; margin-bottom: 2rem;">
                            Introduce tus datos de progreso real para obtener un análisis personalizado y ajustes precisos en tus calorías
                        </p>
                        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #e2e8f0;">
                        <h5 style="color: #1e293b; margin-bottom: 0.75rem;">¿Por qué necesitas esto?</h5>
                        <p style="color: #64748b; line-height: 1.6;">
                            Cada persona responde diferente a las calorías. Puede que necesites más o menos de lo calculado inicialmente.
                            Este sistema analiza tu progreso REAL y te dice exactamente qué ajustar.
                        </p>
                    </div>
                </div>

                <div id="resultados" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    <script src="seguimiento.js"></script>

    <style>
        .sticky-card {
            position: sticky;
            top: 1rem;
        }

        @media (max-width: 1024px) {
            .sticky-card {
                position: relative;
                top: 0;
            }
        }
    </style>
</body>
</html>
