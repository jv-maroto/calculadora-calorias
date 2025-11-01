/**
 * CALCULADORA DE GRASA CORPORAL - MÉTODO JACKSON-POLLOCK
 * Implementa las fórmulas de 3 y 7 pliegues cutáneos
 * Precisión: ±3.5% error estándar
 */

// Variable global para almacenar el resultado actual
let resultadoJPActual = null;

/**
 * Abrir modal de calculadora de grasa
 */
function abrirCalculadoraGrasa() {
    const modal = new bootstrap.Modal(document.getElementById('modalCalculadoraGrasa'));
    modal.show();

    // Configurar los sitios según el sexo seleccionado
    actualizarSitiosPliegues();

    // Ocultar resultado previo
    document.getElementById('resultado-jp').style.display = 'none';
    document.getElementById('btn-usar-resultado-jp').style.display = 'none';
}

/**
 * Actualizar los sitios de medición según el sexo
 */
function actualizarSitiosPliegues() {
    const sexo = document.querySelector('input[name="sexo"]:checked').value;

    if (sexo === 'hombre') {
        // HOMBRES: Pecho, Abdomen, Muslo
        document.getElementById('sitios-3-texto').innerHTML =
            '<strong>Hombres:</strong> Pecho, Abdomen, Muslo';
        document.getElementById('label-pliegue-1').textContent = 'Pecho';
        document.getElementById('desc-pliegue-1').textContent = 'Diagonal entre axila y pezón';
        document.getElementById('label-pliegue-2').textContent = 'Abdomen';
        document.getElementById('desc-pliegue-2').textContent = 'Vertical, 2cm al lado del ombligo';
        document.getElementById('label-pliegue-3').textContent = 'Muslo';
        document.getElementById('desc-pliegue-3').textContent = 'Vertical, parte frontal del muslo';
    } else {
        // MUJERES: Tríceps, Suprailiaco, Muslo
        document.getElementById('sitios-3-texto').innerHTML =
            '<strong>Mujeres:</strong> Tríceps, Suprailiaco, Muslo';
        document.getElementById('label-pliegue-1').textContent = 'Tríceps';
        document.getElementById('desc-pliegue-1').textContent = 'Vertical, parte trasera del brazo';
        document.getElementById('label-pliegue-2').textContent = 'Suprailiaco';
        document.getElementById('desc-pliegue-2').textContent = 'Diagonal, sobre cresta ilíaca';
        document.getElementById('label-pliegue-3').textContent = 'Muslo';
        document.getElementById('desc-pliegue-3').textContent = 'Vertical, parte frontal del muslo';
    }
}

/**
 * Alternar entre formularios de 3 y 7 pliegues
 */
document.addEventListener('DOMContentLoaded', function() {
    // Listener para cambio de método
    const radios = document.querySelectorAll('input[name="metodo-jp"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === '3') {
                document.getElementById('form-jp-3').style.display = 'block';
                document.getElementById('form-jp-7').style.display = 'none';
            } else {
                document.getElementById('form-jp-3').style.display = 'none';
                document.getElementById('form-jp-7').style.display = 'block';
            }
            // Ocultar resultado al cambiar método
            document.getElementById('resultado-jp').style.display = 'none';
            document.getElementById('btn-usar-resultado-jp').style.display = 'none';
        });
    });

    // Listener para cambio de sexo (actualizar sitios)
    const radiosSexo = document.querySelectorAll('input[name="sexo"]');
    radiosSexo.forEach(radio => {
        radio.addEventListener('change', actualizarSitiosPliegues);
    });
});

/**
 * CALCULAR PORCENTAJE DE GRASA - MÉTODO JACKSON-POLLOCK
 */
function calcularJacksonPollock() {
    const metodo = document.querySelector('input[name="metodo-jp"]:checked').value;
    const sexo = document.querySelector('input[name="sexo"]:checked').value;
    const edad = parseInt(document.getElementById('edad').value) || 25;

    let porcentajeGrasa = null;

    if (metodo === '3') {
        // MÉTODO DE 3 PLIEGUES
        const p1 = parseFloat(document.getElementById('pliegue_1').value);
        const p2 = parseFloat(document.getElementById('pliegue_2').value);
        const p3 = parseFloat(document.getElementById('pliegue_3').value);

        if (!p1 || !p2 || !p3) {
            alert('❌ Por favor, introduce los 3 pliegues cutáneos');
            return;
        }

        const sumaPliegues = p1 + p2 + p3;
        const sumaPlieguesCuadrado = sumaPliegues * sumaPliegues;

        let densidadCorporal;

        if (sexo === 'hombre') {
            // FÓRMULA JACKSON-POLLOCK 3 PLIEGUES - HOMBRES
            // Pliegues: Pecho, Abdomen, Muslo
            densidadCorporal = 1.10938 - (0.0008267 * sumaPliegues) +
                               (0.0000016 * sumaPlieguesCuadrado) -
                               (0.0002574 * edad);
        } else {
            // FÓRMULA JACKSON-POLLOCK 3 PLIEGUES - MUJERES
            // Pliegues: Tríceps, Suprailiaco, Muslo
            densidadCorporal = 1.0994921 - (0.0009929 * sumaPliegues) +
                               (0.0000023 * sumaPlieguesCuadrado) -
                               (0.0001392 * edad);
        }

        // Convertir densidad corporal a % grasa (Fórmula de Siri)
        porcentajeGrasa = ((4.95 / densidadCorporal) - 4.50) * 100;

    } else {
        // MÉTODO DE 7 PLIEGUES
        const pPecho = parseFloat(document.getElementById('pliegue_pecho').value);
        const pAbdomen = parseFloat(document.getElementById('pliegue_abdomen').value);
        const pMuslo = parseFloat(document.getElementById('pliegue_muslo').value);
        const pTriceps = parseFloat(document.getElementById('pliegue_triceps').value);
        const pSubescapular = parseFloat(document.getElementById('pliegue_subescapular').value);
        const pSuprailiaco = parseFloat(document.getElementById('pliegue_suprailiaco').value);
        const pAxilar = parseFloat(document.getElementById('pliegue_axilar').value);

        if (!pPecho || !pAbdomen || !pMuslo || !pTriceps || !pSubescapular || !pSuprailiaco || !pAxilar) {
            alert('❌ Por favor, introduce los 7 pliegues cutáneos');
            return;
        }

        const sumaPliegues = pPecho + pAbdomen + pMuslo + pTriceps + pSubescapular + pSuprailiaco + pAxilar;
        const sumaPlieguesCuadrado = sumaPliegues * sumaPliegues;

        let densidadCorporal;

        if (sexo === 'hombre') {
            // FÓRMULA JACKSON-POLLOCK 7 PLIEGUES - HOMBRES
            densidadCorporal = 1.112 - (0.00043499 * sumaPliegues) +
                               (0.00000055 * sumaPlieguesCuadrado) -
                               (0.00028826 * edad);
        } else {
            // FÓRMULA JACKSON-POLLOCK 7 PLIEGUES - MUJERES
            densidadCorporal = 1.097 - (0.00046971 * sumaPliegues) +
                               (0.00000056 * sumaPlieguesCuadrado) -
                               (0.00012828 * edad);
        }

        // Convertir densidad corporal a % grasa (Fórmula de Siri)
        porcentajeGrasa = ((4.95 / densidadCorporal) - 4.50) * 100;
    }

    // Limitar valores razonables (3% - 50%)
    porcentajeGrasa = Math.max(3, Math.min(50, porcentajeGrasa));

    // Guardar resultado
    resultadoJPActual = porcentajeGrasa;

    // Mostrar resultado
    document.getElementById('resultado-jp-valor').textContent = porcentajeGrasa.toFixed(1);
    document.getElementById('resultado-jp-metodo').textContent =
        `Jackson-Pollock ${metodo} pliegues (${sexo === 'hombre' ? 'Hombre' : 'Mujer'})`;
    document.getElementById('resultado-jp').style.display = 'block';
    document.getElementById('btn-usar-resultado-jp').style.display = 'inline-block';
}

/**
 * Usar el resultado calculado en el formulario principal
 */
function usarResultadoJP() {
    if (resultadoJPActual === null) {
        alert('❌ Error: No hay resultado calculado');
        return;
    }

    // Rellenar el campo de porcentaje de grasa
    document.getElementById('porcentaje_grasa_input').value = resultadoJPActual.toFixed(1);

    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCalculadoraGrasa'));
    modal.hide();

    // Mostrar mensaje de confirmación
    alert(`✅ Porcentaje de grasa establecido: ${resultadoJPActual.toFixed(1)}%\n\nYa puedes calcular tu plan nutricional.`);
}

/**
 * Actualizar cálculo de grasa con método Navy (circunferencias)
 * Se ejecuta cuando cambian los valores de circunferencias
 */
function calcularGrasaNavy() {
    const sexo = document.querySelector('input[name="sexo"]:checked').value;
    const altura = parseFloat(document.getElementById('altura').value);
    const cintura = parseFloat(document.getElementById('circunferencia_cintura').value);
    const cuello = parseFloat(document.getElementById('circunferencia_cuello').value);
    const cadera = parseFloat(document.getElementById('circunferencia_cadera').value);

    // Si ya hay valor manual de porcentaje de grasa, no calcular Navy
    if (document.getElementById('porcentaje_grasa_input').value) {
        document.getElementById('resultado-grasa-navy').style.display = 'none';
        return;
    }

    let porcentajeGrasa = null;

    if (cintura && cuello && altura) {
        if (sexo === 'hombre') {
            // Fórmula Navy para hombres
            const log10Abdomen = Math.log10(cintura - cuello);
            const log10Altura = Math.log10(altura);
            porcentajeGrasa = 86.010 * log10Abdomen - 70.041 * log10Altura + 36.76;
        } else if (cadera) {
            // Fórmula Navy para mujeres
            const log10Circunferencias = Math.log10(cintura + cadera - cuello);
            const log10Altura = Math.log10(altura);
            porcentajeGrasa = 163.205 * log10Circunferencias - 97.684 * log10Altura - 78.387;
        }

        // Limitar valores razonables (5% - 50%)
        if (porcentajeGrasa !== null) {
            porcentajeGrasa = Math.max(5, Math.min(50, porcentajeGrasa));

            // Mostrar resultado
            document.getElementById('valor-grasa-navy').textContent =
                `${porcentajeGrasa.toFixed(1)}% (menos preciso que Jackson-Pollock)`;
            document.getElementById('resultado-grasa-navy').style.display = 'block';
        }
    } else {
        document.getElementById('resultado-grasa-navy').style.display = 'none';
    }
}

/**
 * Listeners para actualizar cálculo Navy en tiempo real
 */
document.addEventListener('DOMContentLoaded', function() {
    const camposNavy = ['circunferencia_cintura', 'circunferencia_cuello', 'circunferencia_cadera'];

    camposNavy.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', calcularGrasaNavy);
        }
    });

    // También actualizar cuando cambia el sexo o altura
    document.querySelectorAll('input[name="sexo"]').forEach(radio => {
        radio.addEventListener('change', calcularGrasaNavy);
    });

    const alturaInput = document.getElementById('altura');
    if (alturaInput) {
        alturaInput.addEventListener('input', calcularGrasaNavy);
    }

    // Limpiar resultado Navy si se introduce % manual
    const porcentajeManual = document.getElementById('porcentaje_grasa_input');
    if (porcentajeManual) {
        porcentajeManual.addEventListener('input', function() {
            if (this.value) {
                document.getElementById('resultado-grasa-navy').style.display = 'none';
            } else {
                calcularGrasaNavy();
            }
        });
    }
});
