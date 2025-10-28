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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">💪 Calculadora de Calorías</h3>
                    <p class="mb-0 small">Identifícate para continuar</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">👤 Nombre</label>
                            <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" required maxlength="100" autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="apellidos" class="form-label">👥 Apellidos</label>
                            <input type="text" class="form-control form-control-lg" id="apellidos" name="apellidos" required maxlength="100">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <small class="text-muted">Tus datos se guardarán en tu navegador durante esta sesión</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
