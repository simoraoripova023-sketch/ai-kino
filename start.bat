@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ========================================================
echo        AI KINO 2.0 - TELEGRAM MINI APP + BOT MVP
echo ========================================================
echo.

set "SCRIPT_DIR=%~dp0"

echo [1/2] Backend API serverini ishga tushirish (http://127.0.0.1:8000)...
start "AI Kino 2.0 - Backend API" cmd /k "cd /d "%SCRIPT_DIR%backend" && php artisan serve --port=8000"

echo [2/2] Frontend Mini App dev serverini ishga tushirish (http://localhost:3000)...
start "AI Kino 2.0 - Frontend Mini App" cmd /k "cd /d "%SCRIPT_DIR%frontend" && npm run dev"

echo.
echo ========================================================
echo [MUVAFFAQIYATLI] AI Kino 2.0 ishga tushirildi!
echo.
echo - Foydalanuvchi interfeysi: http://localhost:3000
echo - Boshqaruv paneli (Admin): http://localhost:3000/admin
echo - Backend REST API:        http://127.0.0.1:8000/api/v1/works
echo.
echo Telegramda test qilish uchun: start-tunnel.bat ni ishga tushiring
echo ========================================================
echo.
pause
