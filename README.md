# 🎨 Calculadora de Calorías V2 - Frontend Moderno

> Versión 2.0 con diseño moderno v0.dev style

## 🌟 Características

- ✨ Diseño moderno con Tailwind CSS
- 🎯 Iconos Lucide escalables
- 📱 100% Responsive (móvil, tablet, desktop)
- 🎨 Sistema de diseño consistente
- ⚡ Animaciones suaves
- 💪 8 módulos completos

## 📋 Módulos Incluidos

### 1. 📊 Calculadora de Calorías
**Archivo:** `index.php`

Calculadora completa de TDEE y macronutrientes con:
- Sliders precisos (0.05h de incremento)
- Radio buttons visuales modernos
- Tabs para objetivos (Déficit/Mantenimiento/Volumen)
- Validación en tiempo real

### 2. ⚖️ Registro de Peso
**Archivo:** `introducir_peso_v0.php`

Sistema de seguimiento de peso con:
- Formulario de registro diario
- Historial con indicadores de cambio (↑↓)
- Gráfico de evolución
- Colores semánticos

### 3. 📈 Gráficas de Progreso
**Archivo:** `grafica_v0.php`

Visualización de datos con:
- Chart.js personalizado
- Estadísticas en cards
- Filtros por período
- Exportación de datos

### 4. 🎯 Ajuste de Calorías
**Archivo:** `seguimiento_v0.php`

Sistema inteligente de análisis que:
- Analiza tu progreso real
- Recomienda ajustes personalizados
- Considera energía y rendimiento
- Calcula cambios necesarios

### 5. 🔄 Reverse Diet
**Archivo:** `reverse_diet_v0.php`

Wizard de 7 pasos para:
- Transición de déficit a volumen
- Cálculo de adaptación metabólica
- Plan semanal personalizado
- Proyecciones de peso

### 6. 🏋️ Rutinas de Entrenamiento
**Archivo:** `rutinas_v0.php`

Gestión de rutinas con:
- Visualización por días
- Progreso semanal
- Cards con gradientes por tipo
- Acceso rápido a entrenamientos

### 7. 💪 Día de Entrenamiento
**Archivo:** `dia_entrenamiento_v0.php`

Registro de entrenamientos con:
- Formulario por ejercicio
- Registro de sets (peso/reps/RPE)
- Histórico de rendimiento
- Comparación de marcas

### 8. ⚙️ Gestión de Ejercicios
**Archivo:** `gestionar_ejercicios_v0.php`

CRUD completo para:
- Añadir/editar/eliminar ejercicios
- Organización por días
- Modal moderno
- Validación de datos

## 🎨 Sistema de Diseño

### Colores
```css
Primario:   #6366f1 (Índigo)
Éxito:      #10b981 (Verde)
Peligro:    #ef4444 (Rojo)
Advertencia:#f59e0b (Amarillo)
Info:       #0ea5e9 (Azul)
```

### Componentes
- **Cards:** Bordes redondeados 24px, sombras suaves
- **Botones:** Gradientes, animaciones hover
- **Inputs:** Focus states animados
- **Badges:** Colores semánticos
- **Modals:** Animación fade-in
- **Tablas:** Hover states, responsive

## 🚀 Instalación

### Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Navegador moderno

### Configuración

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/v2-frontend.git
cd v2-frontend
```

2. **Configurar base de datos**
```bash
# Importar estructura de BD (si existe)
mysql -u root -p calculadora < database.sql
```

3. **Configurar conexión**
Edita `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'calculadora');
```

4. **Iniciar servidor**
```bash
# Si usas XAMPP
# Coloca la carpeta en c:/xampp/htdocs/

# Accede a:
http://localhost/v2-frontend/
```

## 📱 Responsive Breakpoints

```css
Móvil:   < 768px  (1 columna)
Tablet:  768-1024px (2 columnas)
Desktop: > 1024px (3-4 columnas)
```

## 🛠️ Tecnologías

- **Frontend:**
  - HTML5
  - Tailwind CSS (CDN)
  - JavaScript (Vanilla)
  - Lucide Icons

- **Backend:**
  - PHP 7.4+
  - MySQL

- **Librerías:**
  - Chart.js (gráficas)
  - Custom v0-theme.css

## 📂 Estructura de Archivos

```
v2/
├── index.php                      # Página principal
├── introducir_peso_v0.php         # Registro de peso
├── grafica_v0.php                 # Gráficas
├── seguimiento_v0.php             # Ajuste calorías
├── reverse_diet_v0.php            # Reverse diet
├── rutinas_v0.php                 # Rutinas
├── dia_entrenamiento_v0.php       # Entrenamiento
├── gestionar_ejercicios_v0.php    # Gestión ejercicios
├── assets/
│   └── css/
│       └── v0-theme.css           # Sistema de diseño
├── *.js                           # JavaScript files
├── config.php                     # Configuración BD
├── login.php                      # Login
├── logout.php                     # Logout
└── README.md                      # Este archivo
```

## 🎯 Características Técnicas

### Compatibilidad
- ✅ 100% compatible con código JavaScript original
- ✅ Sin cambios en lógica PHP
- ✅ Mantiene IDs originales
- ✅ Base de datos sin modificar

### Performance
- ⚡ Carga rápida (CDN Tailwind)
- 🎨 CSS optimizado
- 📦 Assets mínimos
- 🚀 Sin dependencias pesadas

### Seguridad
- 🔒 Sesiones PHP
- 🛡️ Validación de formularios
- 🚫 Prevención SQL injection (prepared statements)
- ✅ Sanitización de inputs

## 📖 Guía de Uso

### Flujo de Usuario Típico

1. **Login** → Acceso con credenciales
2. **Calculadora** → Calcula TDEE y macros
3. **Registrar Peso** → Añade peso diario
4. **Ver Gráficas** → Analiza progreso
5. **Ajustar Calorías** → Optimiza según resultados
6. **Rutinas** → Programa entrenamientos
7. **Entrenar** → Registra ejercicios

## 🎨 Personalización

### Cambiar Colores
Edita `assets/css/v0-theme.css`:
```css
:root {
    --primary: #6366f1;
    --success: #10b981;
    --danger: #ef4444;
}
```

### Añadir Nueva Página
1. Copia estructura de página existente
2. Incluye Tailwind + Lucide + v0-theme
3. Usa clases del sistema: `.v0-card`, `.v0-btn`, etc.
4. Inicializa Lucide: `lucide.createIcons()`

## 🐛 Troubleshooting

### Error de Conexión BD
```php
// Verifica config.php
// Asegúrate que MySQL está corriendo
```

### Iconos no se muestran
```html
<!-- Verifica que Lucide esté incluido -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
```

### Estilos no cargan
```html
<!-- Verifica rutas de assets -->
<link rel="stylesheet" href="assets/css/v0-theme.css">
```

## 📊 Métricas

- **8 páginas** completamente diseñadas
- **1 sistema de diseño** unificado
- **400+ líneas** de CSS reutilizable
- **100% responsive** en todos los módulos
- **0 errores** de funcionalidad

## 🚀 Roadmap

- [ ] Modo oscuro
- [ ] PWA (Progressive Web App)
- [ ] Notificaciones push
- [ ] Exportar datos a PDF
- [ ] Integración con wearables
- [ ] API REST
- [ ] App móvil nativa

## 📝 Changelog

### Version 2.0 (2025)
- ✅ Rediseño completo con v0.dev style
- ✅ 8 módulos con diseño moderno
- ✅ Sistema de diseño unificado
- ✅ 100% responsive
- ✅ Iconos Lucide
- ✅ Animaciones suaves

### Version 1.0
- ✅ Funcionalidad básica
- ✅ Bootstrap design
- ✅ Módulos principales

## 👤 Autor

**Tu Nombre**
- GitHub: [@tu-usuario](https://github.com/tu-usuario)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## 🙏 Agradecimientos

- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS
- [Lucide Icons](https://lucide.dev/) - Iconos
- [Chart.js](https://www.chartjs.org/) - Gráficas
- [v0.dev](https://v0.dev/) - Inspiración de diseño

---

**Versión:** 2.0
**Estado:** ✅ Production Ready
**Última actualización:** 2025

¡Disfruta de tu calculadora de calorías con diseño moderno! 💪✨
