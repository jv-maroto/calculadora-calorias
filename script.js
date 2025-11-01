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

    // Mostrar campo ciclo menstrual y cadera solo para mujeres
    document.getElementById('sexo').addEventListener('change', function() {
        const campoCiclo = document.getElementById('campo-ciclo-menstrual');
        const campoCadera = document.getElementById('campo-circunferencia-cadera');
        if (this.value === 'mujer') {
            campoCiclo.style.display = 'block';
            campoCadera.style.display = 'block';
        } else {
            campoCiclo.style.display = 'none';
            campoCadera.style.display = 'none';
        }
    });

    // Mostrar campo tipo de cardio solo si hace cardio
    document.getElementById('dias_cardio').addEventListener('input', function() {
        const campoTipoCardio = document.getElementById('campo-tipo-cardio');
        if (this.value > 0) {
            campoTipoCardio.style.display = 'block';
        } else {
            campoTipoCardio.style.display = 'none';
        }
    });

    // Mostrar campo calorías si viene de volumen
    document.getElementById('vengo_de_volumen').addEventListener('change', function() {
        const campoCaloriasVolumen = document.getElementById('campo-calorias-volumen');
        if (this.value === 'si') {
            campoCaloriasVolumen.style.display = 'block';
        } else {
            campoCaloriasVolumen.style.display = 'none';
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
        let incluirMinicuts = false;
        let vengoDeVolumen = false;
        let caloriasVolumen = null;

        // Obtener datos avanzados para cálculos tempranos (antes de usarlos)
        const circunferenciaCintura = parseFloat(document.getElementById('circunferencia_cintura').value) || null;
        const circunferenciaCuello = parseFloat(document.getElementById('circunferencia_cuello').value) || null;
        const circunferenciaCadera = parseFloat(document.getElementById('circunferencia_cadera').value) || null;

        // CALCULAR % GRASA CORPORAL CON MÉTODO NAVY (si hay datos) - MOVER AQUÍ
        let porcentajeGrasa = null;
        if (circunferenciaCintura && circunferenciaCuello && altura) {
            if (sexo === 'hombre') {
                // Fórmula Navy para hombres
                const log10Abdomen = Math.log10(circunferenciaCintura - circunferenciaCuello);
                const log10Altura = Math.log10(altura);
                porcentajeGrasa = 86.010 * log10Abdomen - 70.041 * log10Altura + 36.76;
            } else if (circunferenciaCadera) {
                // Fórmula Navy para mujeres
                const log10Circunferencias = Math.log10(circunferenciaCintura + circunferenciaCadera - circunferenciaCuello);
                const log10Altura = Math.log10(altura);
                porcentajeGrasa = 163.205 * log10Circunferencias - 97.684 * log10Altura - 78.387;
            }

            // Limitar valores razonables (5% - 50%)
            if (porcentajeGrasa !== null) {
                porcentajeGrasa = Math.max(5, Math.min(50, porcentajeGrasa));
            }
        }

        if (objetivo === 'deficit') {
            kgObjetivo = parseFloat(document.getElementById('kg_perder').value) || 5;
            semanasObjetivo = parseInt(document.getElementById('semanas_objetivo_deficit').value) || null;
            preferencia = document.getElementById('preferencia_deficit').value;
            velocidad = preferencia; // Compatibilidad

            // Datos de volumen previo
            vengoDeVolumen = document.getElementById('vengo_de_volumen').value === 'si';
            caloriasVolumen = parseFloat(document.getElementById('calorias_volumen').value) || null;
        } else if (objetivo === 'volumen') {
            mesesObjetivo = parseInt(document.getElementById('meses_volumen').value) || 6;
            preferencia = document.getElementById('preferencia_volumen').value;

            // Si eligió "recomendado", calcular mejor opción basado en datos
            if (preferencia === 'recomendado' && porcentajeGrasa !== null) {
                const limites = sexo === 'hombre' ? {bajo: 12, medio: 17, alto: 22} : {bajo: 20, medio: 26, alto: 32};

                if (porcentajeGrasa >= limites.alto) {
                    // Grasa alta → Ultra limpio obligatorio
                    velocidad = 'ultra_limpio';
                    document.getElementById('recomendacion-volumen').innerHTML = `
                        <strong>🎯 Recomendación: Ultra Limpio</strong><br>
                        Con ${porcentajeGrasa.toFixed(1)}% de grasa, debes usar el superávit más bajo (8-10%) para minimizar acumulación de grasa.
                        Considera hacer un mini-cut de 2-3 semanas primero.
                    `;
                } else if (porcentajeGrasa >= limites.medio) {
                    // Grasa media-alta → Lean bulk
                    velocidad = 'limpio';
                    document.getElementById('recomendacion-volumen').innerHTML = `
                        <strong>🎯 Recomendación: Lean Bulk</strong><br>
                        Con ${porcentajeGrasa.toFixed(1)}% de grasa, el Lean Bulk (10-12%) es óptimo.
                        Ganancia sostenible sin acumular demasiada grasa.
                    `;
                } else if (porcentajeGrasa >= limites.bajo) {
                    // Grasa baja-media → Lean bulk o balanceado según experiencia
                    velocidad = nivelGym === 'principiante' ? 'limpio' : 'balanceado';
                    document.getElementById('recomendacion-volumen').innerHTML = `
                        <strong>🎯 Recomendación: ${nivelGym === 'principiante' ? 'Lean Bulk' : 'Balanceado'}</strong><br>
                        Con ${porcentajeGrasa.toFixed(1)}% de grasa estás en un buen rango.
                        ${nivelGym === 'principiante' ? 'Como principiante, Lean Bulk es óptimo.' : 'Puedes permitirte un superávit moderado.'}
                    `;
                } else {
                    // Grasa muy baja → Balanceado o agresivo
                    velocidad = nivelGym === 'avanzado' ? 'limpio' : 'balanceado';
                    document.getElementById('recomendacion-volumen').innerHTML = `
                        <strong>🎯 Recomendación: ${velocidad === 'limpio' ? 'Lean Bulk' : 'Balanceado'}</strong><br>
                        Con ${porcentajeGrasa.toFixed(1)}% de grasa estás muy definido. Excelente partición nutricional.
                        Puedes hacer un volumen más largo sin mini-cuts.
                    `;
                }

                document.getElementById('recomendacion-volumen').style.display = 'block';
            } else if (preferencia === 'recomendado') {
                // Sin datos de grasa, usar nivel gym
                velocidad = nivelGym === 'principiante' ? 'limpio' : 'balanceado';
                document.getElementById('recomendacion-volumen').innerHTML = `
                    <strong>🎯 Recomendación: ${velocidad === 'limpio' ? 'Lean Bulk' : 'Balanceado'}</strong><br>
                    Como ${nivelGym}, esta es la mejor opción. Para una recomendación más precisa, introduce tus medidas de cintura y cuello en datos avanzados.
                `;
                document.getElementById('recomendacion-volumen').style.display = 'block';
            } else {
                velocidad = preferencia; // Usuario eligió manualmente
                document.getElementById('recomendacion-volumen').style.display = 'none';
            }

            nivelGym = document.getElementById('nivel_gym').value;
            incluirMinicuts = document.getElementById('incluir_minicuts').value === 'si';
            kgObjetivo = 0; // Se calculará después según meses y nivel
        }

        // Guardar valores
        guardarValores();

        // Obtener datos avanzados opcionales (ya no necesitamos declarar circunferencias aquí)
        const anosEntrenando = document.getElementById('anos_entrenando').value;
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

        // porcentajeGrasa ya fue calculado arriba antes de su uso

        // AJUSTES METABÓLICOS BASADOS EN DATOS AVANZADOS
        let ajusteMetabolico = 1.0;

        // Ajuste por % de grasa corporal (si está disponible)
        if (porcentajeGrasa !== null) {
            // Personas con más grasa tienden a tener metabolismo ligeramente más bajo
            if (porcentajeGrasa > 30) {
                ajusteMetabolico -= 0.03; // -3%
            } else if (porcentajeGrasa < 12) {
                ajusteMetabolico += 0.03; // +3% (más masa magra)
            }
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

            planData = calcularPlanVolumen(tdee, peso, mesesObjetivo, velocidad, nivelAjustado, diasEntreno, horasGym, diasCardio, incluirMinicuts, porcentajeGrasa, sexo);
        }

        else if (objetivo === 'deficit') {
            // Pasar datos avanzados al cálculo de déficit
            planData = calcularPlanDeficit(
                tdee,
                peso,
                kgObjetivo,
                velocidad,
                diasCardio,
                horasCardio,
                semanasObjetivo,
                anosEntrenando,
                historialDietas,
                vengoDeVolumen,
                caloriasVolumen
            );
        }

        else {
            planData = { tipo: 'mantenimiento', calorias: Math.round(tdee) };
        }

        datosCalculados = {
            tmb: Math.round(tmb),
            tdee: Math.round(tdee),
            peso: peso,
            objetivo: objetivo,
            plan: planData,
            incluirMinicuts: incluirMinicuts
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

    function calcularPlanDeficit(tdee, peso, kgPerder, velocidad, diasCardio, horasCardio, semanasObjetivo, anosEntrenando, historialDietas, vengoDeVolumen, caloriasVolumen) {
        // AJUSTAR TDEE SI VIENE DE VOLUMEN
        let tdeeAjustado = tdee;
        let ajusteVolumen = 0;

        if (vengoDeVolumen && caloriasVolumen) {
            // Si viene de volumen, su TDEE real es más alto (metabolismo acelerado)
            // Usar las calorías de volumen como referencia más cercana a su TDEE real
            ajusteVolumen = caloriasVolumen - tdee;

            // Limitar ajuste a valores razonables (+10% a +30% del TDEE calculado)
            const ajusteMinimo = tdee * 0.10;
            const ajusteMaximo = tdee * 0.30;
            ajusteVolumen = Math.max(ajusteMinimo, Math.min(ajusteMaximo, ajusteVolumen));

            tdeeAjustado = tdee + ajusteVolumen;
        }

        // DEFINIR LÍMITES FIJOS POR VELOCIDAD (no cambiarlos)
        const LIMITES_DEFICIT = {
            'conservador': 400,   // Conservador: máx 400 kcal/día
            'saludable': 600,     // Saludable: máx 600 kcal/día
            'rapido': 700,        // Rápido: máx 700 kcal/día
            'agresivo': 1000      // Agresivo: máx 1000 kcal/día (límite absoluto)
        };

        // Ajustar límite de "rápido" según experiencia (solo para opción "rápido")
        let deficitMaxRapido = 700; // Base
        if (anosEntrenando && velocidad === 'rapido') {
            const anos = parseInt(anosEntrenando);
            if (anos >= 3) {
                deficitMaxRapido = 800; // Avanzados pueden 800 en "rápido"
            } else if (anos >= 1) {
                deficitMaxRapido = 750; // Intermedios pueden 750 en "rápido"
            }

            // Reducir si hay historial de dietas
            if (historialDietas === 'muchas') {
                deficitMaxRapido -= 100;
            } else if (historialDietas === 'varias') {
                deficitMaxRapido -= 50;
            }
        }

        // BONUS: Si viene de volumen, puede soportar déficit ligeramente mayor
        if (vengoDeVolumen && caloriasVolumen) {
            deficitMaxRapido += 50; // +50 kcal extra por metabolismo acelerado
            LIMITES_DEFICIT['saludable'] += 50;
            LIMITES_DEFICIT['rapido'] += 50;
        }

        // SI EL USUARIO ESPECIFICÓ SEMANAS, calcular déficit necesario
        let deficitDiario;
        let kgPorSemana;
        let semanasEstimadas;
        let deficitLimitado = false;
        let deficitMaximo; // Definir fuera del if para que esté disponible en análisis

        if (semanasObjetivo && semanasObjetivo > 0) {
            // Usuario especificó cuántas semanas quiere
            semanasEstimadas = semanasObjetivo;
            kgPorSemana = kgPerder / semanasEstimadas;

            // Calcular déficit necesario (1 kg = ~7700 kcal)
            deficitDiario = Math.round((kgPorSemana * 7700) / 7);
            const deficitOriginal = deficitDiario;

            // LÍMITE DE SEGURIDAD según velocidad elegida
            if (velocidad === 'rapido') {
                deficitMaximo = deficitMaxRapido; // Rápido: 700-800 según experiencia
            } else {
                deficitMaximo = LIMITES_DEFICIT[velocidad]; // Usar límites fijos
            }

            // Aplicar límite
            if (deficitDiario > deficitMaximo) {
                deficitDiario = deficitMaximo;
                deficitLimitado = true;
                // Recalcular kg/semana y semanas reales
                kgPorSemana = (deficitDiario * 7) / 7700;
                semanasEstimadas = Math.ceil(kgPerder / kgPorSemana);
            }

            // Mínimo absoluto (250 kcal/día)
            deficitDiario = Math.max(250, deficitDiario);
            kgPorSemana = (deficitDiario * 7) / 7700;
        } else {
            // Usuario NO especificó semanas, usar déficit según velocidad
            if (velocidad === 'conservador') {
                deficitDiario = 400;
                deficitMaximo = 400;
                kgPorSemana = 0.4;
            } else if (velocidad === 'saludable') {
                deficitDiario = 600;
                deficitMaximo = 600;
                kgPorSemana = 0.6;
            } else if (velocidad === 'rapido') {
                deficitDiario = deficitMaxRapido; // Usar déficit ajustado (700-800)
                deficitMaximo = deficitMaxRapido;
                kgPorSemana = (deficitDiario * 7) / 7700;
            } else if (velocidad === 'agresivo') {
                deficitDiario = 900;
                deficitMaximo = 1000;
                kgPorSemana = 0.9;
            }

            semanasEstimadas = Math.ceil(kgPerder / kgPorSemana);
        }

        const caloriasBase = tdeeAjustado - deficitDiario;
        const mesesEstimados = Math.round((semanasEstimadas / 4) * 10) / 10; // Redondear a 1 decimal

        // Redondear kg/semana a 1 decimal para evitar 0.909090909...
        kgPorSemana = Math.round(kgPorSemana * 10) / 10;

        // ANÁLISIS DEL OBJETIVO (validar si es sano mental y físicamente)
        const analisisObjetivo = {
            esSano: true,
            advertencias: [],
            tipoAdvertencia: null, // 'critico', 'advertencia', 'info', 'exito'
            deficitAjustado: false
        };

        // Verificar si eligió opción agresiva
        if (velocidad === 'agresivo' && deficitDiario >= 700) {
            analisisObjetivo.tipoAdvertencia = 'critico';
            analisisObjetivo.advertencias.push({
                tipo: 'critico',
                titulo: '🚫 Déficit agresivo - Bajo tu responsabilidad',
                mensaje: `Has elegido un déficit de ${deficitDiario} kcal/día (${kgPorSemana.toFixed(1)} kg/semana)`,
                detalle: `Este déficit es muy agresivo y puede causar: pérdida muscular significativa, fatiga extrema, irritabilidad, problemas hormonales, metabolismo adaptado y efecto rebote.`,
                recomendacion: `Solo usa esta opción si: 1) Tienes experiencia en dietas, 2) Entrenas con pesas regularmente, 3) Consumes proteína muy alta (2.5g/kg), 4) Monitorizas progreso semanalmente. Considera cambiar a "Rápido" (${deficitMaxRapido} kcal) para mejor balance.`
            });
        }
        // Verificar si el déficit fue ajustado automáticamente
        else if (deficitLimitado && semanasObjetivo) {
            const deficitDeseado = Math.round((kgPerder / semanasObjetivo) * 7700 / 7);
            analisisObjetivo.deficitAjustado = true;
            analisisObjetivo.tipoAdvertencia = 'advertencia';

            let limiteTexto = deficitMaximo === deficitMaxRapido ?
                `${deficitMaxRapido} kcal/día (ajustado por tu experiencia: ${anosEntrenando ? parseInt(anosEntrenando) : 0} años entrenando` +
                (historialDietas && historialDietas !== 'ninguna' ? ` y historial de dietas: ${historialDietas}` : '') + ')' :
                `${deficitMaximo} kcal/día (límite de opción "${velocidad}")`;

            analisisObjetivo.advertencias.push({
                tipo: 'advertencia',
                titulo: 'Plan ajustado automáticamente por seguridad',
                mensaje: `Querías: ${kgPerder} kg en ${semanasObjetivo} semanas (déficit de ${deficitDeseado} kcal/día)`,
                detalle: `Este déficit supera tu límite de ${limiteTexto}. Se ha ajustado automáticamente a ${deficitDiario} kcal/día para proteger tu salud física y mental.`,
                recomendacion: `Con ${deficitDiario} kcal/día perderás ${kgPorSemana.toFixed(1)} kg/semana. Necesitarás ${semanasEstimadas} semanas (${mesesEstimados} meses) en total. Si quieres más agresivo, selecciona la opción "Agresivo (bajo mi responsabilidad)".`
            });
        }

        // 1. Déficit alto pero dentro de límite (600-700 kcal)
        if (deficitDiario >= 600 && deficitDiario <= 700 && !analisisObjetivo.deficitAjustado) {
            analisisObjetivo.tipoAdvertencia = 'advertencia';
            analisisObjetivo.advertencias.push({
                tipo: 'advertencia',
                titulo: 'Déficit alto - Requiere disciplina',
                mensaje: `Tu déficit es de ${deficitDiario} kcal/día (${kgPorSemana.toFixed(1)} kg/semana)`,
                detalle: 'Este déficit es manejable pero requiere alta adherencia, buen descanso y entrenamiento adecuado.',
                recomendacion: 'Asegúrate de: dormir 7-8h, consumir proteína alta (2.2-2.5g/kg), entrenar con pesas para preservar músculo.'
            });
        }
        // 2. Déficit muy bajo (<300 kcal o <0.3 kg/semana)
        else if (deficitDiario < 300 || kgPorSemana < 0.3) {
            analisisObjetivo.tipoAdvertencia = 'info';
            analisisObjetivo.advertencias.push({
                tipo: 'info',
                titulo: '💡 Déficit muy conservador - Progreso lento',
                mensaje: `Tu déficit es de ${deficitDiario} kcal/día (${kgPorSemana} kg/semana)`,
                detalle: 'Progreso será muy lento. Puede ser frustrante mentalmente aunque es el más sostenible.',
                recomendacion: 'Si quieres acelerar, considera 400-500 kcal/día (0.4-0.5 kg/semana) para balance entre velocidad y adherencia.'
            });
        }
        // 3. Déficit óptimo (400-600 kcal)
        else if (deficitDiario >= 400 && deficitDiario < 600 && !analisisObjetivo.deficitAjustado) {
            analisisObjetivo.tipoAdvertencia = 'exito';
            analisisObjetivo.advertencias.push({
                tipo: 'exito',
                titulo: 'Déficit óptimo - Excelente balance',
                mensaje: `Tu déficit es de ${deficitDiario} kcal/día (${kgPorSemana.toFixed(1)} kg/semana)`,
                detalle: 'Este déficit ofrece el mejor balance entre velocidad de pérdida, preservación muscular y adherencia a largo plazo.',
                recomendacion: 'Mantén este déficit de forma consistente para mejores resultados sostenibles.'
            });
        }

        // 4. ADVERTENCIA: Déficit prolongado (adaptación metabólica)
        const mesesDeficit = mesesEstimados;
        if (mesesDeficit > 3) {
            const intensidadDeficit = deficitDiario >= 700 ? 'alto' : deficitDiario >= 500 ? 'moderado' : 'bajo';

            if (intensidadDeficit === 'alto' && mesesDeficit > 3) {
                analisisObjetivo.advertencias.push({
                    tipo: 'advertencia',
                    titulo: 'Déficit prolongado - Riesgo de adaptación metabólica',
                    mensaje: `Estarás ${mesesDeficit} meses en déficit de ${deficitDiario} kcal/día`,
                    detalle: `Déficits altos y prolongados (>3 meses) causan adaptación metabólica significativa: tu cuerpo reduce TMB, baja NEAT (movimiento inconsciente), reduce hormonas tiroideas, aumenta cortisol y resistencia a la leptina.`,
                    recomendacion: `Considera dividir en fases: ${Math.floor(mesesDeficit/2)} meses déficit → 2-4 semanas mantenimiento (reverse diet) → ${Math.ceil(mesesDeficit/2)} meses déficit. Esto minimiza adaptación metabólica y mejora adherencia.`
                });
            } else if (mesesDeficit > 6) {
                analisisObjetivo.advertencias.push({
                    tipo: 'info',
                    titulo: '💡 Déficit muy prolongado - Considerar diet breaks',
                    mensaje: `Estarás ${mesesDeficit} meses en déficit`,
                    detalle: `Déficits superiores a 6 meses, aunque moderados, benefician de "diet breaks" (1-2 semanas en mantenimiento cada 2-3 meses) para resetear hormonas y reducir fatiga mental.`,
                    recomendacion: `Planifica 1-2 semanas de mantenimiento cada 8-12 semanas para optimizar pérdida de grasa a largo plazo.`
                });
            }
        }

        // 5. ADVERTENCIA ESPECIAL: Agresivo + Prolongado = Muy peligroso
        if (velocidad === 'agresivo' && mesesDeficit > 2) {
            analisisObjetivo.advertencias.push({
                tipo: 'critico',
                titulo: '🚫 PELIGRO: Déficit agresivo prolongado',
                mensaje: `${deficitDiario} kcal/día durante ${mesesDeficit} meses es extremadamente peligroso`,
                detalle: `Déficits agresivos NO deben mantenerse más de 4-6 semanas. Riesgos: pérdida muscular masiva (hasta 50% del peso perdido), supresión metabólica severa (-500 kcal TMB), crash hormonal (testosterona, tiroides), fatiga crónica, depresión, efecto rebote garantizado.`,
                recomendacion: `URGENTE: Cambia a "Rápido" (700 kcal) o divide en ciclos cortos: 4 semanas agresivo → 2 semanas mantenimiento → repetir. O mejor aún, acepta perder más despacio con "Saludable".`
            });
        }

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
            kgPorSemana: kgPorSemana,
            fases: fases,
            macros: { proteina, grasa, carbohidratos },
            infoCardio,
            refeeds,
            refeedInfo,
            deficitDiario,
            tdee: Math.round(tdee),
            tdeeAjustado: Math.round(tdeeAjustado),
            vengoDeVolumen: vengoDeVolumen,
            ajusteVolumen: Math.round(ajusteVolumen),
            analisisObjetivo: analisisObjetivo
        };
    }

    function calcularPlanVolumen(tdee, peso, mesesVolumen, velocidad, nivelGym, diasEntreno, horasGym, diasCardio, incluirMinicuts, porcentajeGrasa, sexo) {
        // TASAS DE GANANCIA MUSCULAR 2024 (Schoenfeld, Helms, Nippard)
        let kgMusculoPorMesBase;
        if (nivelGym === 'principiante') {
            kgMusculoPorMesBase = 1.0; // ~2.2 lbs/mes (1-1.5% peso/mes primeros 6 meses)
        } else if (nivelGym === 'intermedio') {
            kgMusculoPorMesBase = 0.5; // ~1.1 lbs/mes (0.5-1% peso/mes)
        } else { // avanzado
            kgMusculoPorMesBase = 0.25; // ~0.55 lbs/mes (0.25-0.5% peso/mes)
        }

        // AJUSTAR según tipo de bulk
        // Principiantes: peor partición nutricional → menos beneficio de superávits altos
        let multiplicadorMusculo = 1.0;
        let ratioMusculoGrasa;
        let porcentajeSuperavit;

        if (velocidad === 'ultra_limpio') {
            if (nivelGym === 'principiante') {
                multiplicadorMusculo = 0.95;
                ratioMusculoGrasa = 0.75; // Peor partición que avanzados
                porcentajeSuperavit = 0.08; // 8%
            } else {
                multiplicadorMusculo = 0.85;
                ratioMusculoGrasa = 0.80; // Mejor partición
                porcentajeSuperavit = 0.09;
            }
        } else if (velocidad === 'limpio') {
            if (nivelGym === 'principiante') {
                multiplicadorMusculo = 1.0; // ÓPTIMO para principiantes
                ratioMusculoGrasa = 0.70; // 70% músculo, 30% grasa
                porcentajeSuperavit = 0.10; // 10% (~250 kcal)
            } else if (nivelGym === 'intermedio') {
                multiplicadorMusculo = 0.95;
                ratioMusculoGrasa = 0.75;
                porcentajeSuperavit = 0.11;
            } else {
                multiplicadorMusculo = 0.90;
                ratioMusculoGrasa = 0.78;
                porcentajeSuperavit = 0.12;
            }
        } else if (velocidad === 'balanceado') {
            if (nivelGym === 'principiante') {
                multiplicadorMusculo = 1.05;
                ratioMusculoGrasa = 0.65; // 65% músculo, 35% grasa
                porcentajeSuperavit = 0.15;
            } else {
                multiplicadorMusculo = 1.0;
                ratioMusculoGrasa = 0.70;
                porcentajeSuperavit = 0.15;
            }
        } else if (velocidad === 'agresivo') {
            if (nivelGym === 'principiante') {
                multiplicadorMusculo = 1.10; // Poco beneficio extra
                ratioMusculoGrasa = 0.55; // 55% músculo, 45% grasa
                porcentajeSuperavit = 0.20;
            } else {
                multiplicadorMusculo = 1.15;
                ratioMusculoGrasa = 0.60;
                porcentajeSuperavit = 0.20;
            }
        } else {
            multiplicadorMusculo = 1.0;
            ratioMusculoGrasa = 0.70;
            porcentajeSuperavit = 0.10;
        }

        // Aplicar multiplicador
        const kgMusculoPorMes = kgMusculoPorMesBase * multiplicadorMusculo;

        // SUPERÁVIT CALÓRICO basado en % del TDEE
        const superavitDiario = Math.round(tdee * porcentajeSuperavit);

        const caloriasBase = tdee + superavitDiario;

        // Calcular músculo esperado en el período
        const kgMusculoEsperado = kgMusculoPorMes * mesesVolumen;

        // Calcular peso total ganado (músculo / ratio)
        const kgTotalesEsperados = kgMusculoEsperado / ratioMusculoGrasa;
        const kgGrasaEsperada = kgTotalesEsperados - kgMusculoEsperado;

        // Duración
        const mesesEstimados = mesesVolumen;
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

        // ANÁLISIS DE % GRASA Y RECOMENDACIONES
        let recomendacionGrasa = null;
        let advertenciaGrasa = null;

        if (porcentajeGrasa !== null) {
            const limiteHombre = { bajo: 10, ideal: 15, alto: 20, muyAlto: 25 };
            const limiteMujer = { bajo: 18, ideal: 22, alto: 28, muyAlto: 32 };
            const limites = sexo === 'hombre' ? limiteHombre : limiteMujer;

            if (porcentajeGrasa >= limites.muyAlto) {
                advertenciaGrasa = {
                    nivel: 'critico',
                    mensaje: `⛔ ATENCIÓN: Tu % de grasa actual es ${porcentajeGrasa.toFixed(1)}%. NO es recomendable hacer volumen desde aquí.`,
                    detalle: `Con ${porcentajeGrasa.toFixed(1)}% de grasa corporal, ganarás principalmente grasa y muy poco músculo (partición nutricional muy pobre). Además, niveles altos de grasa corporal reducen sensibilidad a insulina y testosterona.`,
                    accion: `RECOMENDACIÓN FUERTE: Haz primero una fase de déficit hasta ${limites.ideal}% de grasa, LUEGO empieza tu volumen. Ganarás mucho más músculo y menos grasa.`
                };
            } else if (porcentajeGrasa >= limites.alto) {
                advertenciaGrasa = {
                    nivel: 'advertencia',
                    mensaje: `⚠️ Tu % de grasa actual es ${porcentajeGrasa.toFixed(1)}%. Puedes hacer volumen, pero con precauciones.`,
                    detalle: `Estás en el límite superior. La partición nutricional será subóptima y acumularás grasa más rápido.`,
                    accion: `RECOMENDACIÓN: Usa "Lean Bulk" (10-12% superávit) e incluye mini-cuts cada ${nivelGym === 'principiante' ? '12' : '10'} semanas para controlar la grasa.`
                };
            } else if (porcentajeGrasa <= limites.bajo) {
                recomendacionGrasa = {
                    nivel: 'excelente',
                    mensaje: `✅ ¡Excelente! Con ${porcentajeGrasa.toFixed(1)}% de grasa estás en el punto ÓPTIMO para volumen.`,
                    detalle: `Niveles bajos de grasa = mejor partición nutricional, más sensibilidad a insulina, mejor respuesta hormonal.`,
                    accion: `Puedes hacer un volumen más largo (${mesesVolumen > 6 ? mesesVolumen : 6}+ meses) sin preocuparte tanto por mini-cuts.`
                };
            } else if (porcentajeGrasa <= limites.ideal) {
                recomendacionGrasa = {
                    nivel: 'bueno',
                    mensaje: `✓ Con ${porcentajeGrasa.toFixed(1)}% de grasa estás en un buen rango para volumen.`,
                    detalle: `Partición nutricional óptima. Ganarás bien con cualquier tipo de superávit.`,
                    accion: `Mini-cuts opcionales cada ${nivelGym === 'principiante' ? '16' : '12'} semanas si quieres mantenerte definido.`
                };
            }
        }

        // Calcular mini-cuts programados
        const miniCuts = [];
        let frecuenciaMiniCutAjustada = nivelGym === 'principiante' ? 16 :
                                        nivelGym === 'intermedio' ? 12 : 10;

        // AJUSTAR FRECUENCIA según % grasa
        if (porcentajeGrasa !== null) {
            const limites = sexo === 'hombre' ?
                { alto: 20, muyAlto: 25 } :
                { alto: 28, muyAlto: 32 };

            if (porcentajeGrasa >= limites.alto) {
                // Si ya tienes grasa alta, hacer mini-cuts más frecuentes
                frecuenciaMiniCutAjustada = Math.max(8, frecuenciaMiniCutAjustada - 4);
            }
        }

        if (incluirMinicuts) {
            const caloriasMinicut = Math.round(tdee - 300);

            let semanasAcumuladas = 0;
            while (semanasAcumuladas + frecuenciaMiniCutAjustada < semanasEstimadas) {
                semanasAcumuladas += frecuenciaMiniCutAjustada;
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
        }

        // Info de cardio (solo informativa, no recomendar cambios)
        const horasCardioSemanal = diasCardio * (parseFloat(document.getElementById('horas_cardio').value) || 0);
        const infoCardio = horasCardioSemanal > 0
            ? `Cardio actual integrado en el plan: ${horasCardioSemanal.toFixed(1)}h/semana`
            : 'Sin cardio actualmente. El cardio es opcional en volumen, puedes añadir 1-2 sesiones de 15-20min para salud cardiovascular';

        // Nombre descriptivo del tipo de volumen
        let nombreTipoVolumen;
        if (velocidad === 'ultra_limpio') {
            nombreTipoVolumen = 'Ultra Limpio (8-10% superávit)';
        } else if (velocidad === 'limpio') {
            nombreTipoVolumen = 'Lean Bulk Óptimo ⭐ (10-12% superávit)';
        } else if (velocidad === 'balanceado') {
            nombreTipoVolumen = 'Balanceado (13-17% superávit)';
        } else if (velocidad === 'agresivo') {
            nombreTipoVolumen = 'Agresivo (20%+ superávit)';
        } else {
            nombreTipoVolumen = 'Personalizado';
        }

        // CALCULAR % GRASA FINAL ESPERADO
        let porcentajeGrasaFinal = null;
        let esGrasaFinalSaludable = null;
        let advertenciaGrasaFinal = null;
        let mensajeSinDatosGrasa = null;

        if (porcentajeGrasa !== null && peso) {
            // Calcular masa grasa actual
            const masaGrasaActual = peso * (porcentajeGrasa / 100);
            const masaMagraActual = peso - masaGrasaActual;

            // Calcular masa grasa final (actual + esperada)
            const masaGrasaFinal = masaGrasaActual + kgGrasaEsperada;
            const masaMagraFinal = masaMagraActual + kgMusculoEsperado; // Solo músculo, no agua/glucógeno
            const pesoFinal = peso + kgTotalesEsperados;

            porcentajeGrasaFinal = (masaGrasaFinal / pesoFinal) * 100;

            // Evaluar si es saludable
            const limites = sexo === 'hombre' ?
                { saludable: 18, alto: 22, muyAlto: 25 } :
                { saludable: 25, alto: 30, muyAlto: 35 };

            if (porcentajeGrasaFinal <= limites.saludable) {
                esGrasaFinalSaludable = 'excelente';
            } else if (porcentajeGrasaFinal <= limites.alto) {
                esGrasaFinalSaludable = 'bueno';
                advertenciaGrasaFinal = `Terminarás en el límite superior recomendado. Considera hacer un mini-cut en el mes ${Math.ceil(mesesEstimados / 2)}.`;
            } else if (porcentajeGrasaFinal <= limites.muyAlto) {
                esGrasaFinalSaludable = 'advertencia';
                advertenciaGrasaFinal = `⚠️ Terminarás con grasa alta (${porcentajeGrasaFinal.toFixed(1)}%). Recomendación: Haz un volumen más corto (${Math.ceil(mesesEstimados * 0.6)} meses) o incluye mini-cuts cada ${frecuenciaMiniCutAjustada < 12 ? frecuenciaMiniCutAjustada : 10} semanas.`;
            } else {
                esGrasaFinalSaludable = 'critico';
                advertenciaGrasaFinal = `⛔ PROBLEMA: Terminarás con ${porcentajeGrasaFinal.toFixed(1)}% de grasa, nivel no saludable. ACCIÓN: Reduce duración a ${Math.ceil(mesesEstimados * 0.5)} meses o usa "Ultra Limpio" con mini-cuts frecuentes.`;
            }
        } else {
            // Sin datos de grasa corporal
            mensajeSinDatosGrasa = `
                Para obtener una predicción precisa de tu % de grasa final, introduce tus medidas de
                <strong>circunferencia de cintura</strong> y <strong>circunferencia de cuello</strong>
                en la sección de Datos Avanzados. Esto te permitirá ver si terminarás en un rango saludable.
            `;
        }

        return {
            tipo: 'volumen',
            tipoVolumen: nombreTipoVolumen,
            velocidad: velocidad,
            porcentajeSuperavit: (porcentajeSuperavit * 100).toFixed(0) + '%',
            duracion: { meses: mesesEstimados, semanas: semanasEstimadas },
            kgObjetivo: kgMusculoEsperado, // kg de músculo esperado
            kgMusculoEsperado: kgMusculoEsperado,
            kgGrasaEsperada: kgGrasaEsperada,
            kgTotalesEsperados: kgTotalesEsperados,
            kgPorMes: kgMusculoPorMes,
            ratioMusculoGrasa: ratioMusculoGrasa,
            nivelGym: nivelGym,
            fases: fases,
            macros: { proteina, grasa, carbohidratos },
            infoCardio,
            miniCuts,
            superavitDiario,
            tdee: Math.round(tdee),
            porcentajeGrasa: porcentajeGrasa,
            porcentajeGrasaFinal: porcentajeGrasaFinal,
            esGrasaFinalSaludable: esGrasaFinalSaludable,
            advertenciaGrasaFinal: advertenciaGrasaFinal,
            mensajeSinDatosGrasa: mensajeSinDatosGrasa,
            recomendacionGrasa: recomendacionGrasa,
            advertenciaGrasa: advertenciaGrasa,
            frecuenciaMiniCutAjustada: frecuenciaMiniCutAjustada
        };
    }

    function mostrarResultados(data) {
        // Limpiar resultados previos
        resultadosDiv.innerHTML = '';

        // Card de resultados básicos - Estilo minimalista
        let html = `
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="bar-chart-3" style="color: #1a1a1a;"></i>
                    <div>
                        <h3>Resultados Básicos</h3>
                        <p>Tus métricas calculadas</p>
                    </div>
                </div>
                <div style="padding-top: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">TMB (Metabolismo Basal)</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">${data.tmb} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">TDEE (Gasto Total Diario)</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">${data.tdee} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">${
                                data.plan.tipo === 'deficit' ? 'Calorías Déficit' :
                                data.plan.tipo === 'volumen' ? 'Calorías Volumen' :
                                'Calorías Mantenimiento'
                            }</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">${
                                data.plan.tipo === 'deficit' ? (data.calorias_deficit || Math.round(data.tdee * 0.85)) :
                                data.plan.tipo === 'volumen' ? (data.plan.fases && data.plan.fases[0] ? data.plan.fases[0].calorias : Math.round(data.tdee * 1.10)) :
                                data.tdee
                            } kcal/día</div>
                            ${data.plan.tipo === 'volumen' && data.plan.superavitDiario ? `
                                <div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">+${data.plan.superavitDiario} kcal superávit (${data.plan.porcentajeSuperavit})</div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Mostrar plan según objetivo
        if (data.plan.tipo === 'deficit') {
            html += generarHTMLDeficit(data.plan, data.tdee, data.peso);
        } else if (data.plan.tipo === 'volumen') {
            html += generarHTMLVolumen(data.plan, data.tdee, data.peso, data.incluirMinicuts);
        } else {
            html += `
                <div class="v0-card">
                    <div style="padding: 1rem; border-bottom: 1px solid #e5e5e5; font-weight: 600; color: #1a1a1a;">
                        <h4>Plan de Mantenimiento</h4>
                    </div>
                    <div style="padding: 1rem;">
                        <h5>Mantén ${data.plan.calorias} kcal/día para mantener tu peso actual</div>
                    </div>
                </div>
            `;
        }

        // Recomendaciones nutricionales (ELIMINADO - no se usa)

        resultadosDiv.innerHTML = html;

        // Inicializar iconos de Lucide en los resultados
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
    }

    function generarHTMLDeficit(plan, tdee, peso) {
        // Generar HTML del análisis de objetivo
        let analisisHTML = '';
        if (plan.analisisObjetivo && plan.analisisObjetivo.advertencias.length > 0) {
            plan.analisisObjetivo.advertencias.forEach(adv => {
                analisisHTML += `
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <div style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem;">${adv.titulo}</div>
                        <div style="margin-bottom: 0.5rem;"><strong>${adv.mensaje}</strong></div>
                        <div style="margin-bottom: 0.5rem; color: #666;">${adv.detalle}</div>
                        <div style="color: #666;"><strong>Recomendación:</strong> ${adv.recomendacion}</div>
                    </div>
                `;
            });
        }

        return `
            ${analisisHTML ? `
                <div class="v0-card">
                    <div style="padding: 1rem; border-bottom: 1px solid #e5e5e5; font-weight: 600; color: #1a1a1a;">
                        <h4>Análisis de tu Objetivo</h4>
                    </div>
                    <div style="padding: 1rem;">
                        ${analisisHTML}
                    </div>
                </div>
            ` : ''}

            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="trending-down" style="color: #1a1a1a;"></i>
                    <div>
                        <h3>Plan de Déficit Personalizado</h3>
                        <p>Estrategia para perder grasa de forma óptima</p>
                    </div>
                </div>
                <div style="padding-top: 1rem;">
                    ${plan.vengoDeVolumen && plan.ajusteVolumen > 0 ? `
                        <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                            <div style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem;">Vienes de volumen - Metabolismo acelerado</div>
                            <div style="margin-bottom: 0.25rem;"><strong>TDEE base calculado:</strong> ${plan.tdee} kcal/día</div>
                            <div style="margin-bottom: 0.25rem;"><strong>Ajuste por volumen:</strong> +${plan.ajusteVolumen} kcal/día</div>
                            <div style="margin-bottom: 0.5rem;"><strong>TDEE ajustado real:</strong> ${plan.tdeeAjustado} kcal/día</div>
                            <div style="color: #666; font-size: 0.875rem;">Tu metabolismo está acelerado del volumen, puedes comer más y aún así perder grasa.</div>
                        </div>
                    ` : ''}

                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <div style="font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem;">Objetivo: Perder ${plan.kgObjetivo} kg</div>
                        <div style="margin-bottom: 0.25rem;">Duración estimada: ${plan.duracion.semanas} semanas (${plan.duracion.meses} meses)</div>
                        <h5 style="font-size: 1rem; font-weight: 600; color: #1a1a1a; margin: 0.5rem 0;">Pérdida esperada: ~${plan.kgPorSemana} kg/semana (aproximado)</h5>
                        <p>Déficit calórico: ${plan.deficitDiario} kcal/día</p>
                        <p><strong>Calorías diarias: ${Math.round(plan.tdeeAjustado - plan.deficitDiario)} kcal</strong></p>
                        <small style="color: #666; font-size: 0.875rem;">Nota: En déficit bajarás más al principio y menos al final. Todo es aproximado.</small>
                    </div>

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Fases del Plan</h5>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            ${plan.fases.map(fase => `
                                <tr>
                                    <td><strong>${fase.nombre}</strong></td>
                                    <td class="text-end"><h5>${fase.calorias} kcal/día</div></td>
                                </tr>
                            `).join('')}
                        </table>
                    </div>

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Distribución de Macronutrientes</h5>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Proteína</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.proteina}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.proteina * 4} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Grasa</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.grasa}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.grasa * 9} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Carbohidratos</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.carbohidratos}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.carbohidratos * 4} kcal/día</div>
                        </div>
                    </div>

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Refeeds Programados</h5>
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <strong style="color: #1a1a1a;">${plan.refeedInfo}</strong>
                        <p style="margin: 0.5rem 0 0 0; color: #666;">En estos días come ${plan.tdee} kcal (mantenimiento) para recuperar energía</p>
                        <small style="color: #999; font-size: 0.875rem;">En refeeds: Mantienes peso (0 kg de cambio esperado)</small>
                    </div>
                    ${plan.refeeds.length > 0 ? `
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
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

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Información Adicional</h5>
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <strong style="color: #1a1a1a;">Cardio:</strong> <span style="color: #666;">${plan.infoCardio}</span>
                    </div>
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <strong style="color: #1a1a1a;">Entrenamiento:</strong> <span style="color: #666;">Mantén intensidad alta y peso en barras para preservar músculo</span>
                    </div>
                </div>
            </div>
        `;
    }

    function generarHTMLVolumen(plan, tdee, peso, incluirMinicuts = false) {
        // Usar datos del plan ya calculados
        const kgMusculoPorMes = plan.kgPorMes;
        const ratioMusculoGrasa = plan.ratioMusculoGrasa;
        const kgGrasaPorMes = kgMusculoPorMes * ((1 - ratioMusculoGrasa) / ratioMusculoGrasa);

        return `
            <div class="v0-card">
                <div class="v0-card-header">
                    <i data-lucide="trending-up" style="color: #1a1a1a;"></i>
                    <div>
                        <h3>Tu Plan de Volumen Personalizado</h3>
                        <p>Estrategia para ganar músculo de forma óptima</p>
                    </div>
                </div>
                <div style="padding-top: 1rem;">

                    ${plan.advertenciaGrasa ? `
                        <div style="padding: 1.25rem; border: 2px solid ${plan.advertenciaGrasa.nivel === 'critico' ? '#ef4444' : '#f59e0b'}; background: ${plan.advertenciaGrasa.nivel === 'critico' ? '#fef2f2' : '#fffbeb'}; margin-bottom: 1.5rem;">
                            <div style="font-size: 1rem; font-weight: 700; color: ${plan.advertenciaGrasa.nivel === 'critico' ? '#dc2626' : '#d97706'}; margin-bottom: 0.75rem;">
                                ${plan.advertenciaGrasa.mensaje}
                            </div>
                            <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.75rem;">
                                ${plan.advertenciaGrasa.detalle}
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a; padding: 0.75rem; background: white; border-left: 3px solid ${plan.advertenciaGrasa.nivel === 'critico' ? '#dc2626' : '#d97706'};">
                                ${plan.advertenciaGrasa.accion}
                            </div>
                        </div>
                    ` : ''}

                    ${plan.recomendacionGrasa ? `
                        <div style="padding: 1.25rem; border: 2px solid ${plan.recomendacionGrasa.nivel === 'excelente' ? '#16a34a' : '#3b82f6'}; background: ${plan.recomendacionGrasa.nivel === 'excelente' ? '#f0fdf4' : '#eff6ff'}; margin-bottom: 1.5rem;">
                            <div style="font-size: 1rem; font-weight: 700; color: ${plan.recomendacionGrasa.nivel === 'excelente' ? '#16a34a' : '#2563eb'}; margin-bottom: 0.75rem;">
                                ${plan.recomendacionGrasa.mensaje}
                            </div>
                            <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.75rem;">
                                ${plan.recomendacionGrasa.detalle}
                            </div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a; padding: 0.75rem; background: white; border-left: 3px solid ${plan.recomendacionGrasa.nivel === 'excelente' ? '#16a34a' : '#2563eb'};">
                                ${plan.recomendacionGrasa.accion}
                            </div>
                        </div>
                    ` : ''}

                    ${plan.porcentajeGrasaFinal !== null ? `
                        <div style="padding: 1.25rem; border: 2px solid ${
                            plan.esGrasaFinalSaludable === 'excelente' ? '#16a34a' :
                            plan.esGrasaFinalSaludable === 'bueno' ? '#3b82f6' :
                            plan.esGrasaFinalSaludable === 'advertencia' ? '#f59e0b' : '#ef4444'
                        }; background: ${
                            plan.esGrasaFinalSaludable === 'excelente' ? '#f0fdf4' :
                            plan.esGrasaFinalSaludable === 'bueno' ? '#eff6ff' :
                            plan.esGrasaFinalSaludable === 'advertencia' ? '#fffbeb' : '#fef2f2'
                        }; margin-bottom: 1.5rem;">
                            <div style="font-size: 1rem; font-weight: 700; color: ${
                                plan.esGrasaFinalSaludable === 'excelente' ? '#16a34a' :
                                plan.esGrasaFinalSaludable === 'bueno' ? '#2563eb' :
                                plan.esGrasaFinalSaludable === 'advertencia' ? '#d97706' : '#dc2626'
                            }; margin-bottom: 0.75rem;">
                                📊 Predicción: Terminarás con ${plan.porcentajeGrasaFinal.toFixed(1)}% de grasa corporal
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; margin-bottom: 0.5rem;">
                                <span style="color: #666;">Grasa actual:</span>
                                <span style="font-weight: 600; color: #1a1a1a;">${plan.porcentajeGrasa.toFixed(1)}%</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; margin-bottom: 0.5rem;">
                                <span style="color: #666;">Grasa acumulada:</span>
                                <span style="font-weight: 600; color: #1a1a1a;">+${plan.kgGrasaEsperada.toFixed(1)} kg</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white;">
                                <span style="color: #666;">Grasa final predicha:</span>
                                <span style="font-weight: 700; font-size: 1.125rem; color: ${
                                    plan.esGrasaFinalSaludable === 'excelente' ? '#16a34a' :
                                    plan.esGrasaFinalSaludable === 'bueno' ? '#2563eb' :
                                    plan.esGrasaFinalSaludable === 'advertencia' ? '#d97706' : '#dc2626'
                                };">${plan.porcentajeGrasaFinal.toFixed(1)}%</span>
                            </div>
                            ${plan.advertenciaGrasaFinal ? `
                                <div style="font-size: 0.875rem; color: #666; margin-top: 0.75rem; padding: 0.75rem; background: white; border-left: 3px solid ${
                                    plan.esGrasaFinalSaludable === 'advertencia' ? '#d97706' : '#dc2626'
                                };">
                                    ${plan.advertenciaGrasaFinal}
                                </div>
                            ` : ''}
                        </div>
                    ` : plan.mensajeSinDatosGrasa ? `
                        <div style="padding: 1.25rem; border: 2px solid #3b82f6; background: #eff6ff; margin-bottom: 1.5rem;">
                            <div style="font-size: 1rem; font-weight: 700; color: #2563eb; margin-bottom: 0.75rem;">
                                📊 Predicción de % Grasa Final
                            </div>
                            <div style="font-size: 0.875rem; color: #666; line-height: 1.5;">
                                ${plan.mensajeSinDatosGrasa}
                            </div>
                        </div>
                    ` : ''}

                    <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">TIPO</div>
                                <div style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a;">${plan.tipoVolumen || 'Personalizado'}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">DURACIÓN</div>
                                <div style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a;">${plan.duracion.meses} meses</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">NIVEL</div>
                                <div style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a;">${plan.nivelGym.charAt(0).toUpperCase() + plan.nivelGym.slice(1)}</div>
                            </div>
                        </div>
                        <div style="border-top: 1px solid #e5e5e5; padding-top: 1rem; margin-top: 1rem;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.75rem;">Ganancia esperada total:</div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #666;">Músculo</span>
                                    <span style="font-weight: 700; color: #1a1a1a;">${plan.kgMusculoEsperado.toFixed(1)} kg <span style="font-size: 0.875rem; color: #666;">(${kgMusculoPorMes.toFixed(2)} kg/mes)</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #666;">Grasa</span>
                                    <span style="font-weight: 700; color: #1a1a1a;">${plan.kgGrasaEsperada.toFixed(1)} kg <span style="font-size: 0.875rem; color: #666;">(${kgGrasaPorMes.toFixed(2)} kg/mes)</span></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e5e5; padding-top: 0.5rem;">
                                    <span style="font-weight: 600; color: #1a1a1a;">Total</span>
                                    <span style="font-weight: 700; color: #1a1a1a;">${plan.kgTotalesEsperados.toFixed(1)} kg <span style="font-size: 0.875rem; color: #666;">(${(ratioMusculoGrasa * 100).toFixed(0)}% músculo)</span></span>
                                </div>
                            </div>
                        </div>
                        <div style="border-top: 1px solid #e5e5e5; padding-top: 1rem; margin-top: 1rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-weight: 600; color: #666;">Superávit calórico:</span>
                                <span style="font-weight: 700; color: #1a1a1a;">${plan.superavitDiario} kcal/día</span>
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; color: #666; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e5e5;">
                            Basado en tasas científicas 2024 para entrenamientos naturales. Los resultados individuales varían según genética, adherencia y calidad del entrenamiento.
                        </div>
                    </div>

                    <div style="font-size: 0.875rem; font-weight: 600; color: #1a1a1a; margin-bottom: 1rem;">Fases del Plan (con Mini-cuts Integrados)</div>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #666;">Fase</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #666;">Calorías</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #666;">Músculo</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #666;">Grasa</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #666;">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                            ${(() => {
                                let html = '';
                                let musculoAcumulado = 0;
                                let grasaAcumulada = 0;

                                // Calcular ganancia por mes
                                const musculoPorMes = kgMusculoPorMes;
                                const grasaPorMes = kgGrasaPorMes;

                                // Generar fases
                                plan.fases.forEach((fase, index) => {
                                    const duracionMeses = 2;
                                    const mesInicio = index * 2 + 1;
                                    const mesFin = Math.min((index + 1) * 2, plan.duracion.meses);
                                    const mesesReales = mesFin - mesInicio + 1;

                                    // Calcular ganancias en esta fase
                                    const musculoFase = musculoPorMes * mesesReales;
                                    const grasaFase = grasaPorMes * mesesReales;
                                    musculoAcumulado += musculoFase;
                                    grasaAcumulada += grasaFase;

                                    html += `
                                        <tr style="border-bottom: 1px solid #e5e5e5;">
                                            <td style="padding: 0.75rem;"><span style="font-weight: 600; color: #1a1a1a;">Fase ${index + 1}</span><br><span style="font-size: 0.75rem; color: #666;">Mes ${mesInicio}${mesFin > mesInicio ? '-' + mesFin : ''}</span></td>
                                            <td style="padding: 0.75rem;"><span style="font-weight: 600; color: #1a1a1a;">${fase.calorias} kcal</span></td>
                                            <td style="padding: 0.75rem;">
                                                <span style="font-weight: 600; color: #1a1a1a;">+${musculoFase.toFixed(1)} kg</span><br>
                                                <span style="font-size: 0.75rem; color: #666;">Total: ${musculoAcumulado.toFixed(1)} kg</span>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <span style="font-weight: 600; color: #1a1a1a;">+${grasaFase.toFixed(1)} kg</span><br>
                                                <span style="font-size: 0.75rem; color: #666;">Total: ${grasaAcumulada.toFixed(1)} kg</span>
                                            </td>
                                            <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.75rem; background: #1a1a1a; color: white; font-size: 0.75rem; font-weight: 600;">Superávit</span></td>
                                        </tr>
                                    `;

                                    // Verificar mini-cut después de esta fase (solo si hay mini-cuts)
                                    if (plan.miniCuts.length > 0) {
                                        const siguienteMes = mesFin + 1;
                                        const miniCut = plan.miniCuts.find(mc => mc.mes === siguienteMes);

                                        if (miniCut) {
                                            // Pérdida en mini-cut (mayormente grasa)
                                            const perdidaGrasa = 0.7; // ~70% grasa
                                            const perdidaMusculo = 0.2; // ~20% músculo
                                            musculoAcumulado -= perdidaMusculo;
                                            grasaAcumulada -= perdidaGrasa;

                                            html += `
                                                <tr style="border-bottom: 1px solid #e5e5e5; background: #fafafa;">
                                                    <td style="padding: 0.75rem;"><span style="font-weight: 600; color: #1a1a1a;">Mini-cut</span><br><span style="font-size: 0.75rem; color: #666;">Mes ${miniCut.mes}</span></td>
                                                    <td style="padding: 0.75rem;"><span style="font-weight: 600; color: #1a1a1a;">${miniCut.calorias} kcal</span></td>
                                                    <td style="padding: 0.75rem;">
                                                        <span style="font-weight: 600; color: #1a1a1a;">-${perdidaMusculo.toFixed(1)} kg</span><br>
                                                        <span style="font-size: 0.75rem; color: #666;">Total: ${musculoAcumulado.toFixed(1)} kg</span>
                                                    </td>
                                                    <td style="padding: 0.75rem;">
                                                        <span style="font-weight: 600; color: #16a34a;">-${perdidaGrasa.toFixed(1)} kg</span><br>
                                                        <span style="font-size: 0.75rem; color: #666;">Total: ${grasaAcumulada.toFixed(1)} kg</span>
                                                    </td>
                                                    <td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.75rem; background: white; border: 1px solid #e5e5e5; color: #1a1a1a; font-size: 0.75rem; font-weight: 600;">Déficit</span></td>
                                                </tr>
                                            `;
                                        }
                                    }
                                });

                                // Resumen final
                                const pesoTotal = musculoAcumulado + grasaAcumulada;
                                html += `
                                    <tr style="background: white; border-top: 1px solid #e5e5e5;">
                                        <td colspan="2" style="padding: 0.75rem;"><strong style="color: #1a1a1a;">Resultado Final</strong></td>
                                        <td style="padding: 0.75rem;">
                                            <strong style="color: #1a1a1a;">+${musculoAcumulado.toFixed(1)} kg</strong><br>
                                            <span style="font-size: 0.75rem; color: #666;">músculo</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <strong style="color: #1a1a1a;">+${grasaAcumulada.toFixed(1)} kg</strong><br>
                                            <span style="font-size: 0.75rem; color: #666;">grasa</span>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <strong style="color: #1a1a1a;">+${pesoTotal.toFixed(1)} kg</strong><br>
                                            <span style="font-size: 0.75rem; color: #666;">total</span>
                                        </td>
                                    </tr>
                                `;

                                return html;
                            })()}
                            </tbody>
                        </table>
                    </div>

                    ${plan.miniCuts.length === 0 ? `
                        <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                            <strong>Sin mini-cuts programados</strong>
                            ${(() => {
                                if (incluirMinicuts) {
                                    // Usuario quería mini-cuts pero no hay porque es muy corto
                                    const frecuenciaMiniCut = plan.nivelGym === 'principiante' ? 16 :
                                                              plan.nivelGym === 'intermedio' ? 12 : 10;
                                    return `<p>Querías incluir mini-cuts, pero tu plan es de solo ${plan.duracion.semanas} semanas. Los mini-cuts se programan cada ${frecuenciaMiniCut} semanas para ${plan.nivelGym}s. <strong>Necesitas al menos ${frecuenciaMiniCut + 3} semanas</strong> para que tenga sentido hacer un mini-cut. Con duraciones cortas, es mejor hacer volumen continuo.</p>`;
                                } else {
                                    // Usuario eligió NO incluir mini-cuts
                                    return `<p>Has elegido un volumen continuo sin mini-cuts. Ten en cuenta que acumularás más grasa, pero el proceso será más simple y directo.</p>`;
                                }
                            })()}
                        </div>
                    ` : ''}

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Distribución de Macronutrientes</h5>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Proteína</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.proteina}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.proteina * 4} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Grasa</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.grasa}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.grasa * 9} kcal/día</div>
                        </div>
                        <div style="padding: 1.5rem; border: 1px solid #e5e5e5; background: white; text-align: center;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #666; margin-bottom: 0.5rem;">Carbohidratos</div>
                            <div style="font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem;">${plan.macros.carbohidratos}g</div>
                            <div style="font-size: 0.75rem; color: #999;">${plan.macros.carbohidratos * 4} kcal/día</div>
                        </div>
                    </div>

                    <h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Información Adicional</h5>
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <strong style="color: #1a1a1a;">Cardio:</strong> <span style="color: #666;">${plan.infoCardio}</span>
                    </div>
                    <div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">
                        <strong style="color: #1a1a1a;">Entrenamiento:</strong> <span style="color: #666;">Mantén sobrecarga progresiva, incrementa pesos cada semana</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Función eliminada - Recomendaciones nutricionales no se usan

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
                nombre: document.getElementById('usuario_nombre').value,
                apellidos: document.getElementById('usuario_apellidos').value,
                edad: document.getElementById('edad').value,
                sexo: document.getElementById('sexo').value,
                peso: document.getElementById('peso').value,
                altura: document.getElementById('altura').value,
                anos_entrenando: document.getElementById('anos_entrenando') ? document.getElementById('anos_entrenando').value : null,
                historial_dietas: document.getElementById('historial_dietas') ? document.getElementById('historial_dietas').value : null,
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
            const kgPerder = document.getElementById('kg_perder');
            const preferencia = document.getElementById('preferencia_deficit');
            datosParaGuardar.formulario.kg_objetivo = kgPerder ? kgPerder.value : null;
            datosParaGuardar.formulario.velocidad = preferencia ? preferencia.value : null;
        } else if (objetivo === 'volumen') {
            const mesesVolumen = document.getElementById('meses_volumen');
            const preferencia = document.getElementById('preferencia_volumen');
            const nivelGym = document.getElementById('nivel_gym');
            datosParaGuardar.formulario.kg_objetivo = mesesVolumen ? mesesVolumen.value : null;
            datosParaGuardar.formulario.velocidad = preferencia ? preferencia.value : null;
            datosParaGuardar.formulario.nivel_gym = nivelGym ? nivelGym.value : null;
        }

        // Debug: mostrar qué se va a guardar
        console.log('Guardando plan con objetivo:', datosParaGuardar.formulario.objetivo);
        console.log('Datos completos:', datosParaGuardar);

        fetch('guardar_plan.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosParaGuardar)
        })
        .then(response => {
            // Primero verificar si la respuesta es OK
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            // Obtener el texto de la respuesta para debugging
            return response.text();
        })
        .then(text => {
            console.log('Respuesta del servidor:', text);
            // Intentar parsear como JSON
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    planGuardadoId = data.id;
                    alert('Plan guardado correctamente en la base de datos (ID: ' + data.id + ')');
                    btnGuardar.innerHTML = 'Plan Guardado';

                    // Habilitar y mostrar botón PDF
                    const btnPdf = document.getElementById('btn-pdf');
                    btnPdf.style.display = 'block';
                    btnPdf.disabled = false;
                } else {
                    alert('Error al guardar: ' + data.error);
                    btnGuardar.innerHTML = '💾 Guardar Plan';
                    btnGuardar.disabled = false;
                }
            } catch (e) {
                console.error('Error parseando JSON:', e);
                console.error('Respuesta recibida:', text);
                alert('Error: La respuesta del servidor no es válida. Revisa la consola para más detalles.');
                btnGuardar.innerHTML = '💾 Guardar Plan';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar: ' + error.message);
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

        // Descargar PDF directamente
        window.open('descargar_pdf.php?id=' + planGuardadoId, '_blank');
    });

    // Función para mostrar advertencias de validación
    function mostrarAdvertencias(validacion, tipo) {
        let html = '<div class="v0-card" id="advertencias-card">';
        html += '<div style="padding: 1rem; border-bottom: 1px solid #e5e5e5; font-weight: 600; color: #1a1a1a;"><h4>Análisis de tu Objetivo</h4></div>';
        html += '<div style="padding: 1rem;">';

        // Advertencias
        validacion.advertencias.forEach(adv => {
            let colorClass = 'alert-info';
            let icon = '💡';

            if (adv.tipo === 'critico') {
                colorClass = 'alert-danger';
                icon = '🚫';
            } else if (adv.tipo === 'advertencia') {
                colorClass = 'alert-warning';
                icon = '⚠';
            } else if (adv.tipo === 'exito') {
                colorClass = 'alert-success';
                icon = '✓';
            }

            html += `<div class="alert ${colorClass}">`;
            html += `<h5>${icon} ${adv.mensaje}</div>`;
            html += `<p>${adv.detalle}</p>`;
            html += '</div>';
        });

        // Sugerencias
        if (validacion.sugerencias.length > 0) {
            html += '<h5 class="mt-3">💡 Sugerencias:</div><ul>';
            validacion.sugerencias.forEach(sug => {
                html += `<li>${sug}</li>`;
            });
            html += '</ul>';
        }

        // Alternativas
        if (validacion.alternativas.length > 0) {
            html += '<h5 style="font-size: 1.125rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 1rem 0;">Planes Alternativos:</h5>';
            validacion.alternativas.forEach(alt => {
                const badge = alt.recomendado ? '<span style="padding: 0.25rem 0.75rem; background: #1a1a1a; color: white; font-size: 0.75rem; font-weight: 600;">RECOMENDADO</span>' : '';
                html += `<div class="card mb-2">`;
                html += `<div style="padding: 1rem;">`;
                html += `<h6>${alt.titulo} ${badge}</h6>`;
                html += `<p>${alt.descripcion}</p>`;
                html += `</div></div>`;
            });
        }

        // Info adicional para volumen
        if (tipo === 'volumen' && validacion.pesoTotalGanado) {
            html += '<div style="padding: 1rem; border: 1px solid #e5e5e5; margin-bottom: 1rem; background: white;">';
            html += `<h6 style="font-size: 1rem; font-weight: 600; color: #1a1a1a; margin: 0 0 0.5rem 0;">Proyección Real:</h6>`;
            html += `<p style="color: #666; margin: 0;">Músculo puro: <strong style="color: #1a1a1a;">${validacion.kgObjetivo}kg</strong><br>`;
            html += `Grasa inevitable: ~${validacion.grasaAproximada.toFixed(1)}kg<br>`;
            html += `<strong style="color: #1a1a1a;">Peso total a ganar: ~${validacion.pesoTotalGanado.toFixed(1)}kg</strong></p>`;
            html += '</div>';
        }

        html += '</div></div>';

        // Insertar antes de resultados
        if (document.getElementById('advertencias-card')) {
            document.getElementById('advertencias-card').remove();
        }
        resultadosDiv.insertAdjacentHTML('beforebegin', html);
    }

    // ============================================
    // FUNCIONES PARA CARGAR PLANES GUARDADOS
    // ============================================
    window.mostrarPlanesGuardados = function() {
        const nombre = document.getElementById('usuario_nombre').value.trim();
        const apellidos = document.getElementById('usuario_apellidos').value.trim();

        if (!nombre || !apellidos) {
            alert('Error: No se pudo obtener el usuario');
            return;
        }

        // Cargar planes del usuario
        fetch(`cargar_planes.php?nombre=${encodeURIComponent(nombre)}&apellidos=${encodeURIComponent(apellidos)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.planes.length === 0) {
                        alert('No tienes planes guardados anteriormente');
                        return;
                    }
                    mostrarModalPlanes(data.planes);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los planes');
            });
    };

    function mostrarModalPlanes(planes) {
        let html = `
        <div class="modal fade" id="modalPlanes" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <!-- Header moderno -->
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 24px 24px 0 0; padding: 1.5rem;">
                        <div>
                            <h5 class="modal-title" style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                                </svg>
                                Planes Guardados
                            </h5>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; opacity: 0.95;">Selecciona un plan para cargarlo</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="opacity: 1;"></button>
                    </div>

                    <!-- Body con tabla moderna -->
                    <div class="modal-body" style="padding: 1.5rem;">`;

        if (planes.length === 0) {
            html += `
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <h5 style="color: #334155; margin-bottom: 0.5rem;">No hay planes guardados</h5>
                    <p style="margin: 0; font-size: 0.875rem;">Crea tu primer plan y guárdalo para cargarlo después</p>
                </div>`;
        } else {
            html += `
                <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <table class="v0-table" style="margin: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Fecha</th>
                                <th style="text-align: left; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Objetivo</th>
                                <th style="text-align: left; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Peso</th>
                                <th style="text-align: left; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Calorías</th>
                                <th style="text-align: left; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Duración</th>
                                <th style="text-align: center; padding: 1rem; background: #f8fafc; color: #334155; font-weight: 600; font-size: 0.875rem;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>`;

            planes.forEach((plan, index) => {
                const fecha = new Date(plan.fecha).toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                let objetivoIcon = '';
                let objetivoText = '';
                let objetivoColor = '';

                if (plan.objetivo === 'deficit') {
                    objetivoIcon = '🔽';
                    objetivoText = 'Déficit';
                    objetivoColor = '#ef4444';
                } else if (plan.objetivo === 'volumen') {
                    objetivoIcon = '🔼';
                    objetivoText = 'Volumen';
                    objetivoColor = '#10b981';
                } else {
                    objetivoIcon = '➡️';
                    objetivoText = 'Mantenimiento';
                    objetivoColor = '#6366f1';
                }

                const duracion = plan.duracion_semanas ? `${plan.duracion_semanas} semanas` : `${plan.duracion_meses} meses`;
                const bgColor = index % 2 === 0 ? '#ffffff' : '#f8fafc';

                html += `
                    <tr style="background: ${bgColor}; transition: background-color 0.2s;">
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0; color: #334155; font-size: 0.875rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                ${fecha}
                            </div>
                        </td>
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0;">
                            <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.75rem; background: ${objetivoColor}15; color: ${objetivoColor}; border-radius: 12px; font-weight: 600; font-size: 0.875rem;">
                                ${objetivoIcon} ${objetivoText}
                            </span>
                        </td>
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0; color: #334155; font-weight: 600; font-size: 0.875rem;">
                            ${plan.peso} kg
                        </td>
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0; color: #334155; font-weight: 600; font-size: 0.875rem;">
                            ${Math.round(plan.calorias)} kcal
                        </td>
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 0.875rem;">
                            ${duracion}
                        </td>
                        <td style="padding: 1rem; border-top: 1px solid #e2e8f0; text-align: center;">
                            <button class="v0-btn v0-btn-primary" onclick="cargarPlan(${plan.id})" style="padding: 0.5rem 1rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Cargar
                            </button>
                        </td>
                    </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>`;
        }

        html += `
                    </div>

                    <!-- Footer con info -->
                    <div class="modal-footer" style="background: #f8fafc; border: none; border-radius: 0 0 24px 24px; padding: 1rem 1.5rem;">
                        <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                            <small style="color: #64748b; font-size: 0.875rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-right: 0.25rem;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                Total de planes: ${planes.length}
                            </small>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 0.5rem 1.25rem; border-radius: 12px; font-size: 0.875rem;">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        // Eliminar modal anterior si existe
        const modalAnterior = document.getElementById('modalPlanes');
        if (modalAnterior) modalAnterior.remove();

        // Agregar nuevo modal
        document.body.insertAdjacentHTML('beforeend', html);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalPlanes'));
        modal.show();
    }

    window.cargarPlan = function(id) {
        fetch(`cargar_plan_id.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const plan = data.plan;

                    // Rellenar formulario con los datos guardados (sin nombre/apellidos)
                    document.getElementById('edad').value = plan.edad;
                    document.getElementById('sexo').value = plan.sexo;
                    document.getElementById('peso').value = plan.peso;
                    document.getElementById('altura').value = plan.altura;

                    // Campos avanzados opcionales
                    if (document.getElementById('anos_entrenando') && plan.anos_entrenando) {
                        document.getElementById('anos_entrenando').value = plan.anos_entrenando;
                    }
                    if (document.getElementById('historial_dietas') && plan.historial_dietas) {
                        document.getElementById('historial_dietas').value = plan.historial_dietas;
                    }

                    document.getElementById('dias_entreno').value = plan.dias_entreno;
                    document.getElementById('horas_gym').value = plan.horas_gym;
                    document.getElementById('dias_cardio').value = plan.dias_cardio;
                    document.getElementById('horas_cardio').value = plan.horas_cardio;
                    document.getElementById('tipo_trabajo').value = plan.tipo_trabajo;
                    document.getElementById('horas_trabajo').value = plan.horas_trabajo;
                    document.getElementById('horas_sueno').value = plan.horas_sueno;
                    document.getElementById('objetivo').value = plan.objetivo;

                    // Cargar nivel_gym si existe
                    if (document.getElementById('nivel_gym') && plan.nivel_gym) {
                        document.getElementById('nivel_gym').value = plan.nivel_gym;
                    }

                    // Mostrar resultados directamente
                    datosCalculados = plan.plan_json;

                    // Guardar el ID del plan cargado para poder generar PDF
                    planGuardadoId = plan.id;

                    // Mostrar sección de resultados
                    document.getElementById('resultados').style.display = 'block';
                    document.getElementById('mensaje-inicial').style.display = 'none';
                    document.getElementById('btn-guardar').style.display = 'block';

                    // Mostrar y habilitar botón PDF
                    const btnPdf = document.getElementById('btn-pdf');
                    btnPdf.style.display = 'block';
                    btnPdf.disabled = false;

                    mostrarResultados(datosCalculados);

                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalPlanes'));
                    if (modal) modal.hide();

                    alert('Plan cargado correctamente');
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el plan');
            });
    };
});
