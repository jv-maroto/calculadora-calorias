# 🚀 Instrucciones de Instalación - Calculadora de Calorías

## ✅ Estado Actual
- ✅ Proyecto descargado desde GitHub
- ✅ Archivos principales verificados
- ✅ `connection.php` creado
- ✅ `test_guardar.php` creado
- ⚠️ **PENDIENTE**: Configurar base de datos MySQL

## 📋 Pasos para Completar la Instalación

### 1. Configurar Base de Datos MySQL

**Opción A: Usando phpMyAdmin (Recomendado)**
1. Abre tu navegador y ve a `http://localhost/phpmyadmin`
2. Haz clic en "Importar" en la barra superior
3. Selecciona el archivo `database.sql` de este proyecto
4. Haz clic en "Continuar" para importar
5. Verifica que se creó la base de datos `calculadora_calorias`

**Opción B: Usando línea de comandos**
```bash
# Conectarse a MySQL
mysql -u root -p

# Ejecutar el archivo SQL
source C:/xampp/htdocs/calculadora/database.sql
```

### 2. Verificar la Instalación

1. Abre tu navegador y ve a: `http://localhost/calculadora/test_guardar.php`
2. Verifica que todos los tests muestren ✅ (verde)
3. Si hay errores ❌ (rojo), revisa las instrucciones de solución

### 3. Acceder a la Aplicación

Una vez que todos los tests pasen:
- **Calculadora principal**: `http://localhost/calculadora/index.php`
- **Sistema de seguimiento**: `http://localhost/calculadora/seguimiento.php`

## 🔧 Solución de Problemas Comunes

### Error: "Base de datos no existe"
**Solución**: Importa el archivo `database.sql` en phpMyAdmin

### Error: "Tabla no existe"
**Solución**: Verifica que el archivo `database.sql` se importó completamente

### Error: "Conexión fallida"
**Solución**: 
1. Verifica que XAMPP esté ejecutándose
2. Verifica que MySQL esté activo en el panel de control de XAMPP
3. Revisa las credenciales en `connection.php`

### Error: "Permisos de escritura"
**Solución**: 
1. En Windows: Haz clic derecho en la carpeta del proyecto → Propiedades → Seguridad → Editar → Dar permisos completos
2. O ejecuta XAMPP como administrador

## 📁 Estructura del Proyecto

```
calculadora/
├── index.php              # 🏠 Página principal (calculadora)
├── seguimiento.php        # 📊 Sistema de ajuste de calorías
├── script.js              # ⚙️ Lógica de la calculadora
├── seguimiento.js         # ⚙️ Lógica del seguimiento
├── limites_reales.js      # 📚 Base de conocimiento científico
├── styles.css             # 🎨 Estilos personalizados
├── guardar.php            # 💾 API para guardar planes
├── generar_pdf.php        # 📄 Generador de PDF
├── connection.php         # 🔌 Conexión a base de datos
├── database.sql           # 🗄️ Estructura de la base de datos
├── test_guardar.php       # 🧪 Test de instalación
└── README.md              # 📖 Documentación completa
```

## 🎯 Funcionalidades Principales

### Calculadora Principal (`index.php`)
- ✅ Cálculo de TMB usando fórmula Mifflin-St Jeor
- ✅ Cálculo de TDEE basado en actividad física
- ✅ Planes personalizados para déficit, volumen y mantenimiento
- ✅ Distribución de macronutrientes optimizada
- ✅ Validación de objetivos realistas
- ✅ Guardado en base de datos
- ✅ Exportación a PDF

### Sistema de Seguimiento (`seguimiento.php`)
- ✅ Análisis de progreso real
- ✅ Ajuste automático de calorías
- ✅ Detección de estancamientos
- ✅ Recomendaciones personalizadas

## 🔬 Base Científica

### Límites de Ganancia Muscular
- **Principiantes**: 1.0-1.5 kg/mes (hombres), 0.5-0.75 kg/mes (mujeres)
- **Intermedios**: 0.5-0.75 kg/mes (hombres), 0.25-0.4 kg/mes (mujeres)
- **Avanzados**: 0.25-0.4 kg/mes (hombres), 0.1-0.2 kg/mes (mujeres)

### Límites de Pérdida de Grasa
- **Saludable**: 0.4-0.7 kg/semana
- **Agresivo**: 0.7-1.0 kg/semana
- **Extremo**: >1.0 kg/semana (no recomendado >4 semanas)

## 🆘 Soporte

Si encuentras problemas:
1. Revisa el archivo `debug_guardar.log` que se genera automáticamente
2. Ejecuta `test_guardar.php` para diagnóstico completo
3. Verifica que XAMPP esté ejecutándose correctamente
4. Asegúrate de que todos los archivos estén en la carpeta correcta

## 🎉 ¡Listo para Usar!

Una vez completada la instalación, tendrás una calculadora de calorías profesional con:
- ✅ Cálculos científicos precisos
- ✅ Validación de objetivos realistas
- ✅ Planes personalizados
- ✅ Seguimiento de progreso
- ✅ Exportación a PDF
- ✅ Base de datos para guardar planes

**¡Disfruta calculando tus planes de nutrición! 💪**
