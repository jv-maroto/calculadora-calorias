<?php
require_once('vendor/autoload.php');
include 'connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die('ID de plan inválido');
}

$stmt = $conn->prepare("SELECT * FROM planes_nutricionales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Plan no encontrado');
}

$plan = $result->fetch_assoc();
$planData = json_decode($plan['plan_json'], true);

// Crear HTML para el PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 10px;
        }
        h2 {
            color: #1a1a1a;
            margin-top: 20px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 5px;
        }
        .info-box {
            background: #fafafa;
            border: 1px solid #e5e5e5;
            padding: 15px;
            margin: 10px 0;
        }
        .macro-grid {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .macro-box {
            border: 1px solid #e5e5e5;
            padding: 15px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #e5e5e5;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #fafafa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <h1>Plan Nutricional</h1>

    <div class="info-box">
        <strong>Nombre:</strong> ' . htmlspecialchars($plan['nombre'] . ' ' . $plan['apellidos']) . '<br>
        <strong>Fecha:</strong> ' . date('d/m/Y', strtotime($plan['fecha_calculo'])) . '<br>
        <strong>Objetivo:</strong> ' . ucfirst($plan['objetivo']) . '
    </div>

    <h2>Datos Personales</h2>
    <div class="info-box">
        <strong>Edad:</strong> ' . $plan['edad'] . ' años<br>
        <strong>Sexo:</strong> ' . ucfirst($plan['sexo']) . '<br>
        <strong>Peso:</strong> ' . $plan['peso'] . ' kg<br>
        <strong>Altura:</strong> ' . $plan['altura'] . ' cm<br>
        <strong>Nivel:</strong> ' . ucfirst($plan['nivel_gym']) . '
    </div>

    <h2>Calorías y Macronutrientes</h2>
    <div class="info-box">
        <strong>TMB:</strong> ' . round($plan['tmb']) . ' kcal<br>
        <strong>TDEE:</strong> ' . round($plan['tdee']) . ' kcal<br>
        <strong>Calorías del Plan:</strong> ' . round($plan['calorias_plan']) . ' kcal/día<br>
        <strong>Duración:</strong> ' . $plan['duracion_semanas'] . ' semanas (' . $plan['duracion_meses'] . ' meses)
    </div>

    <div class="macro-grid">
        <div class="macro-box">
            <h3>Proteína</h3>
            <p style="font-size: 24px; font-weight: bold;">' . $plan['proteina_gramos'] . 'g</p>
            <p>' . ($plan['proteina_gramos'] * 4) . ' kcal/día</p>
        </div>
        <div class="macro-box">
            <h3>Grasa</h3>
            <p style="font-size: 24px; font-weight: bold;">' . $plan['grasa_gramos'] . 'g</p>
            <p>' . ($plan['grasa_gramos'] * 9) . ' kcal/día</p>
        </div>
        <div class="macro-box">
            <h3>Carbohidratos</h3>
            <p style="font-size: 24px; font-weight: bold;">' . $plan['carbohidratos_gramos'] . 'g</p>
            <p>' . ($plan['carbohidratos_gramos'] * 4) . ' kcal/día</p>
        </div>
    </div>
';

// Añadir información específica del objetivo
if ($plan['objetivo'] === 'volumen' && isset($planData['semanas'])) {
    $html .= '<h2>Plan de Volumen Semanal</h2>';
    $html .= '<table>';
    $html .= '<tr><th>Semana</th><th>Calorías</th><th>Músculo</th><th>Grasa</th><th>Peso Total</th></tr>';

    $maxSemanas = min(12, count($planData['semanas']));
    for ($i = 0; $i < $maxSemanas; $i++) {
        $semana = $planData['semanas'][$i];
        $html .= '<tr>';
        $html .= '<td>Semana ' . $semana['num'] . '</td>';
        $html .= '<td>' . round($semana['calorias']) . ' kcal</td>';
        $html .= '<td>+' . number_format($semana['musculoAcumulado'], 2) . ' kg</td>';
        $html .= '<td>+' . number_format($semana['grasaAcumulada'], 2) . ' kg</td>';
        $html .= '<td>+' . number_format($semana['musculoAcumulado'] + $semana['grasaAcumulada'], 2) . ' kg</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
}

$html .= '
    <h2>Información Adicional</h2>
    <div class="info-box">
        <strong>Cardio:</strong> ' . htmlspecialchars($planData['infoCardio'] ?? 'No especificado') . '
    </div>
</body>
</html>';

// Configurar Dompdf (versión 0.6.2)
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->set_paper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("plan_nutricional_" . $id . ".pdf", array("Attachment" => true));
?>
