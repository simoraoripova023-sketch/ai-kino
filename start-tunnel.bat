@echo off
chcp 65001 >nul

echo ========================================================
echo   AI KINO 2.0 - TELEGRAM MINI APP HTTPS TUNNEL (LOCAL)
echo ========================================================
echo.
echo Bu skript sizning lokal ilovangizni (port 3000) internetga
echo xavfsiz HTTPS orqali chiqaradi, shunda uni bevosita Telegram
echo ichida (BotFather orqali) sinab ko'rishingiz mumkin bo'ladi.
echo.
echo Quyida ko'rsatilgan havolani (masalan: https://xxxxx.loca.lt)
echo nusxalab olib, @BotFather ga Mini App URL sifatida bering!
echo ========================================================
echo.

npx localtunnel --port 3000
pause
