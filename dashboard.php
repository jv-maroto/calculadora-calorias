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
    <title>Dashboard - FitTracker</title>

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
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 16px;
            color: #666;
        }

        .module-card {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 2rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .module-card:hover {
            border-color: #1a1a1a;
        }

        .module-icon {
            font-size: 48px;
            margin-bottom: 1rem;
        }

        .module-card h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .module-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 1rem;
        }

        .stat-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            font-size: 12px;
            color: #666;
            margin: 4px;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 24px;
            background: white;
            border: 1px solid #e5e5e5;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
        }

        .logout-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        @media (max-width: 768px) {
            .module-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FitTracker</h1>
        <p>Hola, <?php echo htmlspecialchars($nombre); ?> 👋</p>
    </div>

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Módulos principales -->
        <div class="module-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem;">

            <!-- Módulo GYM -->
            <div class="module-card" onclick="location.href='gym_hub.php'">
                <h2>GYM</h2>
                <p>Rutinas, ejercicios y análisis de progreso</p>
                <div>
                    <span class="stat-badge">Rutinas</span>
                    <span class="stat-badge">Progreso</span>
                    <span class="stat-badge">Ejercicios</span>
                    <span class="stat-badge">Volumen</span>
                </div>
            </div>

            <!-- Módulo DIET -->
            <div class="module-card" onclick="location.href='diet_hub.php'">
                <h2>DIET</h2>
                <p>Nutrición, calorías y seguimiento de peso</p>
                <div>
                    <span class="stat-badge">Calculadora</span>
                    <span class="stat-badge">Reverse Diet</span>
                    <span class="stat-badge">Peso</span>
                    <span class="stat-badge">Gráficas</span>
                </div>
            </div>

        </div>

        <!-- Botón de cerrar sesión -->
        <div style="text-align: center;">
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
