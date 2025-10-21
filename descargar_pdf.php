<?php
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

// NO establecer headers de PDF todavía - primero mostramos el HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            font-size: 24px;
            margin-top: 0;
        }
        h2 {
            color: #34495e;
            background: #ecf0f1;
            padding: 8px;
            margin-top: 20px;
            font-size: 18px;
        }
        h3 {
            color: #555;
            font-size: 14px;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 13px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .info-box {
            background: #e8f5e9;
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid #4caf50;
            font-size: 13px;
        }
        .warning-box {
            background: #fff3cd;
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
            font-size: 13px;
        }
        #download-btn {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            margin: 15px 0;
            display: block;
            width: 100%;
        }
        #download-btn:hover {
            background: #2980b9;
        }
        #download-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        p {
            margin: 8px 0;
            font-size: 13px;
        }
        @media print {
            #download-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button id="download-btn" onclick="descargarPDF()">📥 Descargar PDF</button>

    <div id="content">
        <h1>📊 Plan Nutricional Personalizado</h1>
        <p><strong>Usuario:</strong> <?= htmlspecialchars($plan['nombre'] . ' ' . $plan['apellidos']) ?></p>
        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($plan['fecha_calculo'])) ?></p>

        <div class="info-box">
            <h3>👤 Datos Personales</h3>
            <p><strong>Edad:</strong> <?= $plan['edad'] ?> años | <strong>Sexo:</strong> <?= ucfirst($plan['sexo']) ?></p>
            <p><strong>Peso:</strong> <?= $plan['peso'] ?> kg | <strong>Altura:</strong> <?= $plan['altura'] ?> cm</p>
        </div>

        <h2>🎯 Objetivo: <?= ucfirst($plan['objetivo']) ?></h2>

        <?php if ($plan['objetivo'] === 'deficit'): ?>
            <p><strong>Meta:</strong> Perder <?= $plan['kg_objetivo'] ?> kg</p>
            <p><strong>Duración estimada:</strong> <?= $plan['duracion_semanas'] ?> semanas</p>
        <?php elseif ($plan['objetivo'] === 'volumen'): ?>
            <p><strong>Duración estimada:</strong> <?= $plan['duracion_meses'] ?> meses</p>
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

        <div class="info-box">
            <p><strong>📌 Nota:</strong> Este plan es una guía personalizada. Ajusta según tu progreso.</p>
        </div>
    </div>

    <script>
        async function descargarPDF() {
            const button = document.getElementById('download-btn');
            button.textContent = '⏳ Generando PDF...';
            button.disabled = true;

            try {
                const { jsPDF } = window.jspdf;
                const content = document.getElementById('content');

                // Mejorar configuración de html2canvas para evitar errores
                const canvas = await html2canvas(content, {
                    scale: 1.5,  // Reducido de 2 a 1.5 para mejor rendimiento
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    windowWidth: 800,
                    windowHeight: content.scrollHeight,
                    backgroundColor: '#ffffff'
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.95);  // JPEG con calidad 95% en lugar de PNG
                const pdf = new jsPDF('p', 'mm', 'a4');

                const imgWidth = 210; // A4 width in mm
                const pageHeight = 297; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                // Primera página
                pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Páginas adicionales si es necesario
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save('plan_nutricional_<?= htmlspecialchars($plan['nombre']) ?>_<?= date('Y-m-d') ?>.pdf');

                button.textContent = '✅ PDF Descargado';
                setTimeout(() => {
                    window.close();
                }, 2000);
            } catch (error) {
                console.error('Error al generar PDF:', error);
                alert('Error al generar el PDF: ' + error.message + '\n\nIntenta usar el botón "Imprimir" del navegador y selecciona "Guardar como PDF".');
                button.textContent = '❌ Error - Usa Ctrl+P para imprimir';
                button.disabled = false;
            }
        }

        // Auto-descargar después de que la página cargue completamente
        window.addEventListener('load', () => {
            setTimeout(() => {
                descargarPDF();
            }, 500);
        });
    </script>
</body>
</html>
    <?php
    $stmt->close();
    $conn->close();
    exit;
}
?>
