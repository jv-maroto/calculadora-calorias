# 📦 Instrucciones para subir a GitHub

## ✅ Estado Actual

El repositorio local está completamente preparado:
- ✅ Git inicializado
- ✅ Todos los archivos añadidos
- ✅ Primer commit creado
- ✅ Rama principal: `main`
- ✅ README.md completo
- ✅ .gitignore configurado

**Ubicación local:** `c:\xampp\htdocs\calculadora\v2`

---

## 🚀 Pasos para crear el repositorio en GitHub

### Opción 1: Desde la Web de GitHub (Recomendado)

1. **Ve a GitHub:** https://github.com/new

2. **Completa el formulario:**
   ```
   Repository name:    v2-frontend
   Description:        Calculadora de Calorías V2 con diseño moderno v0.dev style
   Visibility:         Public (o Private según prefieras)

   ⚠️ NO marques:
   [ ] Add a README file
   [ ] Add .gitignore
   [ ] Choose a license

   (Ya tenemos estos archivos en nuestro proyecto)
   ```

3. **Click en "Create repository"**

4. **Copia las instrucciones que aparecen** (sección: "…or push an existing repository from the command line")

5. **Ejecuta en tu terminal:**

   ```bash
   cd c:/xampp/htdocs/calculadora/v2

   # Reemplaza TU_USUARIO con tu nombre de usuario de GitHub
   git remote add origin https://github.com/TU_USUARIO/v2-frontend.git

   git push -u origin main
   ```

### Opción 2: Usando GitHub CLI (Si la instalas)

1. **Instala GitHub CLI:**
   - Descarga desde: https://cli.github.com/
   - O con winget: `winget install GitHub.cli`

2. **Autentícate:**
   ```bash
   gh auth login
   ```

3. **Crea y sube el repositorio:**
   ```bash
   cd c:/xampp/htdocs/calculadora/v2
   gh repo create v2-frontend --public --source=. --push
   ```

---

## 📋 Comandos Listos para Copiar

Después de crear el repositorio en GitHub, ejecuta:

```bash
# Navegar a la carpeta
cd c:/xampp/htdocs/calculadora/v2

# Agregar el remote (reemplaza TU_USUARIO)
git remote add origin https://github.com/TU_USUARIO/v2-frontend.git

# Verificar que se agregó correctamente
git remote -v

# Subir al repositorio
git push -u origin main
```

**Salida esperada:**
```
Enumerating objects: 26, done.
Counting objects: 100% (26/26), done.
Delta compression using up to 8 threads
Compressing objects: 100% (23/23), done.
Writing objects: 100% (26/26), 89.45 KiB | 8.13 MiB/s, done.
Total 26 (delta 2), reused 0 (delta 0)
To https://github.com/TU_USUARIO/v2-frontend.git
 * [new branch]      main -> main
Branch 'main' set up to track remote branch 'main' from 'origin'.
```

---

## 🔐 Autenticación

Si te pide credenciales:

### Opción A: Personal Access Token (Recomendado)
1. Ve a: https://github.com/settings/tokens
2. Click en "Generate new token (classic)"
3. Dale un nombre: "v2-frontend"
4. Selecciona: `repo` (todos los scopes de repo)
5. Click "Generate token"
6. **Copia el token** (no podrás verlo después)
7. Cuando git te pida password, **pega el token**

### Opción B: GitHub Desktop
1. Instala GitHub Desktop
2. Abre la carpeta `v2` desde GitHub Desktop
3. Publica el repositorio desde la app

### Opción C: SSH (Avanzado)
```bash
# Generar clave SSH
ssh-keygen -t ed25519 -C "tu_email@example.com"

# Agregar a ssh-agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# Copiar clave pública
cat ~/.ssh/id_ed25519.pub

# Agregar en GitHub: Settings > SSH and GPG keys > New SSH key
```

---

## ✅ Verificación

Después de subir, verifica que todo esté correcto:

1. **Ve a tu repositorio:**
   ```
   https://github.com/TU_USUARIO/v2-frontend
   ```

2. **Deberías ver:**
   - ✅ README.md renderizado en la página principal
   - ✅ 21 archivos
   - ✅ Carpeta `assets/css/`
   - ✅ Commit message: "Initial commit: V2 Frontend with modern design"
   - ✅ Badge: "1 commit"

---

## 📝 Próximos Commits

Para futuros cambios:

```bash
cd c:/xampp/htdocs/calculadora/v2

# Ver cambios
git status

# Añadir archivos modificados
git add .

# Crear commit
git commit -m "Descripción del cambio"

# Subir a GitHub
git push
```

---

## 🌿 Crear Rama de Desarrollo

Para trabajar en nuevas features:

```bash
# Crear y cambiar a rama development
git checkout -b development

# Hacer cambios...

# Commit
git add .
git commit -m "Add new feature"

# Subir rama
git push -u origin development
```

---

## 🔗 URLs Importantes

Después de crear el repo, tendrás:

- **Repo:** `https://github.com/TU_USUARIO/v2-frontend`
- **Clone HTTPS:** `https://github.com/TU_USUARIO/v2-frontend.git`
- **Clone SSH:** `git@github.com:TU_USUARIO/v2-frontend.git`
- **Issues:** `https://github.com/TU_USUARIO/v2-frontend/issues`
- **Pull Requests:** `https://github.com/TU_USUARIO/v2-frontend/pulls`

---

## 📊 Estadísticas del Proyecto

```
Total archivos:     21
Líneas de código:   ~10,000
Páginas PHP:        8
Archivos JS:        5
Sistema CSS:        1 (v0-theme.css)
Documentación:      README.md + este archivo
```

---

## 🎯 Resultado Final

Tu repositorio en GitHub mostrará:

```
v2-frontend
│
├── 📄 README.md (hermoso, con badges)
├── 📁 assets/
│   └── css/
│       └── v0-theme.css
├── 🎨 8 archivos *_v0.php (diseño moderno)
├── 📜 5 archivos .js (funcionalidad)
├── ⚙️ Archivos de configuración
└── 📝 Documentación completa
```

---

## 💡 Tips

1. **Badge del Proyecto:**
   Añade esto al README para mostrar stats:
   ```markdown
   ![GitHub repo size](https://img.shields.io/github/repo-size/TU_USUARIO/v2-frontend)
   ![GitHub stars](https://img.shields.io/github/stars/TU_USUARIO/v2-frontend)
   ![GitHub forks](https://img.shields.io/github/forks/TU_USUARIO/v2-frontend)
   ```

2. **Proteger rama main:**
   Settings > Branches > Add rule > `main` > Require pull request reviews

3. **GitHub Pages:**
   Si quieres demo online: Settings > Pages > Source: main branch

---

## ❓ Problemas Comunes

### Error: "remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/TU_USUARIO/v2-frontend.git
```

### Error: "Authentication failed"
- Usa Personal Access Token en lugar de password
- O configura SSH

### Error: "Updates were rejected"
```bash
git pull origin main --rebase
git push origin main
```

---

**¡Listo para subir a GitHub!** 🚀

Recuerda reemplazar `TU_USUARIO` con tu nombre de usuario real de GitHub.
