@echo off
chcp 65001 >nul
cls
color 0A

echo.
echo ═══════════════════════════════════════════════════════════════
echo   🚀 SUBIR PROYECTO A GITHUB - AUTOMÁTICO
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Este script te guiará paso a paso para subir tu proyecto
echo.
echo ═══════════════════════════════════════════════════════════════
echo.
pause

cls
echo.
echo ═══════════════════════════════════════════════════════════════
echo   PASO 1: CREAR REPOSITORIO EN GITHUB
echo ═══════════════════════════════════════════════════════════════
echo.
echo   1. Abre este enlace en tu navegador:
echo      https://github.com/new
echo.
echo   2. Configura así:
echo      • Repository name: calculadora-calorias
echo      • Description: Sistema avanzado de cálculo de calorías
echo      • Public (marcado)
echo      • ❌ NO marcar "Add a README file"
echo      • ❌ NO marcar ".gitignore"
echo.
echo   3. Click en "Create repository"
echo.
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Abriendo navegador en 3 segundos...
timeout /t 3 >nul
start https://github.com/new
echo.
echo   ✅ Navegador abierto
echo.
pause

cls
echo.
echo ═══════════════════════════════════════════════════════════════
echo   PASO 2: INTRODUCE TUS DATOS
echo ═══════════════════════════════════════════════════════════════
echo.
set /p USUARIO="   👤 Tu usuario de GitHub: "

if "%USUARIO%"=="" (
    echo.
    echo   ❌ ERROR: Debes introducir tu usuario
    pause
    exit /b
)

echo.
set /p REPO="   📦 Nombre del repositorio [calculadora-calorias]: "

if "%REPO%"=="" (
    set REPO=calculadora-calorias
)

cls
echo.
echo ═══════════════════════════════════════════════════════════════
echo   PASO 3: CONFIGURACIÓN
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Usuario: %USUARIO%
echo   Repositorio: %REPO%
echo   URL: https://github.com/%USUARIO%/%REPO%
echo.
echo ═══════════════════════════════════════════════════════════════
echo.
set /p CONFIRMAR="   ¿Es correcto? (S/N): "

if /i not "%CONFIRMAR%"=="S" (
    echo.
    echo   ❌ Cancelado. Ejecuta el script de nuevo.
    pause
    exit /b
)

cls
echo.
echo ═══════════════════════════════════════════════════════════════
echo   PASO 4: SUBIENDO A GITHUB
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Conectando con GitHub...
echo.

git remote add origin https://github.com/%USUARIO%/%REPO%.git

if errorlevel 1 (
    echo.
    echo   ⚠️  Ya existe un remote, eliminando el anterior...
    git remote remove origin
    git remote add origin https://github.com/%USUARIO%/%REPO%.git
)

echo   ✅ Remote configurado
echo.
echo   Renombrando rama a 'master'...
git branch -M master
echo   ✅ Rama renombrada
echo.
echo   Subiendo archivos a GitHub...
echo   (Te puede pedir usuario y contraseña)
echo.
echo ═══════════════════════════════════════════════════════════════
echo   IMPORTANTE: Si te pide contraseña, usa Personal Access Token
echo   No uses tu contraseña normal de GitHub
echo.
echo   Crear token en: https://github.com/settings/tokens
echo ═══════════════════════════════════════════════════════════════
echo.

git push -u origin master

if errorlevel 1 (
    echo.
    echo ═══════════════════════════════════════════════════════════════
    echo   ❌ ERROR AL SUBIR
    echo ═══════════════════════════════════════════════════════════════
    echo.
    echo   Posibles causas:
    echo   1. No creaste el repositorio en GitHub primero
    echo   2. Autenticación fallida (necesitas Personal Access Token)
    echo   3. El repositorio ya existe con contenido
    echo.
    echo   Soluciones:
    echo   • Verifica que creaste el repo en: https://github.com/%USUARIO%/%REPO%
    echo   • Crea un Personal Access Token en: https://github.com/settings/tokens
    echo   • Ejecuta este script de nuevo
    echo.
    echo ═══════════════════════════════════════════════════════════════
    pause
    exit /b
)

cls
echo.
echo ═══════════════════════════════════════════════════════════════
echo   ✅ ¡ÉXITO! PROYECTO SUBIDO A GITHUB
echo ═══════════════════════════════════════════════════════════════
echo.
echo   Tu proyecto está en:
echo   🌐 https://github.com/%USUARIO%/%REPO%
echo.
echo   Archivos subidos:
git ls-files | find /c /v ""
echo.
echo ═══════════════════════════════════════════════════════════════
echo   PRÓXIMOS PASOS
echo ═══════════════════════════════════════════════════════════════
echo.
echo   1. Visita tu repositorio:
echo      https://github.com/%USUARIO%/%REPO%
echo.
echo   2. Verifica que se vea el README.md
echo.
echo   3. Añade topics (etiquetas):
echo      nutrition, fitness, calories, php, javascript, mysql
echo.
echo   4. Comparte el enlace con quien quieras
echo.
echo ═══════════════════════════════════════════════════════════════
echo.
echo   ¿Quieres abrir tu repositorio en el navegador?
set /p ABRIR="   (S/N): "

if /i "%ABRIR%"=="S" (
    start https://github.com/%USUARIO%/%REPO%
    echo.
    echo   ✅ Abriendo navegador...
)

echo.
echo   ¡Gracias por usar este script!
echo.
pause
