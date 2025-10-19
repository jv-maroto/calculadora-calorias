document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('calculadoraForm');
    const resultadosDiv = document.getElementById('resultados');
    const mensajeInicial = document.getElementById('mensaje-inicial');
    const objetivoSelect = document.getElementById('objetivo');
    const camposDeficit = document.getElementById('campos-deficit');
    const camposVolumen = document.getElementById('campos-volumen');

    let datosCalculados = null; // Para guardar/PDF

    // Cargar valores guardados
    cargarValoresGuardados();

    // Verificar estado inicial del campo tipo de cardio
    function verificarCampoCardio() {
        const diasCardio = document.getElementById('dias_cardio').value;
        const campoTipoCardio = document.getElementById('campo-tipo-cardio');
        if (diasCardio > 0) {
            campoTipoCardio.style.display = 'block';
        } else {
            campoTipoCardio.style.display = 'none';
        }
    }

    // Verificar al cargar la página
    verificarCampoCardio();

    // Mostrar/ocultar campos según objetivo
    objetivoSelect.addEventListener('change', function() {
        if (this.value === 'deficit') {
            camposDeficit.style.display = 'block';
            camposVolumen.style.display = 'none';
        } else if (this.value === 'volumen') {
            camposDeficit.style.display = 'none';
            camposVolumen.style.display = 'block';
        } else {
            camposDeficit.style.display = 'none';
            camposVolumen.style.display = 'none';
        }
    });

    // Mostrar campo ciclo menstrual solo para mujeres
    document.getElementById('sexo').addEventListener('change', function() {
        const campoCiclo = document.getElementById('campo-ciclo-menstrual');
        if (this.value === 'mujer') {
            campoCiclo.style.display = 'block';
        } else {
            campoCiclo.style.display = 'none';
        }
    });

    // Mostrar campo tipo de cardio solo si hace cardio
    document.getElementById('dias_cardio').addEventListener('input', function() {
        const campoTipoCardio = document.getElementById('campo-tipo-cardio');
        console.log('Días cardio cambiados a:', this.value);
        if (this.value > 0) {
            campoTipoCardio.style.display = 'block';
            console.log('Mostrando campo tipo de cardio');
        } else {
            campoTipoCardio.style.display = 'none';
            console.log('Ocultando campo tipo de cardio');
        }
    });

    // Validación en tiempo real
    const inputs = form.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateInput(this);
        });
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar campos obligatorios
        let isValid = true;
        const edad = parseInt(document.getElementById('edad').value);
        const sexo = document.getElementById('sexo').value;
        const peso = parseFloat(document.getElementById('peso').value);
        const altura = parseInt(document.getElementById('altura').value);
        const objetivo = document.getElementById('objetivo').value;
        const tipoTrabajo = document.getElementById('tipo_trabajo').value;

        if (!edad || !sexo || !peso || !altura || !objetivo || !tipoTrabajo) {
            alert('Por favor completa todos los campos obligatorios');
            return;
        }

        // Mostrar loading
        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Calculando...';
        btnSubmit.disabled = true;

        // Obtener todos los datos
        const diasEntreno = parseInt(document.getElementById('dias_entreno').value) || 0;
        const horasGym = parseFloat(document.getElementById('horas_gym').value) || 0;
        const diasCardio = parseInt(document.getElementById('dias_cardio').value) || 0;
        const horasCardio = parseFloat(document.getElementById('horas_cardio').value) || 0;
        const tipoCardio = document.getElementById('tipo_cardio').value;
        const horasTrabajo = parseFloat(document.getElementById('horas_trabajo').value) || 0;
        const horasSueno = parseFloat(document.getElementById('horas_sueno').value) || 0;

        // Datos específicos según objetivo
        let kgObjetivo = 0;
        let velocidad = '';
        let nivelGym = '';
        let mesesObjetivo = null;
        let semanasObjetivo = null;
        let preferencia = null;
        let perdidaSemanal = 0.5; // Por defecto 0.5 kg/semana

        if (objetivo === 'deficit') {
            kgObjetivo = parseFloat(document.getElementById('kg_perder').value) || 5;
            perdidaSemanal = parseFloat(document.getElementById('perdida_semanal').value) || 0.5;
            semanasObjetivo = parseInt(document.getElementById('semanas_objetivo_deficit').value) || null;
            preferencia = document.getElementById('preferencia_deficit').value;
            velocidad = preferencia; // Compatibilidad
        } else if (objetivo === 'volumen') {
            kgObjetivo = parseFloat(document.getElementById('kg_ganar').value) || 5;
            mesesObjetivo = parseInt(document.getElementById('meses_objetivo_volumen').value) || null;
            preferencia = document.getElementById('preferencia_volumen').value;
            velocidad = preferencia; // Compatibilidad
            nivelGym = document.getElementById('nivel_gym').value;
        }

        // Guardar valores
        guardarValores();

        // Obtener datos avanzados opcionales
        const anosEntrenando = document.getElementById('anos_entrenando').value;
        const somatotipo = document.getElementById('somatotipo').value;
        const historialDietas = document.getElementById('historial_dietas').value;
        const cicloRegular = document.getElementById('ciclo_regular').value;

        // CALCULAR TMB usando Mifflin-St Jeor (Fórmula más precisa)
        let tmb;
        if (sexo === 'hombre') {
            tmb = (10 * peso) + (6.25 * altura) - (5 * edad) + 5;
        } else {
            tmb = (10 * peso) + (6.25 * altura) - (5 * edad) - 161;
        }
        
        // Redondear TMB para evitar decimales excesivos
        tmb = Math.round(tmb);

        // AJUSTES METABÓLICOS BASADOS EN DATOS AVANZADOS
        let ajusteMetabolico = 1.0;

        // Ajuste por somatotipo
        if (somatotipo === 'ectomorfo') {
            ajusteMetabolico += 0.05; // +5% metabolismo más rápido
        } else if (somatotipo === 'endomorfo') {
            ajusteMetabolico -= 0.05; // -5% metabolismo más lento
        }

        // Ajuste por historial de dietas (adaptación metabólica)
        if (historialDietas === 'varias') {
            ajusteMetabolico -= 0.03; // -3% por varias dietas yoyo
        } else if (historialDietas === 'muchas') {
            ajusteMetabolico -= 0.07; // -7% por efecto yoyo severo
        }

        // Aplicar ajuste metabólico
        tmb = tmb * ajusteMetabolico;

        // CALCULAR FACTOR DE ACTIVIDAD (PAL - Physical Activity Level)
        // Basado en estándares científicos de nutricionistas
        let factorActividad = 1.2; // Base sedentario

        // ACTIVIDAD FÍSICA ESTRUCTURADA (Gym + Cardio)
        const horasGymSemanal = diasEntreno * horasGym;
        const horasCardioSemanal = diasCardio * horasCardio;
        const horasActividadTotal = horasGymSemanal + horasCardioSemanal;

        // Calcular intensidad del cardio
        let intensidadCardio = 0; // 0 = sin cardio, 1 = baja, 2 = moderada, 3 = alta
        if (diasCardio > 0 && tipoCardio) {
            switch(tipoCardio) {
                case 'caminar':
                    intensidadCardio = 1; // Baja intensidad
                    break;
                case 'caminar_rapido':
                case 'bicicleta':
                case 'eliptica':
                    intensidadCardio = 2; // Intensidad moderada
                    break;
                case 'correr_ligero':
                case 'natacion':
                    intensidadCardio = 2.5; // Intensidad moderada-alta
                    break;
                case 'correr_intenso':
                    intensidadCardio = 3; // Alta intensidad
                    break;
                default:
                    intensidadCardio = 2; // Por defecto moderada
            }
        }

        // Factores PAL ajustados por intensidad del cardio
        if (diasEntreno === 0 && diasCardio === 0) {
            factorActividad = 1.2; // Sedentario
        } else if (diasEntreno <= 2 && diasCardio <= 2) {
            // Ajustar según intensidad del cardio
            if (intensidadCardio <= 1) {
                factorActividad = 1.375; // Ligero
            } else if (intensidadCardio <= 2) {
                factorActividad = 1.45; // Ligero-Moderado
            } else {
                factorActividad = 1.55; // Moderado
            }
        } else if (diasEntreno <= 4 && diasCardio <= 3) {
            // Ajustar según intensidad del cardio
            if (intensidadCardio <= 1) {
                factorActividad = 1.45; // Ligero-Moderado
            } else if (intensidadCardio <= 2) {
                factorActividad = 1.55; // Moderado
            } else {
                factorActividad = 1.65; // Moderado-Alto
            }
        } else if (diasEntreno >= 5 || diasCardio >= 4) {
            // Ajustar según intensidad del cardio
            if (intensidadCardio <= 1) {
                factorActividad = 1.6; // Moderado-Alto (tu caso: caminar 7 días)
            } else if (intensidadCardio <= 2) {
                factorActividad = 1.725; // Activo
            } else {
                factorActividad = 1.8; // Activo-Alto
            }
        } else {
            factorActividad = 1.55; // Por defecto moderado
        }

        // AJUSTE POR INTENSIDAD Y DURACIÓN
        if (horasActividadTotal > 10 && intensidadCardio >= 2.5) {
            // Solo si hace mucho ejercicio de alta intensidad
            if (factorActividad >= 1.725) {
                factorActividad = 1.9; // Muy Activo
            }
        }

        // AJUSTE POR TIPO DE TRABAJO (mínimo impacto)
        if (tipoTrabajo === 'activo') {
            // Trabajo activo añade solo 0.05 al factor máximo
            factorActividad = Math.min(1.9, factorActividad + 0.05);
        }

        // AJUSTE POR SUEÑO (mínimo impacto)
        if (horasSueno < 6) {
            factorActividad = Math.max(1.2, factorActividad - 0.05);
        }

        // Límites PAL estándar (1.2 a 1.9)
        factorActividad = Math.max(1.2, Math.min(1.9, factorActividad));

        // TDEE (Gasto Total Diario de Energía)
        // NOTA: Factores PAL estándar basados en evidencia científica
        // - Factores: 1.2 (Sedentario) a 1.9 (Muy Activo)
        // - Basado en frecuencia de ejercicio, no horas acumuladas
        // - Ajustes mínimos por trabajo y sueño
        const tdee = tmb * factorActividad;

        // CALCULAR SEGÚN OBJETIVO CON VALIDACIÓN
        let planData = null;

        if (objetivo === 'volumen') {
            // Ajustar nivel de gym si se especificaron años de entrenamiento
            let nivelAjustado = nivelGym;
            if (anosEntrenando) {
                const anos = parseInt(anosEntrenando);
                if (anos < 1) nivelAjustado = 'principiante';
                else if (anos < 3) nivelAjustado = 'intermedio';
                else nivelAjustado = 'avanzado';
            }

            // Validar objetivo de volumen
            const validacion = validarObjetivoMusculo(
                kgObjetivo,
                mesesObjetivo || 12,
                nivelAjustado,
                sexo,
                diasEntreno,
                horasGym
            );

            // Mostrar advertencias ANTES de calcular
            if (!validacion.realista || validacion.advertencias.length > 0) {
                mostrarAdvertencias(validacion, 'volumen');

                // Si es crítico, preguntar si quiere continuar
                const criticas = validacion.advertencias.filter(a => a.tipo === 'critico');
                if (criticas.length > 0) {
                    const continuar = confirm(
                        '⚠️ TU OBJETIVO NO ES REALISTA\n\n' +
                        criticas.map(a => a.mensaje + '\n' + a.detalle).join('\n\n') +
                        '\n\n¿Quieres ver un plan alternativo realista?'
                    );

                    if (!continuar) {
                        btnSubmit.innerHTML = originalText;
                        btnSubmit.disabled = false;
                        return;
                    }

                    // Usar valores realistas si acepta
                    if (validacion.alternativas.length > 0) {
                        const mejor = validacion.alternativas.find(a => a.recomendado) || validacion.alternativas[0];
                        mesesObjetivo = mejor.duracion;
                        alert(`✅ Ajustado a: ${kgObjetivo}kg en ${mesesObjetivo} meses (realista)`);
                    }
                }
            }

            planData = calcularPlanVolumen(tdee, peso, kgObjetivo, velocidad, nivelGym, diasEntreno, horasGym, diasCardio, validacion);
        }

        else if (objetivo === 'deficit') {
            // Validar objetivo de déficit
            const validacion = validarObjetivoPerdida(
                kgObjetivo,
                semanasObjetivo || 12,
                peso
            );

            // Mostrar advertencias
            if (!validacion.realista || validacion.advertencias.length > 0) {
                mostrarAdvertencias(validacion, 'deficit');

                const criticas = validacion.advertencias.filter(a => a.tipo === 'critico');
                if (criticas.length > 0) {
                    const continuar = confirm(
                        '⚠️ TU OBJETIVO NO ES SALUDABLE\n\n' +
                        criticas.map(a => a.mensaje + '\n' + a.detalle).join('\n\n') +
                        '\n\n¿Quieres ver un plan alternativo saludable?'
                    );

                    if (!continuar) {
                        btnSubmit.innerHTML = originalText;
                        btnSubmit.disabled = false;
                        return;
                    }

                    // Usar valores realistas
                    if (validacion.alternativas.length > 0) {
                        const mejor = validacion.alternativas.find(a => a.recomendado) || validacion.alternativas[0];
                        semanasObjetivo = mejor.duracion;
                        alert(`✅ Ajustado a: ${kgObjetivo}kg en ${semanasObjetivo} semanas (saludable)`);
                    }
                }
            }

            planData = calcularPlanDeficit(tdee, peso, kgObjetivo, perdidaSemanal, diasCardio, horasCardio, validacion);
        }

        else {
            planData = { tipo: 'mantenimiento', calorias: Math.round(tdee) };
        }

        datosCalculados = {
            tmb: Math.round(tmb),
            tdee: Math.round(tdee),
            peso: peso,
            objetivo: objetivo,
            plan: planData
        };

        setTimeout(() => {
            mostrarResultados(datosCalculados);
            mensajeInicial.style.display = 'none';
            resultadosDiv.style.display = 'block';

            // Mostrar botones de guardar y PDF
            document.getElementById('btn-guardar').style.display = 'block';
            document.getElementById('btn-pdf').style.display = 'block';

            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;
        }, 500);
    });

    function calcularPlanDeficit(tdee, peso, kgPerder, perdidaSemanal, diasCardio, horasCardio, validacion) {
        // Déficit calórico basado en pérdida semanal deseada
        // 1 kg de grasa = ~7700 kcal, por lo que 0.5 kg/semana = 3850 kcal/semana = 550 kcal/día
        const deficitDiario = Math.round(perdidaSemanal * 7700 / 7);
        const kgPorSemana = perdidaSemanal;

        const caloriasBase = tdee - deficitDiario;

        // Calcular duración
        const semanasEstimadas = Math.ceil(kgPerder / kgPorSemana);
        const mesesEstimados = Math.ceil(semanasEstimadas / 4);

        // Fases progresivas (ajuste cada 4-6 semanas)
        const numFases = Math.min(Math.ceil(semanasEstimadas / 5), 4);
        const fases = [];

        for (let i = 0; i < numFases; i++) {
            const ajuste = i * 50; // Reducir 50 kcal cada fase
            fases.push({
                nombre: `Fase ${i + 1} (Semanas ${i * 5 + 1}-${Math.min((i + 1) * 5, semanasEstimadas)})`,
                calorias: Math.round(caloriasBase - ajuste)
            });
        }

        // Macros (proteína alta en déficit)
        const proteina = Math.round(peso * 2.3);
        const grasa = Math.round((caloriasBase * 0.27) / 9);
        const carbohidratos = Math.round((caloriasBase - (proteina * 4) - (grasa * 9)) / 4);

        // Info de cardio (solo informativa, no recomendar cambios)
        const horasCardioSemanal = diasCardio * horasCardio;
        const infoCardio = horasCardioSemanal > 0
            ? `Cardio actual integrado en el plan: ${horasCardioSemanal.toFixed(1)}h/semana`
            : 'Sin cardio actualmente. Puedes añadir 2-3 sesiones de 20-30min para acelerar la pérdida (opcional)';

        // Calcular refeeds programados
        const frecuenciaRefeed = velocidad === 'agresiva' ? 6 : 12; // Cada 6 o 12 días
        const caloriasRefeed = Math.round(tdee);

        const refeeds = [];
        for (let semana = 1; semana <= semanasEstimadas; semana++) {
            const diaEnPlan = semana * 7;
            if (diaEnPlan % frecuenciaRefeed === 0 || (semana === 1 && frecuenciaRefeed <= 7)) {
                const refeedDia = Math.ceil(diaEnPlan / 7) * 7;
                if (refeedDia <= semanasEstimadas * 7) {
                    refeeds.push({
                        semana: Math.ceil(refeedDia / 7),
                        calorias: caloriasRefeed
                    });
                }
            }
        }

        const refeedInfo = velocidad === 'agresiva'
            ? `Cada 6-7 días (${refeeds.length} refeeds programados) - RECOMENDADO`
            : `Cada 12-14 días (${refeeds.length} refeeds programados) - OPCIONAL`;

        return {
            tipo: 'deficit',
            duracion: { semanas: semanasEstimadas, meses: mesesEstimados },
            kgObjetivo: kgPerder,
            fases: fases,
            macros: { proteina, grasa, carbohidratos },
            infoCardio,
            refeeds,
            refeedInfo,
            deficitDiario,
            tdee: Math.round(tdee)
        };
    }

    function calcularPlanVolumen(tdee, peso, kgGanar, velocidad, nivelGym, diasEntreno, horasGym, diasCardio, validacion) {
        // Usar gananciaRealMensual de la validación si existe
        const kgPorMes = validacion ? validacion.gananciaRealMensual :
                         (nivelGym === 'principiante' ? 1.0 : nivelGym === 'intermedio' ? 0.6 : 0.3);

        // Superávit calórico según nivel y preferencia
        let superavitDiario;

        // Ajustar según nivel y preferencia
        if (velocidad === 'rapido') {
            superavitDiario = nivelGym === 'principiante' ? 500 : nivelGym === 'intermedio' ? 400 : 350;
        } else if (velocidad === 'limpio') {
            superavitDiario = nivelGym === 'principiante' ? 300 : nivelGym === 'intermedio' ? 250 : 200;
        } else { // optimo
            superavitDiario = nivelGym === 'principiante' ? 400 : nivelGym === 'intermedio' ? 300 : 250;
        }

        const caloriasBase = tdee + superavitDiario;

        // Calcular duración
        const mesesEstimados = Math.ceil(kgGanar / kgPorMes);
        const semanasEstimadas = mesesEstimados * 4;

        // Fases progresivas
        const numFases = Math.min(Math.ceil(mesesEstimados / 2), 6);
        const fases = [];

        for (let i = 0; i < numFases; i++) {
            const ajuste = Math.min(i * 50, 150); // Aumentar 50 kcal cada 8 semanas (máx +150)
            fases.push({
                nombre: `Fase ${i + 1} (Mes ${i * 2 + 1}-${Math.min((i + 1) * 2, mesesEstimados)})`,
                calorias: Math.round(caloriasBase + ajuste)
            });
        }

        // Macros para volumen (grasa más baja)
        const proteina = Math.round(peso * 2.0);
        const grasa = Math.round((caloriasBase * 0.23) / 9); // 23% grasa
        const carbohidratos = Math.round((caloriasBase - (proteina * 4) - (grasa * 9)) / 4);

        // Calcular mini-cuts programados
        const frecuenciaMiniCut = nivelGym === 'principiante' ? 16 :
                                  nivelGym === 'intermedio' ? 12 : 10;
        const caloriasMinicut = Math.round(tdee - 300);

        const miniCuts = [];
        let semanasAcumuladas = 0;
        while (semanasAcumuladas + frecuenciaMiniCut < semanasEstimadas) {
            semanasAcumuladas += frecuenciaMiniCut;
            const mesInicio = Math.ceil(semanasAcumuladas / 4);
            const semanaInicio = semanasAcumuladas + 1;
            const semanaFin = Math.min(semanasAcumuladas + 3, semanasEstimadas);

            miniCuts.push({
                mes: mesInicio,
                semanas: `${semanaInicio}-${semanaFin}`,
                calorias: caloriasMinicut
            });

            semanasAcumuladas += 3; // Duración del mini-cut
        }

        // Info de cardio (solo informativa, no recomendar cambios)
        const horasCardioSemanal = diasCardio * (parseFloat(document.getElementById('horas_cardio').value) || 0);
        const infoCardio = horasCardioSemanal > 0
            ? `Cardio actual integrado en el plan: ${horasCardioSemanal.toFixed(1)}h/semana`
            : 'Sin cardio actualmente. El cardio es opcional en volumen, puedes añadir 1-2 sesiones de 15-20min para salud cardiovascular';

        return {
            tipo: 'volumen',
            duracion: { meses: mesesEstimados, semanas: semanasEstimadas },
            kgObjetivo: kgGanar,
            nivelGym: nivelGym,
            fases: fases,
            macros: { proteina, grasa, carbohidratos },
            infoCardio,
            miniCuts,
            superavitDiario,
            tdee: Math.round(tdee)
        };
    }

    function mostrarResultados(data) {
        // Limpiar resultados previos
        resultadosDiv.innerHTML = '';

        // Card de resultados básicos
        let html = `
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">📊 Resultados Básicos</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-info">
                                <strong>TMB (Metabolismo Basal)</strong>
                                <h3 class="mb-0">${data.tmb} kcal/día</h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning">
                                <strong>TDEE (Gasto Total Diario)</strong>
                                <h3 class="mb-0">${data.tdee} kcal/día</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Mostrar plan según objetivo
        if (data.plan.tipo === 'deficit') {
            html += generarHTMLDeficit(data.plan, data.tdee, data.peso);
        } else if (data.plan.tipo === 'volumen') {
            html += generarHTMLVolumen(data.plan, data.tdee, data.peso);
        } else {
            html += `
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">⚖️ Plan de Mantenimiento</h4>
                    </div>
                    <div class="card-body">
                        <h5>Mantén ${data.plan.calorias} kcal/día para mantener tu peso actual</h5>
                    </div>
                </div>
            `;
        }

        // Recomendaciones nutricionales
        html += generarRecomendacionesNutricionales(data.peso, data.tdee);

        resultadosDiv.innerHTML = html;
    }

    function generarHTMLDeficit(plan, tdee, peso) {
        return `
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">📉 Tu Plan de Déficit Personalizado</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>🎯 Objetivo: Perder ${plan.kgObjetivo} kg</h5>
                        <h5>⏱️ Duración estimada: ${plan.duracion.semanas} semanas (${plan.duracion.meses} meses)</h5>
                        <p class="mb-0">Déficit calórico: ${plan.deficitDiario} kcal/día</p>
                    </div>

                    <h5 class="mt-4">📅 Fases del Plan</h5>
                    <div class="table-responsive">
                        <table class="table">
                            ${plan.fases.map(fase => `
                                <tr>
                                    <td><strong>${fase.nombre}</strong></td>
                                    <td class="text-end"><h5 class="mb-0">${fase.calorias} kcal/día</h5></td>
                                </tr>
                            `).join('')}
                        </table>
                    </div>

                    <h5 class="mt-4">🍽️ Distribución de Macronutrientes</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-danger">🥩 Proteína</h5>
                                    <h3>${plan.macros.proteina}g</h3>
                                    <small>${plan.macros.proteina * 4} kcal/día</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-warning">🥑 Grasa</h5>
                                    <h3>${plan.macros.grasa}g</h3>
                                    <small>${plan.macros.grasa * 9} kcal/día</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-primary">🍚 Carbohidratos</h5>
                                    <h3>${plan.macros.carbohidratos}g</h3>
                                    <small>${plan.macros.carbohidratos * 4} kcal/día</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">🔄 Refeeds Programados</h5>
                    <div class="alert alert-warning">
                        <strong>${plan.refeedInfo}</strong>
                        <p class="mb-0 mt-2">En estos días come ${plan.tdee} kcal (mantenimiento) para recuperar energía</p>
                    </div>
                    ${plan.refeeds.length > 0 ? `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Semana</th>
                                        <th>Calorías</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${plan.refeeds.slice(0, 8).map(refeed => `
                                        <tr>
                                            <td>Semana ${refeed.semana}</td>
                                            <td><strong>${refeed.calorias} kcal</strong></td>
                                        </tr>
                                    `).join('')}
                                    ${plan.refeeds.length > 8 ? '<tr><td colspan="2">... y ' + (plan.refeeds.length - 8) + ' refeeds más</td></tr>' : ''}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}

                    <h5 class="mt-4">📌 Información Adicional</h5>
                    <div class="alert alert-info">
                        <strong>🏃 Cardio:</strong> ${plan.infoCardio}
                    </div>
                    <div class="alert alert-secondary">
                        <strong>💪 Entrenamiento:</strong> Mantén intensidad alta y peso en barras para preservar músculo
                    </div>
                </div>
            </div>
        `;
    }

    function generarHTMLVolumen(plan, tdee, peso) {
        return `
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📈 Tu Plan de Volumen Personalizado</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>🎯 Objetivo: Ganar ${plan.kgObjetivo} kg de músculo</h5>
                        <h5>📊 Nivel: ${plan.nivelGym.charAt(0).toUpperCase() + plan.nivelGym.slice(1)}</h5>
                        <h5>⏱️ Duración estimada: ${plan.duracion.meses} meses (${plan.duracion.semanas} semanas)</h5>
                        <p class="mb-0">Superávit calórico: ${plan.superavitDiario} kcal/día</p>
                    </div>

                    <h5 class="mt-4">📅 Fases del Plan</h5>
                    <div class="table-responsive">
                        <table class="table">
                            ${plan.fases.map(fase => `
                                <tr>
                                    <td><strong>${fase.nombre}</strong></td>
                                    <td class="text-end"><h5 class="mb-0">${fase.calorias} kcal/día</h5></td>
                                </tr>
                            `).join('')}
                        </table>
                    </div>

                    <h5 class="mt-4">🍽️ Distribución de Macronutrientes</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-danger">🥩 Proteína</h5>
                                    <h3>${plan.macros.proteina}g</h3>
                                    <small>${plan.macros.proteina * 4} kcal/día</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-warning">🥑 Grasa</h5>
                                    <h3>${plan.macros.grasa}g</h3>
                                    <small>${plan.macros.grasa * 9} kcal/día</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-primary">🍚 Carbohidratos</h5>
                                    <h3>${plan.macros.carbohidratos}g</h3>
                                    <small>${plan.macros.carbohidratos * 4} kcal/día</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">✂️ Mini-cuts Programados</h5>
                    ${plan.miniCuts.length > 0 ? `
                        <div class="alert alert-warning">
                            <strong>Mini-cuts para controlar grasa acumulada</strong>
                            <p class="mb-0">Durante estas semanas, reduce a ${plan.miniCuts[0].calorias} kcal/día (déficit de 300 kcal)</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Semanas</th>
                                        <th>Calorías</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${plan.miniCuts.map(minicut => `
                                        <tr>
                                            <td>Mes ${minicut.mes}</td>
                                            <td>Semanas ${minicut.semanas}</td>
                                            <td><strong>${minicut.calorias} kcal/día</strong></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : `
                        <div class="alert alert-info">
                            <p class="mb-0">No hay mini-cuts programados para este plan (duración menor a ${plan.nivelGym === 'principiante' ? '16' : plan.nivelGym === 'intermedio' ? '12' : '10'} semanas)</p>
                        </div>
                    `}

                    <h5 class="mt-4">📌 Información Adicional</h5>
                    <div class="alert alert-info">
                        <strong>🏃 Cardio:</strong> ${plan.infoCardio}
                    </div>
                    <div class="alert alert-success">
                        <strong>💪 Entrenamiento:</strong> Mantén sobrecarga progresiva, incrementa pesos cada semana
                    </div>
                </div>
            </div>
        `;
    }

    function generarRecomendacionesNutricionales(peso, tdee) {
        return `
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">🥗 Recomendaciones Nutricionales</h4>
                </div>
                <div class="card-body">
                    <h5>💧 Hidratación</h5>
                    <p>Consumir al menos <strong>${Math.round(peso * 35)} ml</strong> de agua al día (${(peso * 35 / 1000).toFixed(1)} litros)</p>

                    <h5>🍽️ Distribución de Comidas</h5>
                    <ul>
                        <li><strong>Pre-entreno (1-2h antes):</strong> ${Math.round(tdee * 0.20)} kcal - Carbohidratos + proteína moderada</li>
                        <li><strong>Post-entreno (30-60min):</strong> ${Math.round(tdee * 0.25)} kcal - Proteína + Carbohidratos</li>
                        <li><strong>Resto del día:</strong> Distribuir en 2-3 comidas adicionales</li>
                    </ul>

                    <h5>🥩 Fuentes de Proteína</h5>
                    <p>Pollo, pavo, pescado, huevos, carne magra, proteína whey, yogur griego, legumbres</p>

                    <h5>🍚 Fuentes de Carbohidratos</h5>
                    <p>Arroz, avena, patata, boniato, pasta integral, quinoa, frutas, pan integral</p>

                    <h5>🥑 Fuentes de Grasa Saludable</h5>
                    <p>Aceite de oliva, aguacate, frutos secos, salmón, atún, yemas de huevo</p>

                    <h5>💊 Suplementación Básica</h5>
                    <ul>
                        <li>Proteína en polvo: solo si no alcanzas con comida real</li>
                        <li>Creatina monohidrato: 5g diarios</li>
                        <li>Vitamina D3: 2000-4000 UI diarias</li>
                        <li>Omega-3: 2-3g diarios (EPA+DHA)</li>
                    </ul>
                </div>
            </div>
        `;
    }

    function validateInput(input) {
        const value = parseFloat(input.value);
        const min = parseFloat(input.min);
        const max = parseFloat(input.max);

        if (input.required && (!input.value || value < min || value > max)) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        } else if (input.value) {
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
        } else {
            input.classList.remove('is-invalid', 'is-valid');
        }
    }

    // Funciones de Cookies
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    function guardarValores() {
        const datos = {
            edad: document.getElementById('edad').value,
            sexo: document.getElementById('sexo').value,
            peso: document.getElementById('peso').value,
            altura: document.getElementById('altura').value,
            dias_entreno: document.getElementById('dias_entreno').value,
            horas_gym: document.getElementById('horas_gym').value,
            dias_cardio: document.getElementById('dias_cardio').value,
            horas_cardio: document.getElementById('horas_cardio').value,
            tipo_trabajo: document.getElementById('tipo_trabajo').value,
            horas_trabajo: document.getElementById('horas_trabajo').value,
            horas_sueno: document.getElementById('horas_sueno').value,
            objetivo: document.getElementById('objetivo').value
        };

        localStorage.setItem('calculadoraCalorias', JSON.stringify(datos));
        setCookie('calculadoraCalorias', JSON.stringify(datos), 365);
    }

    function cargarValoresGuardados() {
        let datosGuardados = localStorage.getItem('calculadoraCalorias');

        if (!datosGuardados) {
            datosGuardados = getCookie('calculadoraCalorias');
            if (datosGuardados) {
                localStorage.setItem('calculadoraCalorias', datosGuardados);
            }
        }

        if (datosGuardados) {
            try {
                const datos = JSON.parse(datosGuardados);

                // Solo cargar valores si existen y son válidos
                if (datos.edad && datos.edad > 0) document.getElementById('edad').value = datos.edad;
                if (datos.sexo && (datos.sexo === 'hombre' || datos.sexo === 'mujer')) {
                    document.getElementById('sexo').value = datos.sexo;
                }
                if (datos.peso && datos.peso > 0) document.getElementById('peso').value = datos.peso;
                if (datos.altura && datos.altura > 0) document.getElementById('altura').value = datos.altura;
                if (datos.dias_entreno >= 0) document.getElementById('dias_entreno').value = datos.dias_entreno;
                if (datos.horas_gym >= 0) document.getElementById('horas_gym').value = datos.horas_gym;
                if (datos.dias_cardio >= 0) document.getElementById('dias_cardio').value = datos.dias_cardio;
                if (datos.horas_cardio >= 0) document.getElementById('horas_cardio').value = datos.horas_cardio;
                if (datos.tipo_trabajo && (datos.tipo_trabajo === 'sedentario' || datos.tipo_trabajo === 'activo')) {
                    document.getElementById('tipo_trabajo').value = datos.tipo_trabajo;
                }
                if (datos.horas_trabajo >= 0) document.getElementById('horas_trabajo').value = datos.horas_trabajo;
                if (datos.horas_sueno >= 0) document.getElementById('horas_sueno').value = datos.horas_sueno;

                // Cargar objetivo al final y disparar el evento change
                if (datos.objetivo && ['deficit', 'volumen', 'mantenimiento'].includes(datos.objetivo)) {
                    document.getElementById('objetivo').value = datos.objetivo;
                    // Dar tiempo para que el DOM se actualice antes de disparar el evento
                    setTimeout(() => {
                        objetivoSelect.dispatchEvent(new Event('change'));
                    }, 100);
                }
            } catch (e) {
                console.error('Error al cargar datos:', e);
                // Limpiar datos corruptos
                localStorage.removeItem('calculadoraCalorias');
                setCookie('calculadoraCalorias', '', -1);
            }
        }
    }

    // Validación de selects
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.value) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Botón guardar en base de datos
    let planGuardadoId = null;

    document.getElementById('btn-guardar').addEventListener('click', function() {
        if (!datosCalculados) {
            alert('Primero calcula tu plan');
            return;
        }

        const btnGuardar = this;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        // Preparar datos para enviar
        const datosParaGuardar = {
            formulario: {
                edad: document.getElementById('edad').value,
                sexo: document.getElementById('sexo').value,
                peso: document.getElementById('peso').value,
                altura: document.getElementById('altura').value,
                dias_entreno: document.getElementById('dias_entreno').value,
                horas_gym: document.getElementById('horas_gym').value,
                dias_cardio: document.getElementById('dias_cardio').value,
                horas_cardio: document.getElementById('horas_cardio').value,
                tipo_trabajo: document.getElementById('tipo_trabajo').value,
                horas_trabajo: document.getElementById('horas_trabajo').value,
                horas_sueno: document.getElementById('horas_sueno').value,
                objetivo: document.getElementById('objetivo').value,
                kg_objetivo: 0,
                velocidad: null,
                nivel_gym: null
            },
            resultados: datosCalculados
        };

        // Añadir datos específicos según objetivo
        const objetivo = document.getElementById('objetivo').value;
        if (objetivo === 'deficit') {
            datosParaGuardar.formulario.kg_objetivo = document.getElementById('kg_perder').value;
            datosParaGuardar.formulario.velocidad = document.getElementById('velocidad_deficit').value;
        } else if (objetivo === 'volumen') {
            datosParaGuardar.formulario.kg_objetivo = document.getElementById('kg_ganar').value;
            datosParaGuardar.formulario.velocidad = document.getElementById('velocidad_volumen').value;
            datosParaGuardar.formulario.nivel_gym = document.getElementById('nivel_gym').value;
        }

        fetch('guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosParaGuardar)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                planGuardadoId = data.id;
                alert('✅ Plan guardado correctamente en la base de datos (ID: ' + data.id + ')');
                btnGuardar.innerHTML = '✅ Plan Guardado';

                // Habilitar botón PDF
                document.getElementById('btn-pdf').disabled = false;
            } else {
                alert('❌ Error al guardar: ' + data.error);
                btnGuardar.innerHTML = '💾 Guardar Plan';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al guardar. Verifica que la base de datos esté configurada.');
            btnGuardar.innerHTML = '💾 Guardar Plan';
            btnGuardar.disabled = false;
        });
    });

    // Botón generar PDF
    document.getElementById('btn-pdf').addEventListener('click', function() {
        if (!planGuardadoId) {
            alert('Primero debes guardar el plan en la base de datos');
            return;
        }

        // Abrir PDF en nueva ventana
        window.open('generar_pdf.php?id=' + planGuardadoId, '_blank');
    });

    // Función para mostrar advertencias de validación
    function mostrarAdvertencias(validacion, tipo) {
        let html = '<div class="card shadow-lg mb-4" id="advertencias-card">';
        html += '<div class="card-header bg-warning text-dark"><h4 class="mb-0">⚠️ Análisis de tu Objetivo</h4></div>';
        html += '<div class="card-body">';

        // Advertencias
        validacion.advertencias.forEach(adv => {
            let colorClass = 'alert-info';
            let icon = '💡';

            if (adv.tipo === 'critico') {
                colorClass = 'alert-danger';
                icon = '🚫';
            } else if (adv.tipo === 'advertencia') {
                colorClass = 'alert-warning';
                icon = '⚠️';
            } else if (adv.tipo === 'exito') {
                colorClass = 'alert-success';
                icon = '✅';
            }

            html += `<div class="alert ${colorClass}">`;
            html += `<h5>${icon} ${adv.mensaje}</h5>`;
            html += `<p class="mb-0">${adv.detalle}</p>`;
            html += '</div>';
        });

        // Sugerencias
        if (validacion.sugerencias.length > 0) {
            html += '<h5 class="mt-3">💡 Sugerencias:</h5><ul>';
            validacion.sugerencias.forEach(sug => {
                html += `<li>${sug}</li>`;
            });
            html += '</ul>';
        }

        // Alternativas
        if (validacion.alternativas.length > 0) {
            html += '<h5 class="mt-3">🎯 Planes Alternativos:</h5>';
            validacion.alternativas.forEach(alt => {
                const badge = alt.recomendado ? '<span class="badge bg-success">RECOMENDADO</span>' : '';
                html += `<div class="card mb-2">`;
                html += `<div class="card-body">`;
                html += `<h6>${alt.titulo} ${badge}</h6>`;
                html += `<p class="mb-0">${alt.descripcion}</p>`;
                html += `</div></div>`;
            });
        }

        // Info adicional para volumen
        if (tipo === 'volumen' && validacion.pesoTotalGanado) {
            html += '<div class="alert alert-info mt-3">';
            html += `<h6>📊 Proyección Real:</h6>`;
            html += `<p class="mb-0">Músculo puro: <strong>${validacion.kgObjetivo}kg</strong><br>`;
            html += `Grasa inevitable: ~${validacion.grasaAproximada.toFixed(1)}kg<br>`;
            html += `<strong>Peso total a ganar: ~${validacion.pesoTotalGanado.toFixed(1)}kg</strong></p>`;
            html += '</div>';
        }

        html += '</div></div>';

        // Insertar antes de resultados
        if (document.getElementById('advertencias-card')) {
            document.getElementById('advertencias-card').remove();
        }
        resultadosDiv.insertAdjacentHTML('beforebegin', html);
    }
});
