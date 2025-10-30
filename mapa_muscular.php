<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Muscular - Ejercicios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fafafa;
            min-height: 100vh;
            padding: 2rem;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .body-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            position: relative;
            min-height: 800px;
        }

        /* Silueta SVG central */
        .human-body {
            width: 100%;
            height: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 0;
        }

        .human-body > div {
            background: transparent;
        }

        #muscle-svg {
            filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.1));
        }

        .muscle-group {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .muscle-group:hover *,
        .muscle-group.active * {
            fill: rgba(26, 26, 26, 0.3) !important;
            stroke: #1a1a1a !important;
            stroke-width: 4 !important;
        }

        .muscle-group:hover,
        .muscle-group.active {
            filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.8));
        }

        /* Botón volver */
        .back-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: white;
            border: 1px solid #e5e5e5;
            color: #666;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.15s;
            margin-bottom: 2rem;
        }

        .back-btn:hover {
            border-color: #1a1a1a;
            color: #1a1a1a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .muscle-tooltip {
                width: 280px;
                max-height: 400px;
            }

            .muscle-tooltip.left {
                left: 10px;
            }

            .muscle-tooltip.right {
                right: 10px;
            }
        }

        /* Loader */
        .loading {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        /* Tooltip para mostrar ejercicios */
        .muscle-tooltip {
            position: fixed;
            background: white;
            border: 2px solid #1a1a1a;
            padding: 1.5rem;
            width: 300px;
            max-height: 500px;
            overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .muscle-tooltip.show {
            opacity: 1;
        }

        .muscle-tooltip.left {
            left: 50px;
        }

        .muscle-tooltip.right {
            right: 50px;
        }

        .muscle-tooltip::-webkit-scrollbar {
            width: 6px;
        }

        .muscle-tooltip::-webkit-scrollbar-track {
            background: #f5f5f5;
        }

        .muscle-tooltip::-webkit-scrollbar-thumb {
            background: #d5d5d5;
            border-radius: 3px;
        }

        .muscle-tooltip h4 {
            margin: 0 0 0.75rem 0;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .muscle-tooltip ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .muscle-tooltip li {
            padding: 0.375rem 0;
            font-size: 0.8rem;
            color: #666;
            border-bottom: 1px solid #f0f0f0;
        }

        .muscle-tooltip li:last-child {
            border-bottom: none;
        }

        .muscle-tooltip .exercise-count {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.5rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="gym_hub.php" class="back-btn">← Volver al Gym Hub</a>

        <div class="body-container">
            <!-- Silueta humana anatómica -->
            <div class="human-body">
                <div style="position: relative; width: 100%; max-width: 700px; margin: 0 auto;">
                    <!-- SVG del cuerpo humano -->
                    <object data="muscles-body.svg" type="image/svg+xml" id="muscle-svg" style="width: 100%; height: auto;"></object>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltip para mostrar ejercicios -->
    <div class="muscle-tooltip" id="muscle-tooltip">
        <h4 id="tooltip-title"></h4>
        <ul id="tooltip-exercises"></ul>
        <div class="exercise-count" id="tooltip-count"></div>
    </div>

    <script>
        let ejercicios = [];
        let svgDoc = null;

        // Mapeo de nombres de grupos musculares SVG a nombres de la base de datos
        // Incluye 'side' (left/right) y 'position' (% desde arriba) para posicionamiento dinámico
        const muscleMapping = {
            'upper_pecs': { muscles: ['Pecho superior', 'Pecho'], side: 'left', position: 25 },
            'middle_pecs': { muscles: ['Pecho', 'Pecho/Tríceps'], side: 'left', position: 28 },
            'lower_pecs': { muscles: ['Pecho', 'Pecho/Tríceps'], side: 'left', position: 32 },
            'biceps': { muscles: ['Bíceps', 'Espalda/Bíceps'], side: 'left', position: 35 },
            'triceps': { muscles: ['Tríceps', 'Pecho/Tríceps'], side: 'right', position: 35 },
            'front_delts': { muscles: ['Hombros', 'Deltoides'], side: 'left', position: 20 },
            'side_delts': { muscles: ['Deltoides lateral', 'Hombros'], side: 'left', position: 22 },
            'rear_delts': { muscles: ['Deltoides posterior', 'Hombros'], side: 'right', position: 22 },
            'upper_abs': { muscles: ['Abdomen'], side: 'left', position: 40 },
            'lower_abs': { muscles: ['Abdomen'], side: 'left', position: 48 },
            'obliques': { muscles: ['Abdomen'], side: 'left', position: 44 },
            'quads': { muscles: ['Cuádriceps'], side: 'left', position: 60 },
            'hamstrings': { muscles: ['Isquiotibiales', 'Femoral'], side: 'right', position: 60 },
            'calves': { muscles: ['Gemelos', 'Pantorrillas'], side: 'left', position: 80 },
            'forearms': { muscles: ['Antebrazos', 'Antebrazo'], side: 'left', position: 45 },
            'lats': { muscles: ['Dorsal', 'Espalda grosor', 'Espalda media', 'Espalda/Bíceps'], side: 'right', position: 35 },
            'upper_traps': { muscles: ['Trapecio', 'Espalda'], side: 'right', position: 18 },
            'lower_traps': { muscles: ['Trapecio', 'Espalda media'], side: 'right', position: 30 },
            'rhomboids': { muscles: ['Espalda media', 'Dorsal'], side: 'right', position: 32 },
            'lower_back': { muscles: ['Lumbares', 'Lumbar', 'Espalda'], side: 'right', position: 50 },
            'glutes': { muscles: ['Glúteos'], side: 'right', position: 55 },
            'hip_abductor': { muscles: ['Aductores'], side: 'left', position: 58 },
            'hip_adductor': { muscles: ['Aductores'], side: 'left', position: 58 }
        };

        // Esperar a que el SVG se cargue
        const svgObject = document.getElementById('muscle-svg');
        svgObject.addEventListener('load', function() {
            svgDoc = svgObject.contentDocument;
            if (svgDoc) {
                aplicarEstilosIniciales();
                configurarInteractividad();
            }
        });

        // Cargar ejercicios de la base de datos
        async function cargarEjercicios() {
            try {
                const response = await fetch('api_ejercicios.php?action=obtener_todos');
                const data = await response.json();

                if (data.success) {
                    ejercicios = data.ejercicios;
                    console.log(`✓ ${ejercicios.length} ejercicios cargados`);
                }
            } catch (error) {
                console.error('Error al cargar ejercicios:', error);
            }
        }

        function configurarInteractividad() {
            if (!svgDoc) return;

            // Obtener todos los grupos de músculos del SVG
            Object.keys(muscleMapping).forEach(svgGroupId => {
                const group = svgDoc.getElementById(svgGroupId);
                if (group) {
                    // Cambiar el estilo por defecto
                    const paths = group.querySelectorAll('path');
                    paths.forEach(path => {
                        path.style.cursor = 'pointer';
                        path.style.transition = 'all 0.3s ease';
                    });

                    // Agregar eventos
                    group.addEventListener('mouseenter', function(e) {
                        const config = muscleMapping[svgGroupId];
                        highlightMuscle(svgGroupId, true);
                        mostrarTooltip(config.muscles, config.side, config.position);
                    });

                    group.addEventListener('mouseleave', function() {
                        highlightMuscle(svgGroupId, false);
                        ocultarTooltip();
                    });

                    group.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const config = muscleMapping[svgGroupId];

                        // Toggle
                        if (group.classList.contains('active')) {
                            group.classList.remove('active');
                            highlightMuscle(svgGroupId, false);
                        } else {
                            // Desactivar todos primero
                            Object.keys(muscleMapping).forEach(id => {
                                const g = svgDoc.getElementById(id);
                                if (g) g.classList.remove('active');
                                highlightMuscle(id, false);
                            });

                            group.classList.add('active');
                            highlightMuscle(svgGroupId, true);
                        }
                    });
                }
            });
        }

        function highlightMuscle(svgGroupId, highlight) {
            if (!svgDoc) return;

            const group = svgDoc.getElementById(svgGroupId);
            if (group) {
                const paths = group.querySelectorAll('path');
                paths.forEach(path => {
                    if (highlight) {
                        path.style.fill = '#1a1a1a';
                        path.style.stroke = '#000';
                        path.style.strokeWidth = '15';
                        path.style.filter = 'drop-shadow(0 0 10px rgba(0,0,0,0.8))';
                    } else {
                        path.style.fill = '#d5d5d5';
                        path.style.stroke = '#999';
                        path.style.strokeWidth = '8';
                        path.style.filter = 'none';
                    }
                });
            }
        }

        // Aplicar estilos iniciales al SVG
        function aplicarEstilosIniciales() {
            if (!svgDoc) return;

            // Cambiar colores de todos los músculos a gris
            Object.keys(muscleMapping).forEach(svgGroupId => {
                const group = svgDoc.getElementById(svgGroupId);
                if (group) {
                    const paths = group.querySelectorAll('path');
                    paths.forEach(path => {
                        path.style.fill = '#d5d5d5';
                        path.style.stroke = '#999';
                        path.style.strokeWidth = '8';
                    });
                }
            });

            // Cambiar bordes a gris claro
            const frontBorders = svgDoc.getElementById('front_borders');
            const rearBorders = svgDoc.getElementById('rear_borders');
            const neck = svgDoc.getElementById('neck');
            const face = svgDoc.getElementById('face');

            if (frontBorders) {
                const paths = frontBorders.querySelectorAll('path');
                paths.forEach(path => {
                    path.style.stroke = '#bbb';
                    path.style.strokeWidth = '3';
                });
            }

            if (rearBorders) {
                const paths = rearBorders.querySelectorAll('path');
                paths.forEach(path => {
                    path.style.stroke = '#bbb';
                    path.style.strokeWidth = '3';
                });
            }

            // Cambiar cuello y cara a gris también (no son interactivos)
            if (neck) {
                const paths = neck.querySelectorAll('path');
                paths.forEach(path => {
                    path.style.fill = '#d5d5d5';
                    path.style.stroke = '#999';
                    path.style.strokeWidth = '5';
                });
            }

            if (face) {
                const paths = face.querySelectorAll('path');
                paths.forEach(path => {
                    path.style.fill = '#e5e5e5';
                    path.style.stroke = '#aaa';
                    path.style.strokeWidth = '3';
                });
            }

            // Aplicar gris a TODOS los elementos del SVG que tengan clase st4 o st5 (rojo por defecto)
            const allPaths = svgDoc.querySelectorAll('path.st4, path.st5, path[fill="#FF0000"]');
            allPaths.forEach(path => {
                // Solo si no está ya gestionado por un grupo muscular
                const parent = path.closest('g[id]');
                if (!parent || !muscleMapping[parent.id]) {
                    path.style.fill = '#d5d5d5';
                    path.style.stroke = '#999';
                    path.style.strokeWidth = '5';
                }
            });
        }


        // Funciones del tooltip
        function mostrarTooltip(muscleNames, side, position) {
            const tooltip = document.getElementById('muscle-tooltip');
            const title = document.getElementById('tooltip-title');
            const exercisesList = document.getElementById('tooltip-exercises');
            const count = document.getElementById('tooltip-count');

            // Filtrar ejercicios que trabajan cualquiera de estos músculos
            let ejerciciosMusculo = ejercicios.filter(ej => {
                return muscleNames.some(muscleName => {
                    // Comparación insensible a mayúsculas y tildes
                    const muscleNormalized = muscleName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    const grupoNormalized = ej.grupo_muscular.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    return grupoNormalized.includes(muscleNormalized) || muscleNormalized.includes(grupoNormalized);
                });
            });

            // Eliminar ejercicios duplicados por nombre
            const ejerciciosUnicos = [];
            const nombresVistos = new Set();

            ejerciciosMusculo.forEach(ej => {
                if (!nombresVistos.has(ej.nombre)) {
                    nombresVistos.add(ej.nombre);
                    ejerciciosUnicos.push(ej);
                }
            });

            const displayName = muscleNames[0]; // Usar el primer nombre para el título

            if (ejerciciosUnicos.length === 0) {
                title.textContent = displayName;
                exercisesList.innerHTML = `<li style="color: #999; font-style: italic;">No hay ejercicios registrados</li>`;
                count.textContent = '';
            } else {
                title.textContent = displayName;

                // Mostrar todos los ejercicios únicos
                exercisesList.innerHTML = ejerciciosUnicos
                    .map(ej => `<li>• ${ej.nombre}</li>`)
                    .join('');

                count.textContent = `${ejerciciosUnicos.length} ejercicio${ejerciciosUnicos.length !== 1 ? 's' : ''} total${ejerciciosUnicos.length !== 1 ? 'es' : ''}`;
            }

            // Cambiar lado del tooltip y posición vertical
            tooltip.classList.remove('left', 'right');
            tooltip.classList.add(side);
            tooltip.style.top = position + '%';

            // Mostrar tooltip
            tooltip.classList.add('show');
        }

        function ocultarTooltip() {
            const tooltip = document.getElementById('muscle-tooltip');
            tooltip.classList.remove('show');
        }

        // Desactivar al hacer click fuera
        document.addEventListener('click', function() {
            if (svgDoc) {
                Object.keys(muscleMapping).forEach(id => {
                    const g = svgDoc.getElementById(id);
                    if (g) g.classList.remove('active');
                    highlightMuscle(id, false);
                });
            }
            desactivarTarjetas();
            ocultarTooltip();
        });

        // Cargar al inicio
        cargarEjercicios();
    </script>
</body>
</html>
