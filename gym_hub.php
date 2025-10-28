<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellidos'])) {
    header('Location: login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
$apellidos = $_SESSION['usuario_apellidos'];

require_once 'connection.php';

// Obtener estadísticas rápidas
$sql_ultimo = "SELECT MAX(fecha) as ultima_fecha FROM registros_entrenamiento
               WHERE nombre = ? AND apellidos = ?";
$stmt = $conn->prepare($sql_ultimo);
$stmt->bind_param("ss", $nombre, $apellidos);
$stmt->execute();
$ultimo = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM - FitTracker</title>

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
            margin-bottom: 1rem;
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
        }

        .option-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }

        .option-description {
            font-size: 14px;
            color: #666;
        }

        .option-meta {
            font-size: 12px;
            color: #999;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <div style="max-width: 800px; margin: 0 auto; padding: 0 1rem 2rem;">

        <!-- Título -->
        <div class="hub-subtitle">GYM</div>
        <div class="hub-description">Gestiona tus entrenamientos y progreso</div>

        <!-- Opciones -->
        <div>

            <!-- Mis Rutinas -->
            <div class="option-card" onclick="location.href='rutinas.php'">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="flex: 1;">
                        <div class="option-title">Mis Rutinas</div>
                        <div class="option-description">Push, Pull, Legs - Entrenar hoy</div>
                        <?php if ($ultimo && $ultimo['ultima_fecha']): ?>
                            <div class="option-meta">
                                Último entreno: <?php echo date('d/m/Y', strtotime($ultimo['ultima_fecha'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="color: #ccc; font-size: 24px;">→</div>
                </div>
            </div>

            <!-- Análisis de Progreso -->
            <div class="option-card" onclick="location.href='analisis_progreso.php'">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="flex: 1;">
                        <div class="option-title">Análisis de Progreso</div>
                        <div class="option-description">Gráficas, estadísticas y tendencias</div>
                    </div>
                    <div style="color: #ccc; font-size: 24px;">→</div>
                </div>
            </div>

            <!-- Gestionar Ejercicios -->
            <div class="option-card" onclick="location.href='gestionar_ejercicios.php'">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="flex: 1;">
                        <div class="option-title">Gestionar Ejercicios</div>
                        <div class="option-description">Añadir, editar o eliminar ejercicios</div>
                    </div>
                    <div style="color: #ccc; font-size: 24px;">→</div>
                </div>
            </div>

            <!-- Volumen Semanal -->
            <div class="option-card" onclick="location.href='volumen_semanal.php'">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="flex: 1;">
                        <div class="option-title">Volumen Semanal</div>
                        <div class="option-description">Seguimiento de volumen por grupo muscular</div>
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
