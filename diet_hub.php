<?php
session_start();

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
    <title>DIET - FitTracker</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #fafafa;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .back-btn:hover {
            color: #1a1a1a;
        }

        .hub-title {
            text-align: center;
            font-size: 48px;
            margin: 2rem 0 1rem;
        }

        .hub-subtitle {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .hub-description {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 2rem;
        }

        .option-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .option-card:hover {
            border-color: #1a1a1a;
        }

        .option-icon {
            width: 48px;
            height: 48px;
            background: #1a1a1a;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 1rem;
        }

        .option-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
            text-align: center;
        }

        .option-description {
            font-size: 13px;
            color: #666;
            text-align: center;
        }

        @media (max-width: 768px) {
            .option-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <div style="max-width: 900px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Título -->
        <div class="hub-subtitle">DIET</div>
        <div class="hub-description">Control nutricional y seguimiento de peso</div>

        <!-- Opciones -->
        <div class="option-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">

            <!-- Calculadora de Calorías -->
            <div class="option-card" onclick="location.href='calculatorkcal.php'">
                <div class="option-title">Calculadora</div>
                <div class="option-description">Calcula tus calorías y macros</div>
            </div>

            <!-- Reverse Diet -->
            <div class="option-card" onclick="location.href='reverse_diet_v0.php'">
                <div class="option-title">Reverse Diet</div>
                <div class="option-description">Recupera metabolismo post-dieta</div>
            </div>

            <!-- Registrar Peso -->
            <div class="option-card" onclick="location.href='introducir_peso_v0.php'">
                <div class="option-title">Registrar Peso</div>
                <div class="option-description">Añade tu peso diario</div>
            </div>

            <!-- Gráfica de Peso -->
            <div class="option-card" onclick="location.href='grafica_v0.php'">
                <div class="option-title">Gráfica de Peso</div>
                <div class="option-description">Visualiza tu evolución</div>
            </div>

            <!-- Medidas Corporales -->
            <div class="option-card" onclick="location.href='medidas_corporales.php'">
                <div class="option-title">Medidas Corporales</div>
                <div class="option-description">Registro y evolución de medidas</div>
            </div>

            <!-- Ajuste de Calorías -->
            <div class="option-card" onclick="location.href='seguimiento_v0.php'" style="grid-column: span 3;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="flex: 1; text-align: left;">
                        <div class="option-title" style="text-align: left;">Ajuste de Calorías</div>
                        <div class="option-description" style="text-align: left;">Seguimiento y ajustes según objetivos</div>
                    </div>
                    <div style="color: #ccc; font-size: 24px;">→</div>
                </div>
            </div>

        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
