<?php
/**
 * CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
 *
 * Instrucciones:
 * 1. Copia este archivo y renómbralo a "connection.php"
 * 2. Modifica los valores según tu configuración MySQL
 * 3. NO subas connection.php a GitHub (está en .gitignore)
 */

// Configuración de conexión
$host = "localhost";        // Host MySQL (normalmente localhost)
$usuario = "root";          // Usuario MySQL
$password = "";             // Contraseña MySQL (vacío por defecto en XAMPP)
$database = "calculadora_calorias";  // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($host, $usuario, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    throw new Exception("Error de conexión: " . $conn->connect_error);
}

// Configurar charset UTF-8
$conn->set_charset("utf8mb4");
?>
