// Navegación del wizard
let currentStep = 1;
const totalSteps = 7;

function nextStep(step) {
    // Validar campos del paso actual
    if (!validateStep(step)) {
        return;
    }

    // Ocultar paso actual
    document.getElementById(`step-${step}`).classList.remove('active');

    // Mostrar siguiente paso
    currentStep = step + 1;
    document.getElementById(`step-${currentStep}`).classList.add('active');

    // Actualizar barra de progreso
    updateProgressBar();

    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(step) {
    // Ocultar paso actual
    document.getElementById(`step-${step}`).classList.remove('active');

    // Mostrar paso anterior
    currentStep = step - 1;
    document.getElementById(`step-${currentStep}`).classList.add('active');

    // Actualizar barra de progreso
    updateProgressBar();

    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateProgressBar() {
    const percentage = (currentStep / totalSteps) * 100;
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    progressBar.textContent = `Paso ${currentStep} de ${totalSteps}`;
}

function validateStep(step) {
    const stepElement = document.getElementById(`step-${step}`);
    const requiredInputs = stepElement.querySelectorAll('[required]');

    for (let input of requiredInputs) {
        if (!input.value || input.value === '') {
            alert('Por favor completa todos los campos obligatorios (*)');
            input.focus();
            return false;
        }
    }

    // Validaciones específicas
    if (step === 4) {
        const caloriasActuales = parseFloat(document.getElementById('calorias_actuales').value);
        const pesoActual = parseFloat(document.getElementById('peso_actual').value);
        const pesoPerdido = parseFloat(document.getElementById('peso_perdido').value);

        if (caloriasActuales < 1000) {
            alert('⚠️ Las calorías actuales parecen muy bajas. Verifica el valor.');
            return false;
        }

        if (pesoPerdido > pesoActual) {
            alert('⚠️ El peso perdido no puede ser mayor que tu peso actual.');
            return false;
        }
    }

    return true;
}

// ==================== CÁLCULOS PRINCIPALES ====================

function calcularReverseDiet() {
    // Recopilar todos los datos del formulario
    const datos = recopilarDatos();

    // Validar datos finales
    if (!validarDatosFinales(datos)) {
        return;
    }

    // PASO 1: Calcular TMB (Tasa Metabólica Basal)
    const tmb = calcularTMB(datos);

    // PASO 2: Calcular TDEE teórico
    const tdeeTeorico = calcularTDEE(tmb, datos);

    // PASO 3: Ajustar TDEE por adaptación metabólica
    const { tdeeAjustado, ajustes } = ajustarTDEEPorAdaptacion(tdeeTeorico, datos);

    // PASO 4: Calcular déficit actual real
    const deficitActual = tdeeAjustado - datos.caloriasActuales;

    // PASO 5: Diseñar plan de reverse diet
    const planReverse = calcularPlanReverse(datos.caloriasActuales, tdeeAjustado, deficitActual, datos);

    // PASO 6: Calcular peso esperado durante reverse
    const proyeccionPeso = calcularProyeccionPeso(datos.pesoActual, planReverse, tdeeAjustado, datos);

    // PASO 7: Calcular calorías para el bulk
    const caloriasBulk = calcularCaloriasBulk(tdeeAjustado, datos);

    // PASO 8: Proyectar resultados del bulk
    const proyeccionBulk = proyectarBulk(proyeccionPeso.pesoBase, caloriasBulk, tdeeAjustado, datos);

    // PASO 9: Calcular % grasa corporal si es posible
    const grasaCorporal = calcularGrasaCorporal(datos);

    // Compilar resultados
    const resultados = {
        tmb,
        tdeeTeorico,
        tdeeAjustado,
        ajustes,
        deficitActual,
        planReverse,
        proyeccionPeso,
        caloriasBulk,
        proyeccionBulk,
        grasaCorporal,
        datos
    };

    // Mostrar resultados
    mostrarResultados(resultados);

    // Guardar en sessionStorage para posible exportación
    sessionStorage.setItem('reverseDietResults', JSON.stringify(resultados));
}

function recopilarDatos() {
    return {
        // Personales
        edad: parseInt(document.getElementById('edad').value),
        sexo: document.getElementById('sexo').value,
        pesoActual: parseFloat(document.getElementById('peso_actual').value),
        altura: parseInt(document.getElementById('altura').value),

        // Actividad física
        diasGym: parseInt(document.getElementById('dias_gym').value),
        horasGym: parseFloat(document.getElementById('horas_gym').value),
        diasCardio: parseInt(document.getElementById('dias_cardio').value),
        horasCardio: parseFloat(document.getElementById('horas_cardio').value),
        tipoCardio: document.getElementById('tipo_cardio').value,

        // Estilo de vida
        tipoTrabajo: document.getElementById('tipo_trabajo').value,
        horasTrabajo: parseFloat(document.getElementById('horas_trabajo').value),
        horasSueno: parseFloat(document.getElementById('horas_sueno').value),

        // Historial déficit
        tiempoDeficit: document.getElementById('tiempo_deficit').value,
        caloriasActuales: parseFloat(document.getElementById('calorias_actuales').value),
        pesoPerdido: parseFloat(document.getElementById('peso_perdido').value),
        pesoMaximo: parseFloat(document.getElementById('peso_maximo').value) || null,

        // Nivel entrenamiento
        anosEntrenando: document.getElementById('anos_entrenando').value,

        // Composición corporal
        grasaCorporal: parseFloat(document.getElementById('grasa_corporal').value) || null,
        circunferenciaCintura: parseFloat(document.getElementById('circunferencia_cintura').value) || null,
        circunferenciaCuello: parseFloat(document.getElementById('circunferencia_cuello').value) || null,
        circunferenciaCadera: parseFloat(document.getElementById('circunferencia_cadera').value) || null,

        // Objetivo bulk
        tipoBulk: document.getElementById('tipo_bulk').value,
        duracionBulk: parseInt(document.getElementById('duracion_bulk').value)
    };
}

function validarDatosFinales(datos) {
    // Validación de rangos de entrenamiento total
    const horasEntrenamientoTotal = (datos.diasGym * datos.horasGym) + (datos.diasCardio * datos.horasCardio);
    if (horasEntrenamientoTotal > 20) {
        if (!confirm('⚠️ Entrenas más de 20 horas semanales. ¿Estás seguro de que es correcto? Esto es muy alto.')) {
            return false;
        }
    }

    return true;
}

// ==================== CÁLCULO TMB ====================
function calcularTMB(datos) {
    // Fórmula Mifflin-St Jeor (más precisa que Harris-Benedict)
    let tmb;

    if (datos.sexo === 'hombre') {
        tmb = 10 * datos.pesoActual + 6.25 * datos.altura - 5 * datos.edad + 5;
    } else {
        tmb = 10 * datos.pesoActual + 6.25 * datos.altura - 5 * datos.edad - 161;
    }

    return Math.round(tmb);
}

// ==================== CÁLCULO TDEE ====================
function calcularTDEE(tmb, datos) {
    // Factor base según tipo de trabajo
    let factorBase;
    if (datos.tipoTrabajo === 'sedentario') {
        factorBase = 1.2;
    } else if (datos.tipoTrabajo === 'activo') {
        factorBase = 1.4;
    } else { // muy_activo
        factorBase = 1.6;
    }

    // Calcular calorías quemadas por entrenamiento de pesas
    const caloriasGymSemanal = datos.diasGym * datos.horasGym * 350; // ~350 kcal/hora pesas
    const caloriasGymDiarias = caloriasGymSemanal / 7;

    // Calcular calorías quemadas por cardio (varía según tipo)
    let intensidadCardio = 400; // kcal/hora base

    const intensidadesCardio = {
        'ninguno': 0,
        'caminar_ligero': 250,
        'caminar_rapido': 350,
        'correr_moderado': 550,
        'correr_intenso': 750,
        'bicicleta': 450,
        'natacion': 500,
        'eliptica': 400,
        'hiit': 700,
        'otro': 400
    };

    intensidadCardio = intensidadesCardio[datos.tipoCardio] || 400;

    const caloriasCardioSemanal = datos.diasCardio * datos.horasCardio * intensidadCardio;
    const caloriasCardioDiarias = caloriasCardioSemanal / 7;

    // TDEE = TMB × factor base + calorías entrenamiento
    const tdee = (tmb * factorBase) + caloriasGymDiarias + caloriasCardioDiarias;

    return Math.round(tdee);
}

// ==================== AJUSTE POR ADAPTACIÓN METABÓLICA ====================
function ajustarTDEEPorAdaptacion(tdeeTeorico, datos) {
    let reduccionTotal = 0;
    const ajustes = [];

    // 1. Ajuste por tiempo en déficit
    let reduccionTiempo = 0;
    if (datos.tiempoDeficit === '1-2') {
        reduccionTiempo = 0.05; // -5%
        ajustes.push({ tipo: 'Tiempo en déficit (1-2 meses)', valor: '-5%' });
    } else if (datos.tiempoDeficit === '2-3') {
        reduccionTiempo = 0.08; // -8%
        ajustes.push({ tipo: 'Tiempo en déficit (2-3 meses)', valor: '-8%' });
    } else if (datos.tiempoDeficit === '3-6') {
        reduccionTiempo = 0.10; // -10%
        ajustes.push({ tipo: 'Tiempo en déficit (3-6 meses)', valor: '-10%' });
    } else if (datos.tiempoDeficit === '6+') {
        reduccionTiempo = 0.12; // -12%
        ajustes.push({ tipo: 'Tiempo en déficit (>6 meses)', valor: '-12%' });
    }
    reduccionTotal += reduccionTiempo;

    // 2. Ajuste por magnitud del déficit actual
    const ratioDeficit = datos.caloriasActuales / tdeeTeorico;
    let reduccionMagnitud = 0;

    if (ratioDeficit < 0.65) {
        reduccionMagnitud = 0.05; // Déficit muy agresivo
        ajustes.push({ tipo: 'Déficit muy agresivo (<65% TDEE)', valor: '-5%' });
    } else if (ratioDeficit < 0.75) {
        reduccionMagnitud = 0.03; // Déficit agresivo
        ajustes.push({ tipo: 'Déficit agresivo (<75% TDEE)', valor: '-3%' });
    }
    reduccionTotal += reduccionMagnitud;

    // 3. Ajuste por pérdida de peso total
    let reduccionPerdida = 0;
    if (datos.pesoPerdido > 30) {
        reduccionPerdida = 0.05;
        ajustes.push({ tipo: 'Pérdida de peso masiva (>30 kg)', valor: '-5%' });
    } else if (datos.pesoPerdido > 20) {
        reduccionPerdida = 0.03;
        ajustes.push({ tipo: 'Pérdida de peso significativa (>20 kg)', valor: '-3%' });
    } else if (datos.pesoPerdido > 10) {
        reduccionPerdida = 0.02;
        ajustes.push({ tipo: 'Pérdida de peso moderada (>10 kg)', valor: '-2%' });
    }
    reduccionTotal += reduccionPerdida;

    // Aplicar reducción total (máximo 20%)
    reduccionTotal = Math.min(reduccionTotal, 0.20);

    const tdeeAjustado = Math.round(tdeeTeorico * (1 - reduccionTotal));

    ajustes.push({
        tipo: 'REDUCCIÓN TOTAL',
        valor: `-${(reduccionTotal * 100).toFixed(0)}%`,
        destacado: true
    });

    return { tdeeAjustado, ajustes };
}

// ==================== PLAN DE REVERSE DIET ====================
function calcularPlanReverse(caloriasActuales, tdeeAjustado, deficitActual, datos) {
    const semanas = [];
    let caloriasSemanales = caloriasActuales;

    // Determinar duración del reverse según déficit
    let duracionSemanas;
    if (deficitActual > 800) {
        duracionSemanas = 4;
    } else if (deficitActual > 500) {
        duracionSemanas = 3;
    } else if (deficitActual > 300) {
        duracionSemanas = 2;
    } else {
        duracionSemanas = 1;
    }

    // Calcular incremento semanal
    let incrementoSemanal;
    if (deficitActual > 1000) {
        incrementoSemanal = 350; // Más conservador si déficit es extremo
    } else {
        incrementoSemanal = 400; // Estándar
    }

    // Ajustar última semana para llegar exacto al TDEE
    const caloriasRestantes = tdeeAjustado - caloriasActuales;
    const incrementoTotal = (duracionSemanas - 1) * incrementoSemanal;

    if (incrementoTotal > caloriasRestantes) {
        // Recalcular incremento para que sea uniforme
        incrementoSemanal = Math.round(caloriasRestantes / duracionSemanas);
    }

    // Generar plan semana por semana
    for (let i = 1; i <= duracionSemanas; i++) {
        const caloriasInicio = caloriasSemanales;

        if (i < duracionSemanas) {
            caloriasSemanales = Math.min(caloriasSemanales + incrementoSemanal, tdeeAjustado);
        } else {
            // Última semana: ajustar para llegar exacto al TDEE
            caloriasSemanales = tdeeAjustado;
        }

        const incremento = caloriasSemanales - caloriasInicio;

        semanas.push({
            numero: i,
            caloriasInicio: Math.round(caloriasInicio),
            caloriasFin: Math.round(caloriasSemanales),
            incremento: Math.round(incremento),
            descripcion: obtenerDescripcionSemana(i, duracionSemanas)
        });
    }

    // Semana de estabilización en mantenimiento
    semanas.push({
        numero: duracionSemanas + 1,
        caloriasInicio: tdeeAjustado,
        caloriasFin: tdeeAjustado,
        incremento: 0,
        descripcion: 'Semana de estabilización - Mantén estas calorías',
        esEstabilizacion: true
    });

    return {
        semanas,
        duracionTotal: duracionSemanas + 1,
        caloriasInicio: caloriasActuales,
        caloriasFinal: tdeeAjustado,
        incrementoTotal: tdeeAjustado - caloriasActuales
    };
}

function obtenerDescripcionSemana(numSemana, duracionTotal) {
    if (numSemana === 1) {
        return 'Primera subida - Tu cuerpo empieza a recibir más energía';
    } else if (numSemana === duracionTotal) {
        return 'Última subida - Llegamos al mantenimiento';
    } else {
        return 'Incremento progresivo - Adaptación metabólica';
    }
}

// ==================== PROYECCIÓN DE PESO ====================
function calcularProyeccionPeso(pesoInicial, planReverse, tdeeAjustado, datos) {
    const semanas = [];
    let pesoActual = pesoInicial;
    let aguaGlucogenoAcumulado = 0;
    let grasaNetaAcumulada = 0;

    planReverse.semanas.forEach((semana, index) => {
        // Ganancia de agua/glucógeno (disminuye con el tiempo)
        let aguaSemana = 0;
        if (semana.numero === 1) {
            aguaSemana = 0.7; // 0.5-1 kg (usamos promedio 0.7)
        } else if (semana.numero === 2) {
            aguaSemana = 0.4; // 0.3-0.5 kg
        } else if (semana.numero === 3) {
            aguaSemana = 0.3; // 0.2-0.4 kg
        } else if (!semana.esEstabilizacion) {
            aguaSemana = 0.15; // 0.1-0.2 kg
        } else {
            aguaSemana = 0; // Ya estabilizado
        }

        aguaGlucogenoAcumulado += aguaSemana;

        // Calcular déficit/superávit real de la semana
        const caloriasPromedio = (semana.caloriasInicio + semana.caloriasFin) / 2;
        const balanceCaloricoSemanal = (caloriasPromedio - tdeeAjustado) * 7;

        // Grasa ganada o perdida (7700 kcal = 1 kg grasa)
        const grasaSemana = balanceCaloricoSemanal / 7700;
        grasaNetaAcumulada += grasaSemana;

        // Peso total de la semana
        const cambioTotal = aguaSemana + grasaSemana;
        pesoActual += cambioTotal;

        semanas.push({
            numero: semana.numero,
            pesoInicio: semana.numero === 1 ? pesoInicial : semanas[index - 1].pesoFin,
            pesoFin: Math.round(pesoActual * 10) / 10,
            aguaGlucogeno: Math.round(aguaSemana * 100) / 100,
            grasaNeta: Math.round(grasaSemana * 100) / 100,
            cambioTotal: Math.round(cambioTotal * 100) / 100,
            calorias: Math.round((semana.caloriasInicio + semana.caloriasFin) / 2),
            esEstabilizacion: semana.esEstabilizacion || false
        });
    });

    return {
        semanas,
        pesoInicial: pesoInicial,
        pesoFinal: Math.round(pesoActual * 10) / 10,
        pesoBase: Math.round(pesoActual * 10) / 10, // Peso para iniciar bulk
        aguaGlucogenoTotal: Math.round(aguaGlucogenoAcumulado * 10) / 10,
        grasaNetaTotal: Math.round(grasaNetaAcumulada * 100) / 100,
        cambioTotal: Math.round((aguaGlucogenoAcumulado + grasaNetaAcumulada) * 10) / 10
    };
}

// ==================== CALORÍAS PARA BULK ====================
function calcularCaloriasBulk(tdeeAjustado, datos) {
    // Superávit según nivel y tipo de bulk
    let superavitBase;

    // Rangos según nivel de entrenamiento
    const rangos = {
        'novato': { min: 350, max: 500 },        // 15-20%
        'principiante': { min: 300, max: 400 },  // 12-15%
        'intermedio': { min: 250, max: 350 },    // 10-12%
        'avanzado': { min: 200, max: 300 }       // 8-10%
    };

    const rango = rangos[datos.anosEntrenando];

    // Ajuste según tipo de bulk
    if (datos.tipoBulk === 'ultra_limpio') {
        superavitBase = rango.min - 50; // Más conservador
    } else if (datos.tipoBulk === 'limpio') {
        superavitBase = rango.min;
    } else if (datos.tipoBulk === 'balanceado') {
        superavitBase = (rango.min + rango.max) / 2;
    } else { // agresivo
        superavitBase = rango.max;
    }

    // Ajuste por % grasa corporal (si está disponible)
    let ajusteGrasa = 0;
    if (datos.grasaCorporal) {
        if (datos.grasaCorporal > 20) {
            ajusteGrasa = -75; // Más conservador si tiene grasa alta
        } else if (datos.grasaCorporal < 12) {
            ajusteGrasa = +75; // Más agresivo si está definido
        }
    }

    const superavitFinal = Math.round(superavitBase + ajusteGrasa);
    const caloriasBulk = Math.round(tdeeAjustado + superavitFinal);
    const porcentajeSuperavit = ((superavitFinal / tdeeAjustado) * 100).toFixed(1);

    return {
        calorias: caloriasBulk,
        superavit: superavitFinal,
        porcentaje: porcentajeSuperavit,
        tdeeBase: tdeeAjustado
    };
}

// ==================== PROYECCIÓN BULK ====================
function proyectarBulk(pesoBase, caloriasBulk, tdeeAjustado, datos) {
    // Tasa de ganancia muscular mensual según nivel
    const tasasMusculo = {
        'novato': 0.9,        // ~2 lbs/mes
        'principiante': 0.7,  // ~1.5 lbs/mes
        'intermedio': 0.45,   // ~1 lb/mes
        'avanzado': 0.23      // ~0.5 lb/mes
    };

    const kgMusculoPorMes = tasasMusculo[datos.anosEntrenando];

    // Ratio músculo/grasa según tipo de bulk
    const ratios = {
        'ultra_limpio': 0.80,  // 80% músculo / 20% grasa
        'limpio': 0.75,        // 75% músculo / 25% grasa
        'balanceado': 0.70,    // 70% músculo / 30% grasa
        'agresivo': 0.65       // 65% músculo / 35% grasa
    };

    const ratioMusculoGrasa = ratios[datos.tipoBulk];

    // Cálculos totales
    const musculoTotal = kgMusculoPorMes * datos.duracionBulk;
    const pesoTotalGanado = musculoTotal / ratioMusculoGrasa;
    const grasaTotal = pesoTotalGanado - musculoTotal;
    const pesoFinal = pesoBase + pesoTotalGanado;

    // Calcular % grasa final (si tenemos datos iniciales)
    let grasaFinalPorcentaje = null;
    if (datos.grasaCorporal) {
        const masaMagraInicial = pesoBase * (1 - datos.grasaCorporal / 100);
        const masaMagraFinal = masaMagraInicial + musculoTotal;
        const masaGrasaInicial = pesoBase * (datos.grasaCorporal / 100);
        const masaGrasaFinal = masaGrasaInicial + grasaTotal;
        grasaFinalPorcentaje = (masaGrasaFinal / (masaMagraFinal + masaGrasaFinal)) * 100;
    }

    return {
        pesoBase: Math.round(pesoBase * 10) / 10,
        pesoFinal: Math.round(pesoFinal * 10) / 10,
        musculoGanado: Math.round(musculoTotal * 10) / 10,
        grasaGanada: Math.round(grasaTotal * 10) / 10,
        pesoTotalGanado: Math.round(pesoTotalGanado * 10) / 10,
        musculoPorMes: Math.round(kgMusculoPorMes * 100) / 100,
        ratioMusculoGrasa: ratioMusculoGrasa,
        porcentajeMusculo: Math.round(ratioMusculoGrasa * 100),
        porcentajeGrasa: Math.round((1 - ratioMusculoGrasa) * 100),
        grasaInicialPorcentaje: datos.grasaCorporal,
        grasaFinalPorcentaje: grasaFinalPorcentaje ? Math.round(grasaFinalPorcentaje * 10) / 10 : null,
        duracionMeses: datos.duracionBulk
    };
}

// ==================== CÁLCULO GRASA CORPORAL ====================
function calcularGrasaCorporal(datos) {
    // Si ya proporcionó el %
    if (datos.grasaCorporal) {
        return {
            porcentaje: datos.grasaCorporal,
            metodo: 'Proporcionado por el usuario'
        };
    }

    // Método Navy (requiere medidas)
    if (datos.circunferenciaCintura && datos.circunferenciaCuello && datos.altura) {
        let porcentaje;

        if (datos.sexo === 'hombre') {
            // Fórmula Navy para hombres
            const log10Abdomen = Math.log10(datos.circunferenciaCintura - datos.circunferenciaCuello);
            const log10Altura = Math.log10(datos.altura);
            porcentaje = 86.010 * log10Abdomen - 70.041 * log10Altura + 36.76;
        } else if (datos.circunferenciaCadera) {
            // Fórmula Navy para mujeres
            const log10Circunferencias = Math.log10(datos.circunferenciaCintura + datos.circunferenciaCadera - datos.circunferenciaCuello);
            const log10Altura = Math.log10(datos.altura);
            porcentaje = 163.205 * log10Circunferencias - 97.684 * log10Altura - 78.387;
        }

        // Limitar valores razonables
        if (porcentaje) {
            porcentaje = Math.max(5, Math.min(50, porcentaje));
            return {
                porcentaje: Math.round(porcentaje * 10) / 10,
                metodo: 'Método Navy (circunferencias)'
            };
        }
    }

    return {
        porcentaje: null,
        metodo: 'No disponible'
    };
}

// ==================== MOSTRAR RESULTADOS ====================
function mostrarResultados(resultados) {
    const resultadosDiv = document.getElementById('resultados');

    let html = `
        <div class="alert alert-success">
            <h4>✅ Plan Calculado Exitosamente</h4>
            <p class="mb-0">Aquí está tu plan personalizado de transición de déficit a volumen.</p>
        </div>

        <!-- Resumen Ejecutivo -->
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📊 Resumen Ejecutivo</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-info mb-0">
                            <h6>TMB (Basal)</h6>
                            <h4 class="mb-0">${resultados.tmb} kcal</h4>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-warning mb-0">
                            <h6>TDEE Teórico</h6>
                            <h4 class="mb-0">${resultados.tdeeTeorico} kcal</h4>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-danger mb-0">
                            <h6>TDEE Ajustado Real</h6>
                            <h4 class="mb-0">${resultados.tdeeAjustado} kcal</h4>
                            <small>Adaptación metabólica incluida</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-dark mb-0">
                            <h6>Déficit Actual</h6>
                            <h4 class="mb-0">${resultados.deficitActual} kcal</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adaptación Metabólica -->
        ${generarHTMLAdaptacion(resultados.ajustes, resultados.tdeeTeorico, resultados.tdeeAjustado)}

        <!-- Plan de Reverse Diet -->
        ${generarHTMLPlanReverse(resultados.planReverse)}

        <!-- Proyección de Peso -->
        ${generarHTMLProyeccionPeso(resultados.proyeccionPeso)}

        <!-- Calorías para Bulk -->
        ${generarHTMLCaloriasBulk(resultados.caloriasBulk, resultados.datos)}

        <!-- Proyección del Bulk -->
        ${generarHTMLProyeccionBulk(resultados.proyeccionBulk)}

        <!-- Composición Corporal -->
        ${resultados.grasaCorporal.porcentaje ? generarHTMLGrasaCorporal(resultados.grasaCorporal, resultados.datos.pesoActual) : ''}

        <!-- Advertencias y Recomendaciones -->
        ${generarHTMLAdvertencias(resultados)}

        <!-- Botones de Acción -->
        <div class="d-flex gap-2 justify-content-center mt-4">
            <button class="btn btn-success btn-lg" onclick="exportarPDF()">📄 Descargar PDF</button>
            <button class="btn btn-primary btn-lg" onclick="guardarPlan()">💾 Guardar Plan</button>
            <button class="btn btn-secondary" onclick="location.reload()">🔄 Nuevo Cálculo</button>
        </div>
    `;

    resultadosDiv.innerHTML = html;
    resultadosDiv.style.display = 'block';

    // Scroll a resultados
    resultadosDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function generarHTMLAdaptacion(ajustes, tdeeTeorico, tdeeAjustado) {
    let ajustesHTML = '';
    ajustes.forEach(ajuste => {
        const clase = ajuste.destacado ? 'alert-danger' : 'alert-warning';
        const peso = ajuste.destacado ? 'fw-bold' : '';
        ajustesHTML += `
            <tr class="${ajuste.destacado ? 'table-danger' : ''}">
                <td class="${peso}">${ajuste.tipo}</td>
                <td class="text-end ${peso}">${ajuste.valor}</td>
            </tr>
        `;
    });

    const reduccionTotal = tdeeTeorico - tdeeAjustado;

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">⚠️ Adaptación Metabólica Detectada</h4>
            </div>
            <div class="card-body">
                <p><strong>Tu metabolismo se ha adaptado al déficit prolongado.</strong> Tu TDEE real es menor que el teórico.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Factor</th>
                                <th class="text-end">Ajuste</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${ajustesHTML}
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <h5>📉 Reducción Total: ${reduccionTotal} kcal/día</h5>
                    <p class="mb-0"><strong>TDEE Teórico:</strong> ${tdeeTeorico} kcal → <strong>TDEE Real:</strong> ${tdeeAjustado} kcal</p>
                    <small class="text-muted">El reverse diet ayudará a restaurar tu metabolismo gradualmente.</small>
                </div>
            </div>
        </div>
    `;
}

function generarHTMLPlanReverse(planReverse) {
    let semanasHTML = '';
    planReverse.semanas.forEach(semana => {
        const rowClass = semana.esEstabilizacion ? 'table-success' : '';
        const badge = semana.esEstabilizacion ? '<span class="badge bg-success">Estabilización</span>' : '';

        semanasHTML += `
            <tr class="week-row ${rowClass}">
                <td><strong>Semana ${semana.numero}</strong> ${badge}</td>
                <td>${semana.caloriasInicio} kcal</td>
                <td class="text-success"><strong>${semana.caloriasFin} kcal</strong></td>
                <td class="text-primary">${semana.incremento > 0 ? '+' + semana.incremento : semana.incremento} kcal</td>
                <td><small class="text-muted">${semana.descripcion}</small></td>
            </tr>
        `;
    });

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">🔄 Tu Plan de Reverse Diet (Semana a Semana)</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5>📅 Duración Total: ${planReverse.duracionTotal} semanas</h5>
                    <p><strong>Calorías iniciales:</strong> ${planReverse.caloriasInicio} kcal → <strong>Calorías finales:</strong> ${planReverse.caloriasFinal} kcal</p>
                    <p class="mb-0"><strong>Incremento total:</strong> +${planReverse.incrementoTotal} kcal</p>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Semana</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Incremento</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${semanasHTML}
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning mt-3">
                    <h6>💡 Consejos para el Reverse Diet:</h6>
                    <ul class="mb-0">
                        <li>Aumenta las calorías principalmente de carbohidratos (arroz, pasta, avena)</li>
                        <li>Mantén la proteína alta (2-2.2g/kg de peso corporal)</li>
                        <li>No te asustes si ganas 1-2 kg, es agua y glucógeno, no grasa</li>
                        <li>Mantén tu entrenamiento intenso durante todo el proceso</li>
                        <li>Pésate diariamente y haz media semanal para ver tendencias</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
}

function generarHTMLProyeccionPeso(proyeccion) {
    let semanasHTML = '';
    proyeccion.semanas.forEach(semana => {
        const rowClass = semana.esEstabilizacion ? 'table-success' : '';
        const colorCambio = semana.cambioTotal > 0 ? 'text-success' : 'text-danger';

        semanasHTML += `
            <tr class="${rowClass}">
                <td><strong>Semana ${semana.numero}</strong></td>
                <td>${semana.pesoInicio} kg</td>
                <td><strong>${semana.pesoFin} kg</strong></td>
                <td class="${colorCambio}"><strong>${semana.cambioTotal > 0 ? '+' : ''}${semana.cambioTotal} kg</strong></td>
                <td class="text-info">+${semana.aguaGlucogeno} kg</td>
                <td class="${semana.grasaNeta >= 0 ? 'text-success' : 'text-danger'}">${semana.grasaNeta >= 0 ? '+' : ''}${semana.grasaNeta} kg</td>
                <td>${semana.calorias} kcal</td>
            </tr>
        `;
    });

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">⚖️ Proyección de Peso Durante el Reverse</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-primary">
                    <h5>📈 Cambios de Peso Esperados</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Peso Inicial:</strong><br>
                            <h4 class="mb-0">${proyeccion.pesoInicial} kg</h4>
                        </div>
                        <div class="col-md-3">
                            <strong>Peso Final:</strong><br>
                            <h4 class="mb-0">${proyeccion.pesoFinal} kg</h4>
                        </div>
                        <div class="col-md-3">
                            <strong>Agua/Glucógeno:</strong><br>
                            <h4 class="mb-0 text-info">+${proyeccion.aguaGlucogenoTotal} kg</h4>
                        </div>
                        <div class="col-md-3">
                            <strong>Grasa Neta:</strong><br>
                            <h4 class="mb-0 ${proyeccion.grasaNetaTotal >= 0 ? 'text-success' : 'text-danger'}">
                                ${proyeccion.grasaNetaTotal >= 0 ? '+' : ''}${proyeccion.grasaNetaTotal} kg
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Semana</th>
                                <th>Peso Inicio</th>
                                <th>Peso Fin</th>
                                <th>Cambio Total</th>
                                <th>Agua/Glucógeno</th>
                                <th>Grasa Neta</th>
                                <th>Calorías</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${semanasHTML}
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-success mt-3">
                    <h6>✅ Peso Base para Iniciar Bulk:</h6>
                    <h3 class="mb-0">${proyeccion.pesoBase} kg</h3>
                    <small class="text-muted">Este será tu peso estabilizado para comenzar el volumen</small>
                </div>
            </div>
        </div>
    `;
}

function generarHTMLCaloriasBulk(caloriasBulk, datos) {
    const tipoBulkNombres = {
        'ultra_limpio': 'Ultra Limpio (8-10% superávit)',
        'limpio': 'Lean Bulk Óptimo ⭐ (10-12% superávit)',
        'balanceado': 'Balanceado (13-17% superávit)',
        'agresivo': 'Agresivo (20%+ superávit)'
    };

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">🎯 Calorías para el Bulk</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h5>🍽️ Plan Calórico para tu Bulk</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>TDEE (Mantenimiento):</strong><br>
                            <h4>${caloriasBulk.tdeeBase} kcal</h4>
                        </div>
                        <div class="col-md-4">
                            <strong>Superávit:</strong><br>
                            <h4 class="text-success">+${caloriasBulk.superavit} kcal</h4>
                            <small class="text-muted">(${caloriasBulk.porcentaje}% del TDEE)</small>
                        </div>
                        <div class="col-md-4">
                            <strong>Calorías Bulk:</strong><br>
                            <h3 class="text-primary">${caloriasBulk.calorias} kcal/día</h3>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Tipo de Bulk:</strong> ${tipoBulkNombres[datos.tipoBulk]}<br>
                    <strong>Duración Planeada:</strong> ${datos.duracionBulk} meses
                </div>
            </div>
        </div>
    `;
}

function generarHTMLProyeccionBulk(proyeccion) {
    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">📊 Proyección del Bulk (${proyeccion.duracionMeses} meses)</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-primary">
                    <h5>🏋️ Ganancias Esperadas</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Peso Base:</strong><br>
                            <h4>${proyeccion.pesoBase} kg</h4>
                        </div>
                        <div class="col-md-3">
                            <strong>Peso Final:</strong><br>
                            <h4 class="text-success">${proyeccion.pesoFinal} kg</h4>
                        </div>
                        <div class="col-md-3">
                            <strong>Músculo Ganado:</strong><br>
                            <h4 class="text-primary">+${proyeccion.musculoGanado} kg</h4>
                            <small class="text-muted">(${proyeccion.musculoPorMes} kg/mes)</small>
                        </div>
                        <div class="col-md-3">
                            <strong>Grasa Ganada:</strong><br>
                            <h4 class="text-warning">+${proyeccion.grasaGanada} kg</h4>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5>📈 Distribución de Ganancias</h5>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${proyeccion.porcentajeMusculo}%;">
                            ${proyeccion.porcentajeMusculo}% Músculo (${proyeccion.musculoGanado} kg)
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: ${proyeccion.porcentajeGrasa}%;">
                            ${proyeccion.porcentajeGrasa}% Grasa (${proyeccion.grasaGanada} kg)
                        </div>
                    </div>
                    <p class="mt-2 mb-0"><strong>Peso total ganado:</strong> +${proyeccion.pesoTotalGanado} kg en ${proyeccion.duracionMeses} meses</p>
                </div>

                ${proyeccion.grasaFinalPorcentaje ? `
                    <div class="alert alert-warning">
                        <h6>% Grasa Corporal Proyectado:</h6>
                        <p class="mb-0">
                            <strong>Inicial:</strong> ${proyeccion.grasaInicialPorcentaje}% →
                            <strong>Final:</strong> ${proyeccion.grasaFinalPorcentaje}%
                        </p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

function generarHTMLGrasaCorporal(grasaCorporal, peso) {
    const masaMagra = peso * (1 - grasaCorporal.porcentaje / 100);
    const masaGrasa = peso - masaMagra;

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">🔬 Composición Corporal</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5>📊 Análisis Actual</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>% Grasa Corporal:</strong><br>
                            <h3 class="mb-0">${grasaCorporal.porcentaje}%</h3>
                            <small class="text-muted">${grasaCorporal.metodo}</small>
                        </div>
                        <div class="col-md-4">
                            <strong>Masa Magra:</strong><br>
                            <h4 class="mb-0 text-success">${masaMagra.toFixed(1)} kg</h4>
                        </div>
                        <div class="col-md-4">
                            <strong>Masa Grasa:</strong><br>
                            <h4 class="mb-0 text-warning">${masaGrasa.toFixed(1)} kg</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generarHTMLAdvertencias(resultados) {
    const advertencias = [];

    // Advertencia si déficit fue muy largo
    if (resultados.datos.tiempoDeficit === '6+') {
        advertencias.push({
            tipo: 'warning',
            titulo: '⚠️ Déficit Prolongado',
            mensaje: 'Has estado en déficit más de 6 meses. Tu metabolismo necesita recuperación.',
            recomendacion: 'Sigue el reverse diet completo y considera quedarte en mantenimiento 2-4 semanas antes de empezar el bulk.'
        });
    }

    // Advertencia si déficit actual es muy agresivo
    if (resultados.deficitActual > 800) {
        advertencias.push({
            tipo: 'danger',
            titulo: '🚨 Déficit Muy Agresivo',
            mensaje: `Tu déficit actual es de ${resultados.deficitActual} kcal/día, que es muy alto.`,
            recomendacion: 'El reverse diet será más largo para evitar rebote. Sé paciente y sigue el plan.'
        });
    }

    // Advertencia sobre agua/glucógeno
    advertencias.push({
        tipo: 'info',
        titulo: 'ℹ️ Sobre el Aumento de Peso Inicial',
        mensaje: 'Ganarás 1-2 kg en las primeras semanas, principalmente agua y glucógeno.',
        recomendacion: 'Esto es NORMAL y DESEABLE. No es grasa. Es tu cuerpo recargando sus depósitos de energía.'
    });

    // Advertencia sobre horas de sueño
    if (resultados.datos.horasSueno < 7) {
        advertencias.push({
            tipo: 'warning',
            titulo: '😴 Sueño Insuficiente',
            mensaje: `Duermes ${resultados.datos.horasSueno} horas, menos del recomendado (7-9h).`,
            recomendacion: 'El sueño es crucial para recuperación muscular y control del apetito. Intenta aumentarlo.'
        });
    }

    let advertenciasHTML = '';
    advertencias.forEach(adv => {
        const colorClass = adv.tipo === 'danger' ? 'alert-danger' :
                          adv.tipo === 'warning' ? 'alert-warning' : 'alert-info';

        advertenciasHTML += `
            <div class="alert ${colorClass}">
                <h6>${adv.titulo}</h6>
                <p class="mb-1"><strong>${adv.mensaje}</strong></p>
                <p class="mb-0"><small>💡 ${adv.recomendacion}</small></p>
            </div>
        `;
    });

    return `
        <div class="card shadow-lg mb-4">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">⚠️ Advertencias y Recomendaciones</h4>
            </div>
            <div class="card-body">
                ${advertenciasHTML}
            </div>
        </div>
    `;
}

// ==================== FUNCIONES DE EXPORTACIÓN ====================
function exportarPDF() {
    alert('Funcionalidad de exportación a PDF en desarrollo. Por ahora puedes guardar esta página como PDF usando Ctrl+P.');
    window.print();
}

function guardarPlan() {
    // Esta función se conectará con el backend para guardar en la base de datos
    const resultados = sessionStorage.getItem('reverseDietResults');

    if (!resultados) {
        alert('No hay resultados para guardar');
        return;
    }

    alert('Funcionalidad de guardado en desarrollo. Los datos están almacenados temporalmente en tu sesión.');

    // TODO: Implementar guardado en base de datos
    // fetch('guardar_reverse_diet.php', {
    //     method: 'POST',
    //     body: resultados
    // })
}
