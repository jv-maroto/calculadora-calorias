<?php
// Script de prueba para verificar la API
session_start();

echo "<h2>Test de API de Peso</h2>";

// Obtener session_id
if (!isset($_SESSION['calculadora_id'])) {
    $_SESSION['calculadora_id'] = bin2hex(random_bytes(16));
}
$session_id = $_SESSION['calculadora_id'];

echo "<p><strong>Session ID:</strong> $session_id</p>";

// Conectar a la base de datos
$host = 'localhost';
$dbname = 'calculadora_calorias';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green;'>✓ Conexión a base de datos exitosa</p>";

    // Verificar cuántos registros hay en la tabla
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM peso_diario");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de registros en peso_diario:</strong> {$total['total']}</p>";

    // Verificar registros de esta sesión
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM peso_diario WHERE usuario_session = ?");
    $stmt->execute([$session_id]);
    $totalSession = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Registros de tu sesión:</strong> {$totalSession['total']}</p>";

    // Mostrar todos los registros de esta sesión
    $stmt = $pdo->prepare("SELECT * FROM peso_diario WHERE usuario_session = ? ORDER BY fecha DESC");
    $stmt->execute([$session_id]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($registros) > 0) {
        echo "<h3>Tus registros:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Peso</th><th>Notas</th><th>Session</th></tr>";
        foreach ($registros as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>{$reg['fecha']}</td>";
            echo "<td>{$reg['peso']} kg</td>";
            echo "<td>{$reg['notas']}</td>";
            echo "<td>" . substr($reg['usuario_session'], 0, 10) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No tienes registros todavía</p>";
    }

    // Mostrar TODOS los registros (de todas las sesiones)
    $stmt = $pdo->query("SELECT * FROM peso_diario ORDER BY fecha DESC LIMIT 20");
    $todosRegistros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($todosRegistros) > 0) {
        echo "<h3>Todos los registros en la base de datos (últimos 20):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Peso</th><th>Notas</th><th>Session</th></tr>";
        foreach ($todosRegistros as $reg) {
            $estuSesion = ($reg['usuario_session'] === $session_id) ? ' style="background-color: #ffffcc;"' : '';
            echo "<tr$estuSesion>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>{$reg['fecha']}</td>";
            echo "<td>{$reg['peso']} kg</td>";
            echo "<td>{$reg['notas']}</td>";
            echo "<td>" . substr($reg['usuario_session'], 0, 10) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><small>Las filas amarillas son de tu sesión actual</small></p>";
    }

    // Probar la API
    echo "<h3>Probar API:</h3>";
    echo "<p><a href='api_peso.php?action=obtener_pesos&dias=30' target='_blank'>Ver respuesta de api_peso.php?action=obtener_pesos&dias=30</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}
?>
