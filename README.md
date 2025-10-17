# 🧮 Calculadora de Calorías Inteligente

Sistema avanzado de cálculo de calorías y planificación nutricional con validación científica y ajustes personalizados basados en progreso real.

## 🌟 Características Principales

### 📊 Calculadora Base
- **Fórmula Mifflin-St Jeor**: El método más preciso para calcular TMB
- **Factor de actividad personalizado**: Basado en gym, cardio, trabajo y sueño
- **Tres objetivos**: Déficit (perder grasa), Volumen (ganar músculo), Mantenimiento

### 🤖 Validación Inteligente
- **Detección de objetivos irreales**: Te avisa si pides algo imposible ("te has flipado")
- **Límites científicos**: Basado en estudios reales de ganancia muscular y pérdida de grasa
- **Sugerencias automáticas**: Ofrece planes alternativos realistas
- **Advertencias personalizadas**: Por nivel, sexo, volumen de entrenamiento

### 📈 Sistema de Ajuste Basado en Progreso Real
- **Análisis de velocidad**: Compara tu progreso real vs objetivo
- **Recomendaciones automáticas**: Sube/baja calorías según resultados
- **Detección de problemas**: Estancamiento, pérdida/ganancia excesiva
- **Consejos contextuales**: Basados en energía y rendimiento

### 🔬 Datos Avanzados (Opcionales)
- **Años de entrenamiento**: Ajusta expectativas de ganancia muscular
- **Somatotipo**: Ectomorfo/Mesomorfo/Endomorfo (±5% TMB)
- **Historial de dietas**: Compensa adaptación metabólica (efecto yoyo)
- **Ciclo menstrual**: Advertencias sobre retención de líquidos (mujeres)

### 📅 Planificación Detallada
- **Fases progresivas**: Ajuste de calorías cada 4-6 semanas
- **Mini-cuts** (volumen): Cada 10-16 semanas según nivel
- **Refeeds** (déficit): Cada 6-14 días según agresividad
- **Distribución de macros**: Proteína/Grasa/Carbohidratos optimizados

### 💾 Gestión de Planes
- **Guardado en base de datos**: MySQL con estructura completa
- **Exportación a PDF**: Imprime o descarga tu plan
- **Cookies + LocalStorage**: Recuerda tus datos entre sesiones

## 🚀 Instalación

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx) o XAMPP/WAMP

### Paso 1: Clonar el repositorio
```bash
git clone https://github.com/TU_USUARIO/calculadora-calorias.git
cd calculadora-calorias
```

### Paso 2: Configurar base de datos
1. Abre phpMyAdmin o tu gestor MySQL
2. Importa el archivo `database.sql`
3. Verifica que se creó la base de datos `calculadora_calorias`

### Paso 3: Configurar conexión (si es necesario)
Edita `connection.php` si tu configuración MySQL es diferente:
```php
$conn = new mysqli("localhost", "root", "", "calculadora_calorias");
```

### Paso 4: Probar instalación
1. Abre `http://localhost/calculadora-calorias/test_guardar.php`
2. Verifica que todo esté ✅
3. Accede a `http://localhost/calculadora-calorias/index.php`

## 📖 Uso

### Calculadora Principal
1. **Datos personales**: Edad, sexo, peso, altura
2. **Datos avanzados** (opcional): Años entrenando, somatotipo, historial dietas
3. **Actividad física**: Días de gym, cardio, horas por sesión
4. **Estilo de vida**: Tipo de trabajo, horas trabajo/sueño
5. **Objetivo**:
   - **Déficit**: Kg a perder + tiempo objetivo
   - **Volumen**: Kg de MÚSCULO a ganar + nivel gym + tiempo
   - **Mantenimiento**: Solo calorías de mantenimiento
6. **Calcular**: El sistema valida y muestra tu plan personalizado
7. **Guardar**: Almacena en base de datos
8. **PDF**: Exporta tu plan completo

### Sistema de Ajuste (Seguimiento)
1. Accede a `seguimiento.php` desde el menú
2. Introduce:
   - Tu objetivo actual (déficit/volumen)
   - Calorías que consumes
   - Semanas llevando ese plan
   - Peso inicial y actual
   - Cómo te sientes (energía, rendimiento)
3. **Analizar**: El sistema calcula el ajuste exacto necesario
4. Aplica las nuevas calorías durante 1-2 semanas
5. Repite el análisis periódicamente

## 📂 Estructura del Proyecto

```
calculadora-calorias/
├── index.php              # Calculadora principal
├── seguimiento.php        # Sistema de ajuste de calorías
├── script.js              # Lógica calculadora principal
├── seguimiento.js         # Lógica ajuste de calorías
├── limites_reales.js      # Base conocimiento científico
├── styles.css             # Estilos personalizados
├── guardar.php            # API para guardar planes
├── generar_pdf.php        # Generador de PDF
├── connection.php         # Configuración MySQL
├── database.sql           # Estructura base de datos
├── test_guardar.php       # Test de conexión
└── README.md              # Este archivo
```

## 🔬 Base Científica

### Límites de Ganancia Muscular
- **Principiantes**: 1.0-1.5 kg/mes (hombres), 0.5-0.75 kg/mes (mujeres)
- **Intermedios**: 0.5-0.75 kg/mes (hombres), 0.25-0.4 kg/mes (mujeres)
- **Avanzados**: 0.25-0.4 kg/mes (hombres), 0.1-0.2 kg/mes (mujeres)

### Límites de Pérdida de Grasa
- **Saludable**: 0.4-0.7 kg/semana
- **Agresivo**: 0.7-1.0 kg/semana
- **Extremo**: >1.0 kg/semana (no recomendado >4 semanas)

### Ajustes Metabólicos
- **Ectomorfo**: +5% TMB
- **Endomorfo**: -5% TMB
- **Historial dietas (yoyo)**: -3% a -7% TMB
- **Actividad física**: Factor 1.2 a 2.0 según volumen

## 🎯 Ejemplos de Uso

### Ejemplo 1: Usuario quiere ganar músculo
```
Datos:
- Hombre, 25 años, 70kg, 175cm
- Intermedio (2 años entrenando)
- 4 días gym, 1.5h por sesión
- Objetivo: Ganar 10kg músculo en 12 meses

Resultado:
✅ "Objetivo realista - En 12 meses con 0.8kg/mes"
📊 Plan: 2600 kcal/día (superávit 400 kcal)
💪 Macros: 140g proteína, 60g grasa, 350g carbohidratos
✂️ Mini-cuts: Mes 3, 6, 9, 12 (3 semanas cada uno)
```

### Ejemplo 2: Usuario quiere perder grasa
```
Datos:
- Mujer, 30 años, 80kg, 165cm
- Sin experiencia gym
- Trabajo sedentario, 7h sueño
- Objetivo: Perder 15kg en 20 semanas

Resultado:
✅ "Ritmo saludable - 0.75kg/semana"
📊 Plan: 1400 kcal/día (déficit 500 kcal)
💪 Macros: 160g proteína, 40g grasa, 100g carbohidratos
🔄 Refeeds: Cada 10-12 días (1900 kcal)
```

### Ejemplo 3: Usuario se estancó
```
Progreso:
- Objetivo: Déficit
- Calorías: 1600 kcal/día
- 4 semanas llevando el plan
- Peso: 75kg → 75.2kg (ganó peso)

Análisis:
🚫 "SIN PÉRDIDA DE PESO - No estás en déficit real"
💡 Recomendación: Baja 400 kcal → 1200 kcal/día
📝 "Posible causa: estás comiendo más de lo que crees"
```

## 🐛 Debugging

### El botón "Guardar" se queda cargando
1. Abre `test_guardar.php` en tu navegador
2. Verifica que todos los checks estén ✅
3. Revisa el archivo `debug_guardar.log` (se crea automáticamente)
4. Comprueba que la base de datos existe y la conexión es correcta

### El PDF no se genera
1. Verifica que el plan se haya guardado primero
2. El botón PDF solo se habilita DESPUÉS de guardar
3. Si se guardó pero no abre, revisa `generar_pdf.php` directamente

### Las cookies no funcionan
1. Limpia localStorage: Abre consola (F12) → `localStorage.clear()`
2. Limpia cookies: Navegador → Configuración → Borrar cookies
3. Recarga la página

## 🤝 Contribuir

Las contribuciones son bienvenidas:

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Añade nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es de código abierto. Puedes usarlo, modificarlo y distribuirlo libremente.

## 🙏 Créditos

- **Fórmula Mifflin-St Jeor**: Método científico validado para TMB
- **Límites musculares**: Basado en estudios de Lyle McDonald y Alan Aragon
- **Bootstrap 5**: Framework CSS
- **Claude AI**: Asistencia en desarrollo

## 📧 Contacto

Para preguntas, sugerencias o reportar bugs, abre un issue en GitHub.

---

**⚠️ Disclaimer**: Esta calculadora es una herramienta educativa. Consulta con un profesional de la salud antes de hacer cambios significativos en tu dieta o ejercicio.
