<?php
// Test simple de guardar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Probando guardar.php...\n\n";

// Datos de prueba
$datosTest = [
    'formulario' => [
        'edad' => 30,
        'sexo' => 'hombre',
        'peso' => 80.0,
        'altura' => 175,
        'dias_entreno' => 4,
        'horas_gym' => 1.5,
        'dias_cardio' => 2,
        'horas_cardio' => 0.5,
        'tipo_trabajo' => 'sedentario',
        'horas_trabajo' => 8,
        'horas_sueno' => 8,
        'objetivo' => 'deficit',
        'kg_objetivo' => 10,
        'velocidad' => 'saludable',
        'nivel_gym' => null
    ],
    'resultados' => [
        'tmb' => 1800,
        'tdee' => 2500,
        'plan' => [
            'fases' => [
                ['calorias' => 2000]
            ],
            'duracion' => [
                'semanas' => 12,
                'meses' => 3
            ],
            'macros' => [
                'proteina' => 180,
                'grasa' => 65,
                'carbohidratos' => 200
            ]
        ]
    ]
];

// Simular la petición POST
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://input', json_encode($datosTest));

// Capturar la salida
ob_start();
include 'guardar.php';
$output = ob_get_clean();

echo "Respuesta de guardar.php:\n";
echo $output;
echo "\n";
?>
