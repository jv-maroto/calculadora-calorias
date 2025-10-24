<?php
// Script para actualizar ejercicios con datos faltantes
require_once 'config.php';

echo "Actualizando ejercicios con datos faltantes...\n\n";

// Actualizar Press de Banca
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Pecho',
    sets_recomendados = 3,
    reps_recomendadas = '8-12'
    WHERE nombre = 'Press de Banca'");

// Actualizar Press Supino
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Pecho Superior',
    sets_recomendados = 3,
    reps_recomendadas = '8-12'
    WHERE nombre = 'Press Supino'");

// Actualizar Fondos
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Pecho/Tríceps',
    sets_recomendados = 3,
    reps_recomendadas = '8-15'
    WHERE nombre = 'Fondos'");

// Actualizar Aperturas con Mancuernas
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Pecho',
    sets_recomendados = 3,
    reps_recomendadas = '10-15'
    WHERE nombre = 'Aperturas con Mancuernas'");

// Actualizar Press Militar
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Hombro',
    sets_recomendados = 3,
    reps_recomendadas = '8-12'
    WHERE nombre = 'Press Militar'");

// Actualizar Elevaciones Laterales
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Hombro Lateral',
    sets_recomendados = 3,
    reps_recomendadas = '12-15'
    WHERE nombre = 'Elevaciones Laterales'");

// Actualizar Extensiones de Tríceps
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Tríceps',
    sets_recomendados = 3,
    reps_recomendadas = '10-15'
    WHERE nombre LIKE '%Tríceps%'");

// Actualizar Dominadas
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Espalda/Dorsales',
    sets_recomendados = 3,
    reps_recomendadas = '6-12'
    WHERE nombre = 'Dominadas'");

// Actualizar Remo con Barra
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Espalda',
    sets_recomendados = 3,
    reps_recomendadas = '8-12'
    WHERE nombre = 'Remo con Barra'");

// Actualizar Peso Muerto
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Espalda/Glúteos/Isquios',
    sets_recomendados = 3,
    reps_recomendadas = '5-8'
    WHERE nombre = 'Peso Muerto'");

// Actualizar Jalón al Pecho
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Dorsales',
    sets_recomendados = 3,
    reps_recomendadas = '10-12'
    WHERE nombre LIKE '%Jalón%'");

// Actualizar Remo en Polea
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Espalda Media',
    sets_recomendados = 3,
    reps_recomendadas = '10-12'
    WHERE nombre LIKE '%Remo%Polea%'");

// Actualizar Face Pull
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Hombro Posterior',
    sets_recomendados = 3,
    reps_recomendadas = '12-15'
    WHERE nombre = 'Face Pull'");

// Actualizar Curl de Bíceps
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Bíceps',
    sets_recomendados = 3,
    reps_recomendadas = '10-12'
    WHERE nombre LIKE '%Curl%' OR nombre LIKE '%Bíceps%'");

// Actualizar Sentadillas
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Cuádriceps/Glúteos',
    sets_recomendados = 3,
    reps_recomendadas = '8-12'
    WHERE nombre LIKE '%Sentadilla%'");

// Actualizar Prensa
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Cuádriceps',
    sets_recomendados = 3,
    reps_recomendadas = '10-15'
    WHERE nombre = 'Prensa'");

// Actualizar Extensiones de Cuádriceps
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Cuádriceps',
    sets_recomendados = 3,
    reps_recomendadas = '12-15'
    WHERE nombre LIKE '%Extensión%Cuádriceps%'");

// Actualizar Curl Femoral
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Isquiotibiales',
    sets_recomendados = 3,
    reps_recomendadas = '10-15'
    WHERE nombre LIKE '%Curl Femoral%' OR nombre LIKE '%Isquio%'");

// Actualizar Hip Thrust
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Glúteos',
    sets_recomendados = 3,
    reps_recomendadas = '10-15'
    WHERE nombre LIKE '%Hip Thrust%' OR nombre LIKE '%Glúteo%'");

// Actualizar Elevación de Talones
$conn->query("UPDATE ejercicios SET
    grupo_muscular = 'Gemelos',
    sets_recomendados = 3,
    reps_recomendadas = '15-20'
    WHERE nombre LIKE '%Talones%' OR nombre LIKE '%Gemelo%'");

echo "✅ Ejercicios actualizados correctamente!\n";
echo "\nVerifica la página de gestión de ejercicios.\n";

$conn->close();
?>
