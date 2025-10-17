document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('seguimientoForm');
    const resultadosDiv = document.getElementById('resultados');
    const mensajeInicial = document.getElementById('mensaje-inicial');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Obtener datos del formulario
        const objetivo = document.getElementById('objetivo').value;
        const caloriasActuales = parseInt(document.getElementById('calorias_actuales').value);
        const semanasEnPlan = parseInt(document.getElementById('semanas_en_plan').value);
        const pesoInicial = parseFloat(document.getElementById('peso_inicial').value);
        const pesoActual = parseFloat(document.getElementById('peso_actual').value);
        const sensacionEnergia = document.getElementById('sensacion_energia').value;
        const rendimientoGym = document.getElementById('rendimiento_gym').value;

        // CALCULAR CAMBIO DE PESO
        const cambioPeso = pesoActual - pesoInicial;
        const cambioPorSemana = cambioPeso / semanasEnPlan;

        // ANÁLISIS SEGÚN OBJETIVO
        let analisis;
        if (objetivo === 'deficit') {
            analisis = analizarDeficit(cambioPorSemana, caloriasActuales, sensacionEnergia, rendimientoGym, semanasEnPlan);
        } else {
            analisis = analizarVolumen(cambioPorSemana, caloriasActuales, sensacionEnergia, rendimientoGym, semanasEnPlan);
        }

        // Mostrar resultados
        mostrarResultados(analisis, cambioPeso, cambioPorSemana, semanasEnPlan);
        mensajeInicial.style.display = 'none';
        resultadosDiv.style.display = 'block';
    });

    function analizarDeficit(kgPorSemana, caloriasActuales, energia, rendimiento, semanas) {
        const resultado = {
            titulo: '',
            estado: '', // exito, advertencia, critico
            analisis: [],
            ajusteCalorias: 0,
            nuevasCalorias: caloriasActuales,
            explicacion: '',
            consejos: []
        };

        // RANGOS SALUDABLES: -0.4 a -0.7 kg/semana (moderado), -0.7 a -1.0 (agresivo)
        const perdidaIdealMin = -0.4;
        const perdidaIdealMax = -0.7;
        const perdidaAgresivaMax = -1.0;

        // CASO 1: Perdiendo demasiado rápido (más de 1kg/semana)
        if (kgPorSemana < -1.0) {
            resultado.estado = 'critico';
            resultado.titulo = '⚠️ ¡PERDIENDO DEMASIADO RÁPIDO!';
            resultado.analisis.push(`Estás perdiendo ${Math.abs(kgPorSemana).toFixed(2)}kg/semana - esto es EXCESIVO`);
            resultado.analisis.push('Riesgo alto de perder músculo y ralentizar metabolismo');

            // Calcular ajuste necesario
            const deficitExcesivo = (Math.abs(kgPorSemana) - 0.7) * 7700 / 7; // kcal/día excesivas
            resultado.ajusteCalorias = Math.round(deficitExcesivo);
            resultado.nuevasCalorias = caloriasActuales + resultado.ajusteCalorias;
            resultado.explicacion = `Necesitas SUBIR ${resultado.ajusteCalorias} kcal/día para ralentizar la pérdida a un ritmo saludable`;

            resultado.consejos.push('🍽️ Aumenta carbohidratos principalmente (arroz, avena, patata)');
            resultado.consejos.push('💪 Mantén proteína alta para preservar músculo');
            resultado.consejos.push('⚠️ Si continúas así perderás masa muscular');
        }

        // CASO 2: Perdiendo muy lento (menos de 0.3kg/semana)
        else if (kgPorSemana > -0.3) {
            resultado.estado = 'advertencia';
            resultado.titulo = '🐌 Progreso MUY LENTO';
            resultado.analisis.push(`Solo estás perdiendo ${Math.abs(kgPorSemana).toFixed(2)}kg/semana`);
            resultado.analisis.push('A este ritmo tardarás mucho en alcanzar tu objetivo');

            // Calcular déficit necesario para llegar a -0.5kg/semana (óptimo)
            const deficitFaltante = (0.5 - Math.abs(kgPorSemana)) * 7700 / 7;
            resultado.ajusteCalorias = -Math.round(deficitFaltante);
            resultado.nuevasCalorias = caloriasActuales + resultado.ajusteCalorias;
            resultado.explicacion = `Reduce ${Math.abs(resultado.ajusteCalorias)} kcal/día para acelerar a un ritmo saludable de -0.5kg/semana`;

            resultado.consejos.push('📉 Reduce carbohidratos y/o grasas (mantén proteína)');
            resultado.consejos.push('🏃 Considera añadir 2-3 sesiones de cardio de 20-30min');
            resultado.consejos.push('📊 Asegúrate de estar pesándote en las mismas condiciones (mañana, en ayunas)');
        }

        // CASO 3: NO perdiendo peso (estancado)
        else if (kgPorSemana >= 0) {
            resultado.estado = 'critico';
            resultado.titulo = '🚫 SIN PÉRDIDA DE PESO';
            resultado.analisis.push(`Has ${kgPorSemana > 0 ? 'GANADO' : 'mantenido'} peso en ${semanas} semanas`);
            resultado.analisis.push('Tu déficit calórico NO está funcionando');

            // Crear déficit de -500 kcal/día para perder ~0.5kg/semana
            resultado.ajusteCalorias = -400;
            resultado.nuevasCalorias = caloriasActuales - 400;
            resultado.explicacion = `Reduce 400 kcal/día inmediatamente para crear un déficit real`;

            resultado.consejos.push('⚠️ Posibles causas: estás comiendo más de lo que crees');
            resultado.consejos.push('📝 Usa una app para pesar y contar TODO lo que comes');
            resultado.consejos.push('🚫 No olvides contar aceites, salsas, snacks, bebidas');
            resultado.consejos.push('🏃 Aumenta actividad física (caminar 10,000 pasos/día)');
        }

        // CASO 4: Ritmo PERFECTO
        else if (kgPorSemana >= perdidaIdealMin && kgPorSemana <= perdidaIdealMax) {
            resultado.estado = 'exito';
            resultado.titulo = '✅ ¡RITMO PERFECTO!';
            resultado.analisis.push(`Estás perdiendo ${Math.abs(kgPorSemana).toFixed(2)}kg/semana - IDEAL`);
            resultado.analisis.push('Tu déficit está funcionando perfectamente');

            // Analizar bienestar
            if (energia === 'cansado' || rendimiento === 'peor') {
                resultado.ajusteCalorias = 100;
                resultado.nuevasCalorias = caloriasActuales + 100;
                resultado.explicacion = 'Tu progreso es bueno, pero pareces agotado. Sube 100 kcal para mejorar energía sin frenar mucho la pérdida';
                resultado.consejos.push('⚡ Añade 100 kcal en forma de carbohidratos pre-entreno');
                resultado.consejos.push('💤 Asegúrate de dormir 7-9 horas');
            } else {
                resultado.ajusteCalorias = 0;
                resultado.nuevasCalorias = caloriasActuales;
                resultado.explicacion = '¡NO CAMBIES NADA! Todo está funcionando perfectamente';
                resultado.consejos.push('✅ Mantén estas calorías exactas');
                resultado.consejos.push('📊 Sigue monitoreando cada semana');
                resultado.consejos.push('💪 Mantén intensidad alta en el gym');
            }
        }

        // CASO 5: Ritmo agresivo pero aceptable
        else if (kgPorSemana < perdidaIdealMax && kgPorSemana >= perdidaAgresivaMax) {
            resultado.estado = 'advertencia';
            resultado.titulo = '⚡ Ritmo AGRESIVO';
            resultado.analisis.push(`Perdiendo ${Math.abs(kgPorSemana).toFixed(2)}kg/semana - ritmo rápido`);
            resultado.analisis.push('Está bien temporalmente, pero monitorea tu músculo y energía');

            if (energia === 'cansado' || rendimiento === 'peor') {
                resultado.ajusteCalorias = 150;
                resultado.nuevasCalorias = caloriasActuales + 150;
                resultado.explicacion = 'Vas demasiado rápido Y te sientes mal. Sube 150 kcal para proteger músculo';
                resultado.consejos.push('⚠️ Riesgo de perder músculo - SUBE calorías YA');
            } else {
                resultado.ajusteCalorias = 50;
                resultado.nuevasCalorias = caloriasActuales + 50;
                resultado.explicacion = 'Puedes mantenerlo 2-3 semanas más, pero luego sube 50-100 kcal';
                resultado.consejos.push('👀 Monitorea cada semana');
                resultado.consejos.push('⚠️ No mantengas este ritmo más de 4 semanas');
            }
        }

        // Consejos adicionales según rendimiento
        if (rendimiento === 'peor') {
            resultado.consejos.push('🏋️ Tu rendimiento está bajando - señal de déficit excesivo o falta de descanso');
            resultado.consejos.push('📈 Considera un refeed (1 día a calorías de mantenimiento) esta semana');
        }

        return resultado;
    }

    function analizarVolumen(kgPorSemana, caloriasActuales, energia, rendimiento, semanas) {
        const resultado = {
            titulo: '',
            estado: '',
            analisis: [],
            ajusteCalorias: 0,
            nuevasCalorias: caloriasActuales,
            explicacion: '',
            consejos: []
        };

        // RANGOS IDEALES: +0.2 a +0.5 kg/semana (limpio/intermedio)
        const gananciaIdealMin = 0.2;
        const gananciaIdealMax = 0.5;
        const gananciaAgresivaMax = 1.0;

        // CASO 1: Ganando demasiado rápido (más de 1kg/semana)
        if (kgPorSemana > 1.0) {
            resultado.estado = 'critico';
            resultado.titulo = '🚫 ¡GANANDO DEMASIADA GRASA!';
            resultado.analisis.push(`Ganando ${kgPorSemana.toFixed(2)}kg/semana - EXCESIVO`);
            resultado.analisis.push('Estás acumulando mucha grasa, poco de eso es músculo');

            const excesoGanancia = (kgPorSemana - 0.5) * 7700 / 7;
            resultado.ajusteCalorias = -Math.round(excesoGanancia);
            resultado.nuevasCalorias = caloriasActuales + resultado.ajusteCalorias;
            resultado.explicacion = `REDUCE ${Math.abs(resultado.ajusteCalorias)} kcal/día para ganar limpio`;

            resultado.consejos.push('📉 Reduce carbohidratos y grasas proporcionalmente');
            resultado.consejos.push('⚠️ A este ritmo ganarás más grasa que músculo');
            resultado.consejos.push('✂️ Considera un mini-cut de 2-3 semanas pronto');
        }

        // CASO 2: Ganando muy lento o nada
        else if (kgPorSemana < 0.2) {
            resultado.estado = 'advertencia';
            resultado.titulo = '🐌 Ganancia MUY LENTA';
            resultado.analisis.push(`Solo ganando ${kgPorSemana.toFixed(2)}kg/semana`);
            resultado.analisis.push('Tu superávit es insuficiente para maximizar ganancia muscular');

            const faltanteSuperavit = (0.4 - kgPorSemana) * 7700 / 7;
            resultado.ajusteCalorias = Math.round(faltanteSuperavit);
            resultado.nuevasCalorias = caloriasActuales + resultado.ajusteCalorias;
            resultado.explicacion = `SUBE ${resultado.ajusteCalorias} kcal/día para optimizar ganancia`;

            resultado.consejos.push('📈 Aumenta principalmente carbohidratos (arroz, avena, pasta)');
            resultado.consejos.push('💪 Asegúrate de entrenar con intensidad suficiente');
            resultado.consejos.push('😴 Duerme 7-9 horas para maximizar recuperación');
        }

        // CASO 3: Perdiendo peso en volumen (ERROR)
        else if (kgPorSemana < 0) {
            resultado.estado = 'critico';
            resultado.titulo = '🚫 ¡ESTÁS PERDIENDO PESO!';
            resultado.analisis.push(`Has perdido ${Math.abs(kgPorSemana).toFixed(2)}kg/semana`);
            resultado.analisis.push('Imposible ganar músculo así - NO estás en superávit');

            resultado.ajusteCalorias = 500;
            resultado.nuevasCalorias = caloriasActuales + 500;
            resultado.explicacion = `SUBE 500 kcal/día INMEDIATAMENTE para entrar en superávit`;

            resultado.consejos.push('⚠️ Estás en DÉFICIT, no en volumen');
            resultado.consejos.push('🍽️ Añade comidas o aumenta porciones');
            resultado.consejos.push('📊 Pesa y cuenta tus calorías para asegurarte');
        }

        // CASO 4: Ritmo PERFECTO
        else if (kgPorSemana >= gananciaIdealMin && kgPorSemana <= gananciaIdealMax) {
            resultado.estado = 'exito';
            resultado.titulo = '✅ ¡VOLUMEN LIMPIO PERFECTO!';
            resultado.analisis.push(`Ganando ${kgPorSemana.toFixed(2)}kg/semana - ÓPTIMO`);
            resultado.analisis.push('Maximizando músculo y minimizando grasa');

            if (rendimiento === 'mejor') {
                resultado.ajusteCalorias = 0;
                resultado.nuevasCalorias = caloriasActuales;
                resultado.explicacion = '¡PERFECTO! Mantén estas calorías exactas';
                resultado.consejos.push('✅ NO cambies nada');
                resultado.consejos.push('💪 Sigue subiendo pesos progresivamente');
                resultado.consejos.push('📊 Monitorea cada semana');
            } else if (rendimiento === 'estancado') {
                resultado.ajusteCalorias = 100;
                resultado.nuevasCalorias = caloriasActuales + 100;
                resultado.explicacion = 'Tu peso sube bien, pero estás estancado. Sube 100 kcal para romper meseta';
                resultado.consejos.push('📈 Añade 100 kcal antes de entrenar');
                resultado.consejos.push('🏋️ Cambia rutina o aumenta volumen de entrenamiento');
            } else {
                resultado.ajusteCalorias = 0;
                resultado.nuevasCalorias = caloriasActuales;
                resultado.explicacion = 'Todo va bien, mantén el rumbo';
                resultado.consejos.push('✅ Sigue así');
            }
        }

        // CASO 5: Ritmo agresivo (0.5-1.0 kg/semana)
        else if (kgPorSemana > gananciaIdealMax && kgPorSemana <= gananciaAgresivaMax) {
            resultado.estado = 'advertencia';
            resultado.titulo = '⚡ Volumen AGRESIVO';
            resultado.analisis.push(`Ganando ${kgPorSemana.toFixed(2)}kg/semana - ritmo rápido`);
            resultado.analisis.push('Ganarás músculo, pero también bastante grasa');

            resultado.ajusteCalorias = -100;
            resultado.nuevasCalorias = caloriasActuales - 100;
            resultado.explicacion = 'Puedes bajar 100 kcal para ganar más limpio, o mantenerlo si prefieres volumen rápido';

            resultado.consejos.push('⚖️ Decisión tuya: más músculo rápido = más grasa');
            resultado.consejos.push('✂️ Planifica un mini-cut en 8-12 semanas');
            resultado.consejos.push('👀 Monitorea tu definición visual');
        }

        return resultado;
    }

    function mostrarResultados(analisis, cambioPesoTotal, cambioPorSemana, semanas) {
        let colorHeader = 'bg-success';
        if (analisis.estado === 'advertencia') colorHeader = 'bg-warning text-dark';
        if (analisis.estado === 'critico') colorHeader = 'bg-danger text-white';

        let html = `
            <div class="card shadow-lg mb-4">
                <div class="card-header ${colorHeader}">
                    <h4 class="mb-0">${analisis.titulo}</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>📊 Resumen de tu Progreso</h5>
                        <p class="mb-0">
                            En ${semanas} semana${semanas > 1 ? 's' : ''} has ${cambioPesoTotal >= 0 ? 'ganado' : 'perdido'}
                            <strong>${Math.abs(cambioPesoTotal).toFixed(1)}kg</strong>
                            (${cambioPorSemana >= 0 ? '+' : ''}${cambioPorSemana.toFixed(2)}kg/semana)
                        </p>
                    </div>

                    <h5 class="mt-4">🔍 Análisis Detallado</h5>
                    <ul>
                        ${analisis.analisis.map(a => `<li>${a}</li>`).join('')}
                    </ul>

                    <div class="alert alert-${analisis.estado === 'exito' ? 'success' : analisis.estado === 'advertencia' ? 'warning' : 'danger'} mt-4">
                        <h5>💡 Recomendación</h5>
                        <p class="mb-2"><strong>${analisis.explicacion}</strong></p>
                        ${analisis.ajusteCalorias !== 0 ? `
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <small class="text-muted">Calorías actuales</small>
                                            <h3>${analisis.nuevasCalorias - analisis.ajusteCalorias} kcal</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <small>NUEVAS Calorías</small>
                                            <h3>${analisis.nuevasCalorias} kcal</h3>
                                            <small>(${analisis.ajusteCalorias >= 0 ? '+' : ''}${analisis.ajusteCalorias} kcal)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : `
                            <div class="card bg-success text-white mt-3">
                                <div class="card-body text-center">
                                    <h4 class="mb-0">✅ Mantén ${analisis.nuevasCalorias} kcal/día</h4>
                                </div>
                            </div>
                        `}
                    </div>

                    <h5 class="mt-4">💡 Consejos Personalizados</h5>
                    <ul>
                        ${analisis.consejos.map(c => `<li>${c}</li>`).join('')}
                    </ul>

                    <div class="alert alert-secondary mt-4">
                        <h6>📝 Próximos Pasos</h6>
                        <ol class="mb-0">
                            <li>Ajusta tus calorías a <strong>${analisis.nuevasCalorias} kcal/día</strong></li>
                            <li>Mantén ese número durante <strong>7-14 días</strong></li>
                            <li>Vuelve a pesarte y analiza de nuevo</li>
                            <li>Repite el proceso hasta alcanzar tu objetivo</li>
                        </ol>
                    </div>
                </div>
            </div>
        `;

        resultadosDiv.innerHTML = html;
    }
});
