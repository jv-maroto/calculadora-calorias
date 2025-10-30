<?php
session_start();

// Si ya está logueado, redirigir a dashboard
if (isset($_SESSION['usuario_nombre']) && isset($_SESSION['usuario_apellidos'])) {
    header('Location: dashboard.php');
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');

    if (!empty($nombre) && !empty($apellidos)) {
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_apellidos'] = $apellidos;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Por favor, introduce nombre y apellidos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Calculadora de Calorías</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fafafa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            max-width: 450px;
            width: 100%;
            background: white;
            border: 1px solid #e5e5e5;
            padding: 0;
        }

        .card-header {
            padding: 2rem;
            border-bottom: 1px solid #e5e5e5;
            text-align: center;
        }

        .card-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            font-size: 0.875rem;
            color: #666;
        }

        .card-body {
            padding: 2rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #991b1b;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #e5e5e5;
            background: white;
            color: #1a1a1a;
            transition: all 0.15s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1a1a1a;
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background: #1a1a1a;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn:hover {
            background: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #999;
            font-size: 0.75rem;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <h3>Calculadora de Calorías</h3>
            <p>Identifícate para continuar</p>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100" autofocus>
                </div>

                <div class="form-group">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required maxlength="100">
                </div>

                <button type="submit" class="btn">Entrar</button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">Tus datos se guardarán en tu navegador durante esta sesión</small>
            </div>
        </div>
    </div>
</body>
</html>
