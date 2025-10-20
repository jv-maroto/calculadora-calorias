@echo off
echo ========================================
echo  SUBIR PROYECTO A GITHUB
echo ========================================
echo.

REM Verificar que Git este instalado
git --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Git no esta instalado
    echo Descargalo de: https://git-scm.com/download/win
    pause
    exit /b
)

echo [1/4] Tu usuario de GitHub:
set /p USUARIO="Introduce tu usuario de GitHub: "

echo.
echo [2/4] Nombre del repositorio (sin espacios):
set /p REPO="Introduce nombre del repo (ej: calculadora-calorias): "

echo.
echo [3/4] Preparando archivos...
git status

echo.
echo [4/4] Conectando con GitHub...
echo.
echo URL del repositorio: https://github.com/%USUARIO%/%REPO%
echo.
echo IMPORTANTE: Debes crear el repositorio en GitHub PRIMERO
echo Ve a: https://github.com/new
echo - Nombre: %REPO%
echo - NO marques "Add a README file"
echo - Click en "Create repository"
echo.
pause

echo.
echo Conectando...
git remote add origin https://github.com/%USUARIO%/%REPO%.git

echo.
echo Subiendo archivos...
git push -u origin master

echo.
echo ========================================
echo  COMPLETADO
echo ========================================
echo.
echo Tu proyecto esta en:
echo https://github.com/%USUARIO%/%REPO%
echo.
pause
