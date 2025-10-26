// Base de conocimiento: Límites realistas de ganancia muscular
const LIMITES_MUSCULO = {
    // Ganancia muscular REAL por mes (solo músculo, sin grasa)
    principiante: {
        hombre: { min: 1.0, max: 1.5, optimo: 1.2 }, // kg/mes
        mujer: { min: 0.5, max: 0.75, optimo: 0.6 }
    },
    intermedio: {
        hombre: { min: 0.5, max: 0.75, optimo: 0.6 },
        mujer: { min: 0.25, max: 0.4, optimo: 0.3 }
    },
    avanzado: {
        hombre: { min: 0.25, max: 0.4, optimo: 0.3 },
        mujer: { min: 0.1, max: 0.2, optimo: 0.15 }
    }
};

// Límites de pérdida de grasa saludable
const LIMITES_GRASA = {
    saludable: { min: 0.4, max: 0.7 }, // kg/semana
    agresivo: { min: 0.7, max: 1.0 },
    extremo: { min: 1.0, max: 1.2 } // No recomendado más de 4 semanas
};

// Ratio músculo:grasa en volumen
const RATIO_MUSCULO_GRASA = {
    principiante: 1.5, // Por cada 1kg músculo, 0.66kg grasa (3kg total = 2kg músculo + 1kg grasa aprox)
    intermedio: 2.0,   // Por cada 1kg músculo, 1kg grasa
    avanzado: 2.5      // Por cada 1kg músculo, 1.5kg grasa (más difícil ganar limpio)
};

// Requisitos mínimos de entrenamiento
const REQUISITOS_ENTRENO = {
    ganancia_muscular: {
        dias_min: 3,
        horas_min_semana: 3,
        mensaje: 'Para ganar músculo eficientemente necesitas al menos 3-4 días de entrenamiento con pesas'
    },
    perdida_grasa: {
        dias_min: 2,
        horas_min_semana: 2,
        mensaje: 'Para perder grasa es recomendable al menos 2-3 días de entrenamiento para preservar músculo'
    }
};

// Máximos realistas según experiencia
const MAXIMOS_REALES = {
    principiante: {
        ganancia_total_anual: 12, // kg músculo en el primer año
        mensaje: 'En tu primer año puedes ganar hasta 12kg de músculo (24kg de peso total aprox)'
    },
    intermedio: {
        ganancia_total_anual: 6,
        mensaje: 'Con 1-3 años de experiencia puedes ganar hasta 6kg de músculo al año'
    },
    avanzado: {
        ganancia_total_anual: 3,
        mensaje: 'Con más de 3 años de experiencia, ganar 3kg de músculo al año es un gran logro'
    }
};

function validarObjetivoMusculo(kgMusculo, mesesDisponibles, nivelGym, sexo, diasEntreno, horasGym) {
    const limites = LIMITES_MUSCULO[nivelGym][sexo];
    const maximos = MAXIMOS_REALES[nivelGym];
    const ratio = RATIO_MUSCULO_GRASA[nivelGym];

    // Calcular ganancia mensual que el usuario quiere
    const gananciaDeseadaMes = kgMusculo / mesesDisponibles;

    // Verificar entrenamiento suficiente
    const horasSemana = diasEntreno * horasGym;
    const entrenoSuficiente = diasEntreno >= REQUISITOS_ENTRENO.ganancia_muscular.dias_min &&
                               horasSemana >= REQUISITOS_ENTRENO.ganancia_muscular.horas_min_semana;

    // Peso total que ganará (músculo + grasa inevitable)
    const pesoTotalGanado = kgMusculo * ratio;

    const resultado = {
        realista: false,
        advertencias: [],
        sugerencias: [],
        alternativas: [],
        kgObjetivo: kgMusculo,
        gananciaRealMensual: limites.optimo,
        mesesRealesNecesarios: Math.ceil(kgMusculo / limites.optimo),
        pesoTotalGanado: pesoTotalGanado,
        grasaAproximada: pesoTotalGanado - kgMusculo
    };

    // Validación 1: Entrenamiento insuficiente
    if (!entrenoSuficiente) {
        resultado.advertencias.push({
            tipo: 'critico',
            mensaje: `⚠️ Tu volumen de entrenamiento es insuficiente para ganar músculo eficientemente`,
            detalle: REQUISITOS_ENTRENO.ganancia_muscular.mensaje
        });
        resultado.sugerencias.push('Aumenta a 4-5 días de entrenamiento con al menos 1 hora por sesión');
    }

    // Validación 2: Objetivo irreal (demasiado rápido)
    if (gananciaDeseadaMes > limites.max) {
        const diferencia = ((gananciaDeseadaMes / limites.max) - 1) * 100;
        resultado.advertencias.push({
            tipo: 'critico',
            mensaje: `🚫 ¡TE HAS FLIPADO! Quieres ganar ${kgMusculo}kg de músculo en ${mesesDisponibles} meses`,
            detalle: `Eso son ${gananciaDeseadaMes.toFixed(2)}kg/mes, pero tu límite real es ${limites.max}kg/mes (estás pidiendo ${diferencia.toFixed(0)}% más de lo biológicamente posible)`
        });

        // Sugerir alternativa realista
        const mesesReales = Math.ceil(kgMusculo / limites.optimo);
        resultado.alternativas.push({
            titulo: `Plan Realista: ${mesesReales} meses`,
            descripcion: `Ganar ${limites.optimo}kg músculo/mes = ${kgMusculo}kg en ${mesesReales} meses`,
            ganancia: limites.optimo,
            duracion: mesesReales
        });

        // Si el tiempo es muy ajustado, sugerir objetivo menor
        if (mesesDisponibles < mesesReales / 2) {
            const kgRealista = Math.floor(mesesDisponibles * limites.optimo);
            resultado.alternativas.push({
                titulo: `Plan Ajustado: ${kgRealista}kg en ${mesesDisponibles} meses`,
                descripcion: `Es más realista apuntar a ${kgRealista}kg de músculo en tu plazo de ${mesesDisponibles} meses`,
                ganancia: limites.optimo,
                duracion: mesesDisponibles,
                recomendado: true
            });
        }
    }
    // Validación 3: Muy lento (puede ir más rápido)
    else if (gananciaDeseadaMes < limites.min) {
        resultado.advertencias.push({
            tipo: 'info',
            mensaje: `💡 Vas demasiado conservador`,
            detalle: `Puedes ganar más rápido: ${limites.optimo}kg/mes es óptimo para tu nivel`
        });
        resultado.sugerencias.push(`Acelera el plan: podrías lograr ${kgMusculo}kg en ${Math.ceil(kgMusculo / limites.optimo)} meses`);
        resultado.realista = true;
    }
    // Validación 4: En el rango óptimo
    else if (gananciaDeseadaMes >= limites.min && gananciaDeseadaMes <= limites.optimo) {
        resultado.realista = true;
        resultado.advertencias.push({
            tipo: 'exito',
            mensaje: `✅ ¡Objetivo realista y bien planificado!`,
            detalle: `${gananciaDeseadaMes.toFixed(2)}kg/mes está en el rango óptimo para tu nivel`
        });
    }
    // Validación 5: Posible pero agresivo
    else if (gananciaDeseadaMes > limites.optimo && gananciaDeseadaMes <= limites.max) {
        resultado.realista = true;
        resultado.advertencias.push({
            tipo: 'advertencia',
            mensaje: `⚠️ Plan agresivo pero posible`,
            detalle: `${gananciaDeseadaMes.toFixed(2)}kg/mes es el límite superior. Ganarás más grasa de lo ideal`
        });
        resultado.sugerencias.push('Espera ganar más grasa de lo normal con este ritmo acelerado');
    }

    // Validación 6: Excede máximo anual
    const mesesEnAnio = Math.min(mesesDisponibles, 12);
    const gananciaAnualProyectada = gananciaDeseadaMes * mesesEnAnio;
    if (gananciaAnualProyectada > maximos.ganancia_total_anual) {
        resultado.advertencias.push({
            tipo: 'critico',
            mensaje: `🚫 Excedes el límite anual para tu nivel`,
            detalle: maximos.mensaje
        });
    }

    // Advertencia sobre peso total y grasa
    if (pesoTotalGanado > 15) {
        resultado.advertencias.push({
            tipo: 'info',
            mensaje: `📊 Ganarás aproximadamente ${pesoTotalGanado.toFixed(1)}kg de peso total`,
            detalle: `De los cuales ${kgMusculo}kg músculo y ${resultado.grasaAproximada.toFixed(1)}kg grasa. Considera mini-cuts periódicos`
        });
    }

    return resultado;
}

function validarObjetivoPerdida(kgPerder, semanasDisponibles, peso) {
    const perdidaSemanalDeseada = kgPerder / semanasDisponibles;

    const resultado = {
        realista: false,
        advertencias: [],
        sugerencias: [],
        alternativas: [],
        perdidaRealSemanal: LIMITES_GRASA.saludable.max,
        semanasRealesNecesarias: Math.ceil(kgPerder / LIMITES_GRASA.saludable.max)
    };

    // Verificar si es más del 1% del peso corporal por semana
    const porcentajePeso = (perdidaSemanalDeseada / peso) * 100;

    // Validación 1: Demasiado agresivo
    if (perdidaSemanalDeseada > LIMITES_GRASA.extremo.max) {
        resultado.advertencias.push({
            tipo: 'critico',
            mensaje: `🚫 ¡PLAN EXTREMADAMENTE PELIGROSO!`,
            detalle: `Quieres perder ${perdidaSemanalDeseada.toFixed(2)}kg/semana. Límite seguro: ${LIMITES_GRASA.agresivo.max}kg/semana`
        });

        const semanasReales = Math.ceil(kgPerder / LIMITES_GRASA.saludable.max);
        resultado.alternativas.push({
            titulo: `Plan Saludable: ${semanasReales} semanas`,
            descripcion: `Perder ${LIMITES_GRASA.saludable.max}kg/semana preservando músculo`,
            perdida: LIMITES_GRASA.saludable.max,
            duracion: semanasReales,
            recomendado: true
        });
    }
    // Validación 2: Muy agresivo
    else if (perdidaSemanalDeseada > LIMITES_GRASA.agresivo.max) {
        resultado.advertencias.push({
            tipo: 'advertencia',
            mensaje: `⚠️ Plan demasiado agresivo`,
            detalle: `${perdidaSemanalDeseada.toFixed(2)}kg/semana puede causar pérdida muscular significativa`
        });
        resultado.sugerencias.push('Reduce el ritmo para preservar músculo y evitar rebote');
        resultado.realista = true;
    }
    // Validación 3: Agresivo pero aceptable
    else if (perdidaSemanalDeseada >= LIMITES_GRASA.saludable.max && perdidaSemanalDeseada <= LIMITES_GRASA.agresivo.max) {
        resultado.realista = true;
        resultado.advertencias.push({
            tipo: 'advertencia',
            mensaje: `⚠️ Plan agresivo pero posible`,
            detalle: `${perdidaSemanalDeseada.toFixed(2)}kg/semana está en el límite. Incluye refeeds`
        });
    }
    // Validación 4: Óptimo
    else if (perdidaSemanalDeseada >= LIMITES_GRASA.saludable.min && perdidaSemanalDeseada < LIMITES_GRASA.saludable.max) {
        resultado.realista = true;
        resultado.advertencias.push({
            tipo: 'exito',
            mensaje: `✅ ¡Ritmo perfecto!`,
            detalle: `${perdidaSemanalDeseada.toFixed(2)}kg/semana es ideal para preservar músculo`
        });
    }
    // Validación 5: Demasiado lento
    else {
        resultado.realista = true;
        resultado.advertencias.push({
            tipo: 'info',
            mensaje: `💡 Vas muy conservador`,
            detalle: `Puedes acelerar a ${LIMITES_GRASA.saludable.max}kg/semana sin problemas`
        });
    }

    // Advertencia sobre % del peso corporal
    if (porcentajePeso > 1.5) {
        resultado.advertencias.push({
            tipo: 'critico',
            mensaje: `🚫 Excedes el 1.5% de tu peso corporal por semana`,
            detalle: `Alto riesgo de pérdida muscular y metabolismo lento`
        });
    }

    return resultado;
}

// Exportar para usar en script.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { validarObjetivoMusculo, validarObjetivoPerdida, LIMITES_MUSCULO, RATIO_MUSCULO_GRAFA };
}
