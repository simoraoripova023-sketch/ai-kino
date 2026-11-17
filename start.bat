@echo off
echo ========================================================
echo        AI KINO 2.0 - TELEGRAM MINI APP + BOT
echo ========================================================
echo.

echo [1/2] Backend API serverini ishga tushirish (http://127.0.0.1:8000)...
start "AI Kino 2.0 - Backend API" cmd /k "cd /d %~dp0backend && php artisan serve --port=8000"

echo [2/2] Frontend Mini App dev serverini ishga tushirish (http://localhost:3000)...
start "AI Kino 2.0 - Frontend Mini App" cmd /k "cd /d %~dp0frontend && npm run dev"

echo.
echo ========================================================
echo [OK] Loyiha muvaffaqiyatli ishga tushirildi!
echo - Mini App:  http://localhost:3000
echo - REST API:  http://127.0.0.1:8000/api/v1/works
echo ========================================================
pause
