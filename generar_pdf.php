<?php
// Para generar PDF necesitas instalar TCPDF o FPDF
// Comando: composer require tecnickcom/tcpdf

// Por ahora, creo una versión simple que genera HTML para imprimir
require_once 'connection.php';

if (!isset($_GET['id'])) {
    die('ID de plan no especificado');
}

$id = intval($_GET['id']);

// Obtener plan de la base de datos
$stmt = $conn->prepare("SELECT * FROM planes_nutricionales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$plan = $result->fetch_assoc();

if (!$plan) {
    die('Plan no encontrado');
}

$plan_data = json_decode($plan['plan_json'], true);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan Nutricional - PDF</title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; background: #ecf0f1; padding: 10px; }
        h3 { color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #3498db; color: white; }
        .info-box { background: #e8f5e9; padding: 15px; margin: 10px 0; border-left: 4px solid #4caf50; }
        .warning-box { background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .print-btn { background: #3498db; color: white; padding: 10px 20px; border: none; cursor: pointer; margin: 20px 0; }
        .print-btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>

    <h1>📊 Plan Nutricional Personalizado</h1>
    <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($plan['fecha_calculo'])) ?></p>

    <div class="info-box">
        <h3>👤 Datos Personales</h3>
        <p><strong>Edad:</strong> <?= $plan['edad'] ?> años | <strong>Sexo:</strong> <?= ucfirst($plan['sexo']) ?></p>
        <p><strong>Peso:</strong> <?= $plan['peso'] ?> kg | <strong>Altura:</strong> <?= $plan['altura'] ?> cm</p>
    </div>

    <h2>🎯 Objetivo: <?= ucfirst($plan['objetivo']) ?></h2>

    <?php if ($plan['objetivo'] === 'deficit'): ?>
        <p><strong>Meta:</strong> Perder <?= $plan['kg_objetivo'] ?> kg</p>
        <p><strong>Duración estimada:</strong> <?= $plan['duracion_semanas'] ?> semanas (<?= $plan['duracion_meses'] ?> meses)</p>
        <p><strong>Velocidad:</strong> <?= ucfirst($plan['velocidad']) ?></p>
    <?php elseif ($plan['objetivo'] === 'volumen'): ?>
        <p><strong>Meta:</strong> Ganar <?= $plan['kg_objetivo'] ?> kg de músculo</p>
        <p><strong>Duración estimada:</strong> <?= $plan['duracion_meses'] ?> meses (<?= $plan['duracion_semanas'] ?> semanas)</p>
        <p><strong>Nivel:</strong> <?= ucfirst($plan['nivel_gym']) ?></p>
        <p><strong>Velocidad:</strong> <?= ucfirst($plan['velocidad']) ?></p>
    <?php endif; ?>

    <h2>📈 Resultados Metabólicos</h2>
    <table>
        <tr>
            <th>TMB (Metabolismo Basal)</th>
            <td><?= number_format($plan['tmb'], 0) ?> kcal/día</td>
        </tr>
        <tr>
            <th>TDEE (Gasto Total Diario)</th>
            <td><?= number_format($plan['tdee'], 0) ?> kcal/día</td>
        </tr>
        <tr>
            <th>Calorías del Plan</th>
            <td><strong><?= number_format($plan['calorias_plan'], 0) ?> kcal/día</strong></td>
        </tr>
    </table>

    <h2>🍽️ Distribución de Macronutrientes</h2>
    <table>
        <tr>
            <th>Macronutriente</th>
            <th>Gramos/día</th>
            <th>Calorías/día</th>
        </tr>
        <tr>
            <td>🥩 Proteína</td>
            <td><?= $plan['proteina_gramos'] ?>g</td>
            <td><?= $plan['proteina_gramos'] * 4 ?> kcal</td>
        </tr>
        <tr>
            <td>🥑 Grasa</td>
            <td><?= $plan['grasa_gramos'] ?>g</td>
            <td><?= $plan['grasa_gramos'] * 9 ?> kcal</td>
        </tr>
        <tr>
            <td>🍚 Carbohidratos</td>
            <td><?= $plan['carbohidratos_gramos'] ?>g</td>
            <td><?= $plan['carbohidratos_gramos'] * 4 ?> kcal</td>
        </tr>
    </table>

    <?php if (isset($plan_data['plan']['fases'])): ?>
    <h2>📅 Fases del Plan</h2>
    <table>
        <tr>
            <th>Fase</th>
            <th>Calorías</th>
        </tr>
        <?php foreach ($plan_data['plan']['fases'] as $fase): ?>
        <tr>
            <td><?= $fase['nombre'] ?></td>
            <td><?= $fase['calorias'] ?> kcal/día</td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($plan['objetivo'] === 'volumen' && isset($plan_data['plan']['miniCuts']) && count($plan_data['plan']['miniCuts']) > 0): ?>
    <h2>✂️ Mini-cuts Programados</h2>
    <div class="warning-box">
        <p>Durante estas semanas, reduce calorías para controlar la grasa acumulada</p>
    </div>
    <table>
        <tr>
            <th>Mes</th>
            <th>Semanas</th>
            <th>Calorías</th>
        </tr>
        <?php foreach ($plan_data['plan']['miniCuts'] as $minicut): ?>
        <tr>
            <td>Mes <?= $minicut['mes'] ?></td>
            <td>Semanas <?= $minicut['semanas'] ?></td>
            <td><?= $minicut['calorias'] ?> kcal/día</td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($plan['objetivo'] === 'deficit' && isset($plan_data['plan']['refeeds']) && count($plan_data['plan']['refeeds']) > 0): ?>
    <h2>🔄 Refeeds Programados</h2>
    <div class="warning-box">
        <p><?= $plan_data['plan']['refeedInfo'] ?></p>
        <p>En estos días come <?= $plan_data['plan']['tdee'] ?> kcal (mantenimiento)</p>
    </div>
    <table>
        <tr>
            <th>Semana</th>
            <th>Calorías</th>
        </tr>
        <?php foreach (array_slice($plan_data['plan']['refeeds'], 0, 10) as $refeed): ?>
        <tr>
            <td>Semana <?= $refeed['semana'] ?></td>
            <td><?= $refeed['calorias'] ?> kcal</td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($plan_data['plan']['refeeds']) > 10): ?>
        <tr>
            <td colspan="2">... y <?= count($plan_data['plan']['refeeds']) - 10 ?> refeeds más</td>
        </tr>
        <?php endif; ?>
    </table>
    <?php endif; ?>

    <h2>💧 Hidratación</h2>
    <p>Consumir al menos <strong><?= round($plan['peso'] * 35) ?> ml</strong> de agua al día (<?= number_format($plan['peso'] * 35 / 1000, 1) ?> litros)</p>

    <h2>🥗 Recomendaciones Nutricionales</h2>

    <h3>🥩 Fuentes de Proteína</h3>
    <p>Pollo, pavo, pescado, huevos, carne magra, proteína whey, yogur griego, legumbres</p>

    <h3>🍚 Fuentes de Carbohidratos</h3>
    <p>Arroz, avena, patata, boniato, pasta integral, quinoa, frutas, pan integral</p>

    <h3>🥑 Fuentes de Grasa Saludable</h3>
    <p>Aceite de oliva, aguacate, frutos secos, salmón, atún, yemas de huevo</p>

    <h3>💊 Suplementación Básica</h3>
    <ul>
        <li>Proteína en polvo: solo si no alcanzas con comida real</li>
        <li>Creatina monohidrato: 5g diarios</li>
        <li>Vitamina D3: 2000-4000 UI diarias</li>
        <li>Omega-3: 2-3g diarios (EPA+DHA)</li>
    </ul>

    <div class="info-box">
        <p><strong>📌 Nota:</strong> Este plan es una guía personalizada basada en tus datos. Ajusta según tu progreso y cómo te sientas. Consulta con un profesional de la salud antes de hacer cambios importantes en tu dieta.</p>
    </div>

    <button class="print-btn no-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>

    <p class="no-print" style="text-align: center; color: #777; margin-top: 40px;">
        <small>Generado con Calculadora de Calorías - Sistema Mifflin-St Jeor</small>
    </p>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
