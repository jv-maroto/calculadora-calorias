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
            velocidad = preferencia; // Compatibilidad
            nivelGym = document.getElementById('nivel_gym').value;
            incluirMinicuts = document.getElementById('incluir_minicuts').value === 'si';
            kgObjetivo = 0; // Se calculará después según meses y nivel
        }

        // Guardar valores
        guardarValores();

        // Obtener datos avanzados opcionales
        const anosEntrenando = document.getElementById('anos_entrenando').value;
        const historialDietas = document.getElementById('historial_dietas').value;
        const cicloRegular = document.getElementById('ciclo_regular').value;
        const circunferenciaCintura = parseFloat(document.getElementById('circunferencia_cintura').value) || null;
        const circunferenciaCuello = parseFloat(document.getElementById('circunferencia_cuello').value) || null;
        const circunferenciaCadera = parseFloat(document.getElementById('circunferencia_cadera').value) || null;

        // CALCULAR TMB usando Mifflin-St Jeor (Fórmula más precisa)
        let tmb;
        if (sexo === 'hombre') {
            tmb = (10 * peso) + (6.25 * altura) - (5 * edad) + 5;
        } else {
            tmb = (10 * peso) + (6.25 * altura) - (5 * edad) - 161;
        }
        
        // Redondear TMB para evitar decimales excesivos
        tmb = Math.round(tmb);

        // CALCULAR % GRASA CORPORAL CON MÉTODO NAVY (si hay datos)
        let porcentajeGrasa = null;
        if (circunferenciaCintura && circunferenciaCuello && altura) {
            if (sexo === 'hombre') {
                // Fórmula Navy para hombres
                // % Grasa = 86.010 × log10(abdomen - cuello) - 70.041 × log10(altura) + 36.76
                const log10Abdomen = Math.log10(circunferenciaCintura - circunferenciaCuello);
                const log10Altura = Math.log10(altura);
                porcentajeGrasa = 86.010 * log10Abdomen - 70.041 * log10Altura + 36.76;
            } else if (circunferenciaCadera) {
                // Fórmula Navy para mujeres
                // % Grasa = 163.205 × log10(cintura + cadera - cuello) - 97.684 × log10(altura) - 78.387
                const log10Circunferencias = Math.log10(circunferenciaCintura + circunferenciaCadera - circunferenciaCuello);
                const log10Altura = Math.log10(altura);
                porcentajeGrasa = 163.205 * log10Circunferencias - 97.684 * log10Altura - 78.387;
            }

            // Limitar valores razonables (5% - 50%)
            if (porcentajeGrasa !== null) {
                porcentajeGrasa = Math.max(5, Math.min(50, porcentajeGrasa));
            }
        }

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

            planData = calcularPlanVolumen(tdee, peso, mesesObjetivo, velocidad, nivelAjustado, diasEntreno, horasGym, diasCardio, incluirMinicuts);
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
                titulo: '⚠️ Plan ajustado automáticamente por seguridad',
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
                titulo: '⚠️ Déficit alto - Requiere disciplina',
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
                titulo: '✅ Déficit óptimo - Excelente balance',
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
                    titulo: '⚠️ Déficit prolongado - Riesgo de adaptación metabólica',
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

    function calcularPlanVolumen(tdee, peso, mesesVolumen, velocidad, nivelGym, diasEntreno, horasGym, diasCardio, incluirMinicuts) {
        // TASAS DE GANANCIA MUSCULAR BASADAS EN CIENCIA 2024
        // Fuente: Men's Health, BodySpec, Healthline (estudios actualizados)
        let kgMusculoPorMesBase;
        if (nivelGym === 'principiante') {
            kgMusculoPorMesBase = 0.9; // ~2 lbs/mes = 0.9 kg/mes
        } else if (nivelGym === 'intermedio') {
            kgMusculoPorMesBase = 0.45; // ~1 lb/mes = 0.45 kg/mes
        } else { // avanzado
            kgMusculoPorMesBase = 0.23; // ~0.5 lb/mes = 0.23 kg/mes
        }

        // AJUSTAR GANANCIA MUSCULAR según tipo de bulk
        // Más calorías = más potencial muscular (dentro de límites genéticos)
        let multiplicadorMusculo = 1.0;
        let ratioMusculoGrasa;

        if (velocidad === 'limpio') {
            multiplicadorMusculo = 0.85; // Lean bulk: -15% músculo pero mucha menos grasa
            ratioMusculoGrasa = 0.75; // 75% músculo, 25% grasa
        } else if (velocidad === 'rapido') {
            multiplicadorMusculo = 1.15; // Aggressive bulk: +15% músculo pero mucha más grasa
            ratioMusculoGrasa = 0.65; // 65% músculo, 35% grasa
        } else { // optimo
            multiplicadorMusculo = 1.0; // Optimal bulk: ganancia base
            ratioMusculoGrasa = 0.70; // 70% músculo, 30% grasa
        }

        // Aplicar multiplicador
        const kgMusculoPorMes = kgMusculoPorMesBase * multiplicadorMusculo;

        // SUPERÁVIT CALÓRICO según tipo de bulk
        let superavitDiario;
        if (velocidad === 'limpio') {
            superavitDiario = 250; // Lean bulk: +250 kcal
        } else if (velocidad === 'rapido') {
            superavitDiario = 500; // Aggressive: +500 kcal
        } else { // optimo
            superavitDiario = 350; // Optimal: +350 kcal
        }

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

        // Calcular mini-cuts programados (solo si el usuario quiere)
        const miniCuts = [];

        if (incluirMinicuts) {
            const frecuenciaMiniCut = nivelGym === 'principiante' ? 16 :
                                      nivelGym === 'intermedio' ? 12 : 10;
            const caloriasMinicut = Math.round(tdee - 300);

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
        }

        // Info de cardio (solo informativa, no recomendar cambios)
        const horasCardioSemanal = diasCardio * (parseFloat(document.getElementById('horas_cardio').value) || 0);
        const infoCardio = horasCardioSemanal > 0
            ? `Cardio actual integrado en el plan: ${horasCardioSemanal.toFixed(1)}h/semana`
            : 'Sin cardio actualmente. El cardio es opcional en volumen, puedes añadir 1-2 sesiones de 15-20min para salud cardiovascular';

        return {
            tipo: 'volumen',
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
            html += generarHTMLVolumen(data.plan, data.tdee, data.peso, data.incluirMinicuts);
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

        // Recomendaciones nutricionales (ELIMINADO - no se usa)

        resultadosDiv.innerHTML = html;
    }

    function generarHTMLDeficit(plan, tdee, peso) {
        // Generar HTML del análisis de objetivo
        let analisisHTML = '';
        if (plan.analisisObjetivo && plan.analisisObjetivo.advertencias.length > 0) {
            plan.analisisObjetivo.advertencias.forEach(adv => {
                let colorClass = 'alert-info';
                if (adv.tipo === 'critico') colorClass = 'alert-danger';
                else if (adv.tipo === 'advertencia') colorClass = 'alert-warning';
                else if (adv.tipo === 'exito') colorClass = 'alert-success';

                analisisHTML += `
                    <div class="alert ${colorClass}">
                        <h5 class="mb-2">${adv.titulo}</h5>
                        <p class="mb-2"><strong>${adv.mensaje}</strong></p>
                        <p class="mb-2">${adv.detalle}</p>
                        <p class="mb-0"><strong>💡 Recomendación:</strong> ${adv.recomendacion}</p>
                    </div>
                `;
            });
        }

        return `
            ${analisisHTML ? `
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">⚠️ Análisis de tu Objetivo</h4>
                    </div>
                    <div class="card-body">
                        ${analisisHTML}
                    </div>
                </div>
            ` : ''}

            <div class="card shadow-lg mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">📉 Tu Plan de Déficit Personalizado</h4>
                </div>
                <div class="card-body">
                    ${plan.vengoDeVolumen && plan.ajusteVolumen > 0 ? `
                        <div class="alert alert-success">
                            <h5>✅ Vienes de volumen - Metabolismo acelerado</h5>
                            <p class="mb-2"><strong>TDEE base calculado:</strong> ${plan.tdee} kcal/día</p>
                            <p class="mb-2"><strong>Ajuste por volumen:</strong> +${plan.ajusteVolumen} kcal/día</p>
                            <p class="mb-0"><strong>TDEE ajustado real:</strong> ${plan.tdeeAjustado} kcal/día</p>
                            <small class="text-muted">Tu metabolismo está acelerado del volumen, puedes comer más y aún así perder grasa. Límites de déficit aumentados en +50 kcal.</small>
                        </div>
                    ` : ''}

                    <div class="alert alert-info">
                        <h5>🎯 Objetivo: Perder ${plan.kgObjetivo} kg</h5>
                        <h5>⏱️ Duración estimada: ${plan.duracion.semanas} semanas (${plan.duracion.meses} meses)</h5>
                        <h5>📊 Pérdida esperada: ~${plan.kgPorSemana} kg/semana (aproximado)</h5>
                        <p class="mb-0">Déficit calórico: ${plan.deficitDiario} kcal/día</p>
                        <p class="mb-0"><strong>Calorías diarias: ${Math.round(plan.tdeeAjustado - plan.deficitDiario)} kcal</strong></p>
                        <small class="text-muted">⚠️ Nota: En déficit bajarás más al principio y menos al final. Todo es aproximado.</small>
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
                        <small class="text-muted">📊 En refeeds: Mantienes peso (0 kg de cambio esperado)</small>
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

    function generarHTMLVolumen(plan, tdee, peso, incluirMinicuts = false) {
        // Usar datos del plan ya calculados
        const kgMusculoPorMes = plan.kgPorMes;
        const ratioMusculoGrasa = plan.ratioMusculoGrasa;
        const kgGrasaPorMes = kgMusculoPorMes * ((1 - ratioMusculoGrasa) / ratioMusculoGrasa);

        return `
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📈 Tu Plan de Volumen Personalizado</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>📅 Duración: ${plan.duracion.meses} meses de volumen</h5>
                        <h5>📊 Nivel: ${plan.nivelGym.charAt(0).toUpperCase() + plan.nivelGym.slice(1)}</h5>
                        <h5>🎯 Ganancia esperada total:</h5>
                        <ul class="mb-2">
                            <li><strong class="text-primary">${plan.kgMusculoEsperado.toFixed(1)} kg de músculo</strong> (${kgMusculoPorMes.toFixed(2)} kg/mes)</li>
                            <li><strong class="text-warning">${plan.kgGrasaEsperada.toFixed(1)} kg de grasa</strong> (${kgGrasaPorMes.toFixed(2)} kg/mes)</li>
                            <li><strong>${plan.kgTotalesEsperados.toFixed(1)} kg totales</strong> (${(ratioMusculoGrasa * 100).toFixed(0)}% músculo / ${((1 - ratioMusculoGrasa) * 100).toFixed(0)}% grasa)</li>
                        </ul>
                        <p class="mb-0">Superávit calórico: ${plan.superavitDiario} kcal/día</p>
                        <small class="text-muted">⚠️ Basado en tasas científicas 2024 para entrenamientos naturales. Los resultados individuales varían según genética, adherencia y calidad del entrenamiento.</small>
                    </div>

                    <h5 class="mt-4">📅 Fases del Plan (con Mini-cuts Integrados)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" style="border: none;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="border: none;">Fase</th>
                                    <th style="border: none;">Calorías</th>
                                    <th style="border: none;">Músculo</th>
                                    <th style="border: none;">Grasa</th>
                                    <th style="border: none;">Tipo</th>
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
                                        <tr style="background-color: #d4edda; border: none;">
                                            <td style="border: none; padding: 12px;"><strong>Fase ${index + 1}</strong><br><small class="text-muted">Mes ${mesInicio}${mesFin > mesInicio ? '-' + mesFin : ''}</small></td>
                                            <td style="border: none; padding: 12px;"><strong class="text-success">${fase.calorias} kcal</strong></td>
                                            <td style="border: none; padding: 12px;">
                                                <strong class="text-primary">+${musculoFase.toFixed(1)} kg</strong><br>
                                                <small class="text-muted">Total: ${musculoAcumulado.toFixed(1)} kg</small>
                                            </td>
                                            <td style="border: none; padding: 12px;">
                                                <strong class="text-warning">+${grasaFase.toFixed(1)} kg</strong><br>
                                                <small class="text-muted">Total: ${grasaAcumulada.toFixed(1)} kg</small>
                                            </td>
                                            <td style="border: none; padding: 12px;"><span class="badge bg-success">Superávit</span></td>
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
                                                <tr style="background-color: #fff3cd; border: none;">
                                                    <td style="border: none; padding: 12px;"><strong>🔻 Mini-cut</strong><br><small class="text-muted">Mes ${miniCut.mes} (${miniCut.semanas.split('-')[1] - miniCut.semanas.split('-')[0] + 1} sem)</small></td>
                                                    <td style="border: none; padding: 12px;"><strong class="text-danger">${miniCut.calorias} kcal</strong></td>
                                                    <td style="border: none; padding: 12px;">
                                                        <strong class="text-danger">-${perdidaMusculo.toFixed(1)} kg</strong><br>
                                                        <small class="text-muted">Total: ${musculoAcumulado.toFixed(1)} kg</small>
                                                    </td>
                                                    <td style="border: none; padding: 12px;">
                                                        <strong class="text-success">-${perdidaGrasa.toFixed(1)} kg</strong><br>
                                                        <small class="text-muted">Total: ${grasaAcumulada.toFixed(1)} kg</small>
                                                    </td>
                                                    <td style="border: none; padding: 12px;"><span class="badge bg-warning text-dark">Déficit</span></td>
                                                </tr>
                                            `;
                                        }
                                    }
                                });

                                // Resumen final
                                const pesoTotal = musculoAcumulado + grasaAcumulada;
                                html += `
                                    <tr style="background-color: #d1ecf1; border-top: 2px solid #bee5eb;">
                                        <td colspan="2" style="border: none; padding: 12px;"><strong>🏁 Resultado Final</strong></td>
                                        <td style="border: none; padding: 12px;">
                                            <strong class="text-primary">+${musculoAcumulado.toFixed(1)} kg</strong><br>
                                            <small>músculo</small>
                                        </td>
                                        <td style="border: none; padding: 12px;">
                                            <strong class="text-warning">+${grasaAcumulada.toFixed(1)} kg</strong><br>
                                            <small>grasa</small>
                                        </td>
                                        <td style="border: none; padding: 12px;">
                                            <strong>+${pesoTotal.toFixed(1)} kg</strong><br>
                                            <small>total</small>
                                        </td>
                                    </tr>
                                `;

                                return html;
                            })()}
                            </tbody>
                        </table>
                    </div>

                    ${plan.miniCuts.length === 0 ? `
                        <div class="alert alert-info">
                            <strong>ℹ️ Sin mini-cuts programados</strong>
                            ${(() => {
                                if (incluirMinicuts) {
                                    // Usuario quería mini-cuts pero no hay porque es muy corto
                                    const frecuenciaMiniCut = plan.nivelGym === 'principiante' ? 16 :
                                                              plan.nivelGym === 'intermedio' ? 12 : 10;
                                    return `<p class="mb-0">Querías incluir mini-cuts, pero tu plan es de solo ${plan.duracion.semanas} semanas. Los mini-cuts se programan cada ${frecuenciaMiniCut} semanas para ${plan.nivelGym}s. <strong>Necesitas al menos ${frecuenciaMiniCut + 3} semanas</strong> para que tenga sentido hacer un mini-cut. Con duraciones cortas, es mejor hacer volumen continuo.</p>`;
                                } else {
                                    // Usuario eligió NO incluir mini-cuts
                                    return `<p class="mb-0">Has elegido un volumen continuo sin mini-cuts. Ten en cuenta que acumularás más grasa, pero el proceso será más simple y directo.</p>`;
                                }
                            })()}
                        </div>
                    ` : ''}

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

        fetch('guardar.php', {
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
                    alert('✅ Plan guardado correctamente en la base de datos (ID: ' + data.id + ')');
                    btnGuardar.innerHTML = '✅ Plan Guardado';

                    // Habilitar y mostrar botón PDF
                    const btnPdf = document.getElementById('btn-pdf');
                    btnPdf.style.display = 'block';
                    btnPdf.disabled = false;
                } else {
                    alert('❌ Error al guardar: ' + data.error);
                    btnGuardar.innerHTML = '💾 Guardar Plan';
                    btnGuardar.disabled = false;
                }
            } catch (e) {
                console.error('Error parseando JSON:', e);
                console.error('Respuesta recibida:', text);
                alert('❌ Error: La respuesta del servidor no es válida. Revisa la consola para más detalles.');
                btnGuardar.innerHTML = '💾 Guardar Plan';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al guardar: ' + error.message);
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

    // ============================================
    // FUNCIONES PARA CARGAR PLANES GUARDADOS
    // ============================================
    window.mostrarPlanesGuardados = function() {
        const nombre = document.getElementById('usuario_nombre').value.trim();
        const apellidos = document.getElementById('usuario_apellidos').value.trim();

        if (!nombre || !apellidos) {
            alert('⚠️ Error: No se pudo obtener el usuario');
            return;
        }

        // Cargar planes del usuario
        fetch(`cargar_planes.php?nombre=${encodeURIComponent(nombre)}&apellidos=${encodeURIComponent(apellidos)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.planes.length === 0) {
                        alert('ℹ️ No tienes planes guardados anteriormente');
                        return;
                    }
                    mostrarModalPlanes(data.planes);
                } else {
                    alert('❌ Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al cargar los planes');
            });
    };

    function mostrarModalPlanes(planes) {
        let html = '<div class="modal fade" id="modalPlanes" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">';
        html += '<div class="modal-header"><h5 class="modal-title">📂 Planes Guardados</h5>';
        html += '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';
        html += '<div class="modal-body"><div class="table-responsive"><table class="table table-hover">';
        html += '<thead><tr><th>Fecha</th><th>Objetivo</th><th>Peso</th><th>Calorías</th><th>Duración</th><th>Acción</th></tr></thead><tbody>';

        planes.forEach(plan => {
            const fecha = new Date(plan.fecha).toLocaleDateString('es-ES');
            const objetivo = plan.objetivo === 'deficit' ? '🔽 Déficit' : (plan.objetivo === 'volumen' ? '🔼 Volumen' : '⚖️ Mantenimiento');
            const duracion = plan.duracion_semanas ? `${plan.duracion_semanas} semanas` : `${plan.duracion_meses} meses`;

            html += `<tr>
                <td>${fecha}</td>
                <td>${objetivo}</td>
                <td>${plan.peso} kg</td>
                <td>${Math.round(plan.calorias)} kcal</td>
                <td>${duracion}</td>
                <td><button class="btn btn-sm btn-primary" onclick="cargarPlan(${plan.id})">Cargar</button></td>
            </tr>`;
        });

        html += '</tbody></table></div></div></div></div></div>';

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

                    alert('✅ Plan cargado correctamente');
                } else {
                    alert('❌ Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al cargar el plan');
            });
    };
});
