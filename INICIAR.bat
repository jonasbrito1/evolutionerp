@echo off
REM ================================================================
REM Evolution CRM - Auto Start Script
REM ================================================================

cls
color 0A
echo.
echo ================================================================
echo   Evolution CRM - Sistema de Gerenciamento de Licitacoes
echo ================================================================
echo.

REM Kill any existing processes
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
taskkill /F /IM npm.exe >nul 2>&1

echo [1/4] Preparando ambiente...
timeout /t 1 >nul

echo [2/4] Iniciando Backend (Laravel) na porta 8000...
cd /d "%~dp0backend"
start "Evolution CRM - Backend" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 2 >nul

echo [3/4] Iniciando Frontend (Vue.js) na porta 5173...
cd /d "%~dp0frontend"
start "Evolution CRM - Frontend" cmd /k "npm run dev"
timeout /t 2 >nul

echo [4/4] Abrindo navegador...
timeout /t 3 >nul
start http://localhost:5173

echo.
echo ================================================================
echo   Sistema iniciado com sucesso!
echo ================================================================
echo.
echo Frontend:  http://localhost:5173
echo Backend:   http://localhost:8000/api
echo.
echo Credenciais de Demo:
echo   Email: admin@licitaevolution.local
echo   Senha: admin123456
echo.
echo ================================================================
echo.

pause
