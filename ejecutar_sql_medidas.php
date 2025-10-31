<?php
// Script para ejecutar el SQL de creación de tabla de medidas
include 'connection.php';

try {
    // Leer el archivo SQL
    $sql = file_get_contents('crear_tabla_medidas.sql');

    // Separar las consultas
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $success = true;
    $messages = [];

    foreach ($queries as $query) {
        if (empty($query)) continue;

        if ($conn->query($query)) {
            $messages[] = "✓ Ejecutado: " . substr($query, 0, 50) . "...";
        } else {
            $success = false;
            $messages[] = "✗ Error: " . $conn->error;
        }
    }

    $conn->close();

    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Instalación de Tabla de Medidas</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .card {
                background: white;
                border: 1px solid #e5e5e5;
                padding: 2rem;
                margin-bottom: 1rem;
            }
            h1 {
                color: " . ($success ? "#16a34a" : "#ef4444") . ";
            }
            .message {
                padding: 0.5rem;
                margin: 0.5rem 0;
                border-left: 3px solid #e5e5e5;
                background: #fafafa;
            }
            .message.success {
                border-left-color: #16a34a;
                background: #f0fdf4;
            }
            .message.error {
                border-left-color: #ef4444;
                background: #fef2f2;
            }
            .btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background: #1a1a1a;
                color: white;
                text-decoration: none;
                margin-top: 1rem;
            }
        </style>
    </head>
    <body>
        <div class='card'>
            <h1>" . ($success ? "✓ Instalación Completada" : "✗ Error en Instalación") . "</h1>
            <p>" . ($success ?
                "La tabla de medidas corporales se ha creado correctamente en la base de datos." :
                "Hubo errores al crear la tabla. Revisa los mensajes abajo.") . "</p>

            <h3>Detalle de ejecución:</h3>";

    foreach ($messages as $message) {
        $class = strpos($message, '✓') !== false ? 'success' : 'error';
        echo "<div class='message $class'>$message</div>";
    }

    echo "
            <a href='medidas_corporales.php' class='btn'>Ir a Medidas Corporales</a>
        </div>
    </body>
    </html>";

} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Error</title>
    </head>
    <body>
        <h1>Error Fatal</h1>
        <p>" . $e->getMessage() . "</p>
    </body>
    </html>";
}
?>
