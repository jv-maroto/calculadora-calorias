<?php
// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calculadora_calorias";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error . ". Verifica que la base de datos 'calculadora_calorias' exista en phpMyAdmin.");
}

// Establecer charset UTF-8
$conn->set_charset("utf8mb4");
?>
