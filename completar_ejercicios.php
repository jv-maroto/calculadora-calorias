<?php
require_once 'config.php';

echo "Completando datos de ejercicios faltantes...\n\n";

$updates = [
    // PUSH
    4 => ['Arnold Press', 'Hombro', 3, '8-12'],
    5 => ['Lateral Raise', 'Hombro Lateral', 3, '12-15'],
    7 => ['Tricep Pushdown', 'Tríceps', 3, '10-15'],

    // PULL
    8 => ['Chin Up/Dominadas', 'Espalda/Dorsales', 3, '6-12'],
    9 => ['Lat Pulldown', 'Dorsales', 3, '10-12'],
    10 => ['Seated Row', 'Espalda Media', 3, '10-12'],
    11 => ['T Bar Row', 'Espalda', 3, '8-12'],
    14 => ['Reverse Fly', 'Hombro Posterior', 3, '12-15'],
    15 => ['Knee Raise', 'Abdominales', 3, '15-20'],

    // LEGS
    16 => ['Leg Press', 'Cuádriceps', 3, '10-15'],
    17 => ['Hack Squat', 'Cuádriceps', 3, '8-12'],
    18 => ['Leg Extension', 'Cuádriceps', 3, '12-15'],
    20 => ['Seated Leg Curl', 'Isquiotibiales', 3, '10-15'],
    21 => ['Seated Calf Raise', 'Gemelos', 3, '15-20'],

    // TORSO
    22 => ['Deadlift', 'Espalda/Glúteos/Isquios', 3, '5-8'],
    23 => ['Strict Military Press', 'Hombro', 3, '8-12'],
    24 => ['Bent Over Row', 'Espalda', 3, '8-12'],
    25 => ['Lateral Raise', 'Hombro Lateral', 3, '12-15'],
    28 => ['Shrug', 'Trapecio', 3, '10-15'],

    // LEGS 2
    29 => ['Romanian Deadlift', 'Isquiotibiales/Glúteos', 3, '8-12'],
    30 => ['Bulgarian Split Squat', 'Cuádriceps/Glúteos', 3, '10-12'],
    31 => ['Seated Leg Curl', 'Isquiotibiales', 3, '10-15'],
    32 => ['Leg Extension', 'Cuádriceps', 3, '12-15'],
    33 => ['Seated Calf Raise', 'Gemelos', 3, '15-20'],
    34 => ['Knee Raise', 'Abdominales', 3, '15-20'],
];

$stmt = $conn->prepare("UPDATE ejercicios SET grupo_muscular = ?, sets_recomendados = ?, reps_recomendadas = ? WHERE id = ?");

foreach ($updates as $id => $data) {
    $nombre = $data[0];
    $grupo = $data[1];
    $sets = $data[2];
    $reps = $data[3];

    $stmt->bind_param("sisi", $grupo, $sets, $reps, $id);
    $stmt->execute();

    echo "✅ Actualizado: $nombre -> $grupo ($sets x $reps)\n";
}

$stmt->close();
$conn->close();

echo "\n✅ ¡Todos los ejercicios actualizados correctamente!\n";
?>
