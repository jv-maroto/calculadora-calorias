# 📤 Instrucciones para Subir a GitHub

Sigue estos pasos para subir tu proyecto a GitHub:

## 📋 Requisitos Previos

1. **Git instalado**: Verifica con `git --version`
2. **Cuenta de GitHub**: Crea una en [github.com](https://github.com) si no tienes
3. **Git configurado** con tu nombre y email:
```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
```

## 🚀 Pasos para Subir el Proyecto

### 1️⃣ Crear repositorio en GitHub

1. Ve a [github.com/new](https://github.com/new)
2. **Nombre del repositorio**: `calculadora-calorias` (o el que prefieras)
3. **Descripción**: "Sistema avanzado de cálculo de calorías con validación inteligente y ajuste basado en progreso real"
4. **Visibilidad**: Public (o Private si prefieres)
5. **NO** marques "Add a README file" (ya lo tenemos)
6. Click en **"Create repository"**

### 2️⃣ Preparar archivos locales

Abre una terminal/CMD en la carpeta `C:\xampp\htdocs\Calculator` y ejecuta:

```bash
# Inicializar repositorio Git
git init

# Copiar connection.example.php a connection.php (si no lo has hecho)
copy connection.example.php connection.php

# Añadir todos los archivos
git add .

# Ver qué archivos se van a subir
git status

# Crear primer commit
git commit -m "Initial commit: Calculadora de calorías con validación inteligente"
```

### 3️⃣ Conectar con GitHub y subir

Reemplaza `TU_USUARIO` con tu nombre de usuario de GitHub:

```bash
# Añadir repositorio remoto
git remote add origin https://github.com/TU_USUARIO/calculadora-calorias.git

# Verificar que se añadió correctamente
git remote -v

# Subir a GitHub (primera vez)
git push -u origin master
```

Si te pide autenticación:
- **Usuario**: Tu nombre de usuario de GitHub
- **Contraseña**: Usa un **Personal Access Token** (no tu contraseña normal)

### 4️⃣ Crear Personal Access Token (si es necesario)

1. Ve a [github.com/settings/tokens](https://github.com/settings/tokens)
2. Click en "Generate new token" → "Generate new token (classic)"
3. **Note**: "Calculadora Calorias"
4. **Expiration**: 90 days (o más)
5. **Scopes**: Marca solo `repo`
6. Click "Generate token"
7. **COPIA EL TOKEN** (solo se muestra una vez)
8. Úsalo como contraseña cuando Git te lo pida

### 5️⃣ Verificar que se subió

1. Ve a `https://github.com/TU_USUARIO/calculadora-calorias`
2. Deberías ver todos los archivos
3. El README.md se mostrará automáticamente en la página principal

## 📝 Comandos para Futuras Actualizaciones

Cuando hagas cambios en el proyecto:

```bash
# Ver archivos modificados
git status

# Añadir archivos modificados
git add .

# O añadir archivos específicos
git add archivo1.php archivo2.js

# Crear commit con mensaje descriptivo
git commit -m "Descripción de los cambios"

# Subir cambios a GitHub
git push
```

## 🔧 Comandos Útiles

```bash
# Ver historial de commits
git log --oneline

# Ver diferencias antes de commit
git diff

# Deshacer cambios en un archivo (antes de commit)
git checkout -- archivo.php

# Ver ramas
git branch

# Crear nueva rama
git checkout -b nombre-rama

# Cambiar de rama
git checkout master
```

## 📂 Archivos que NO se suben (están en .gitignore)

- `connection.php` - Configuración local (cada uno usa la suya)
- `debug_guardar.log` - Logs temporales
- Archivos de sistema (`.DS_Store`, `Thumbs.db`)
- Configuraciones de IDEs (`.vscode/`, `.idea/`)

## ⚠️ IMPORTANTE: Seguridad

**NUNCA** subas a GitHub:
- ❌ Contraseñas de bases de datos
- ❌ Claves API
- ❌ Tokens de acceso
- ❌ Datos personales sensibles

Por eso `connection.php` está en `.gitignore`.

## 🎨 Personalizar README

Antes de subir, puedes personalizar el README.md:

1. Cambia `TU_USUARIO` por tu nombre de usuario real
2. Añade tu email de contacto si quieres
3. Personaliza la descripción
4. Añade screenshots (opcional):
   - Toma capturas de pantalla
   - Crea carpeta `screenshots/`
   - Añádelas al README: `![Screenshot](screenshots/calculadora.png)`

## 🏷️ Añadir Topics (Etiquetas)

En GitHub, en tu repositorio:
1. Click en ⚙️ (Settings) o en "Add topics"
2. Añade: `nutrition`, `calories`, `fitness`, `php`, `javascript`, `mysql`, `bootstrap`, `health`

## 📊 GitHub Pages (Opcional)

**Nota**: GitHub Pages solo sirve para HTML/CSS/JS estático. Este proyecto usa PHP y MySQL, así que necesitarías un hosting con PHP.

Alternativas para hospedar online:
- **InfinityFree**: Hosting PHP/MySQL gratuito
- **000webhost**: Hosting gratuito con PHP
- **Heroku**: Con ClearDB MySQL (gratis con límites)

## ✅ Checklist Final

Antes de hacer público tu repositorio:

- [ ] README.md completo y personalizado
- [ ] .gitignore configurado correctamente
- [ ] connection.php NO está en el repositorio
- [ ] database.sql incluido y funcionando
- [ ] Instrucciones de instalación claras
- [ ] Sin contraseñas ni datos sensibles
- [ ] Código comentado y limpio
- [ ] License añadida (opcional: MIT, GPL, etc.)

## 🎉 ¡Listo!

Tu proyecto ya está en GitHub y listo para:
- ✅ Compartir con otros
- ✅ Colaborar
- ✅ Recibir contribuciones
- ✅ Mostrar en tu portfolio
- ✅ Control de versiones profesional

---

**¿Necesitas ayuda?**
- [Documentación Git](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [GitHub CLI](https://cli.github.com/) - Alternativa más fácil
