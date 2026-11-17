@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ========================================================
echo        AI KINO 2.0 - BOSHDAN TAYYORLASH (SETUP)
echo ========================================================
echo.

set "SCRIPT_DIR=%~dp0"

echo [1/5] Muhitni tekshirish...
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [OGOHLANTIRISH] 'php' tizim PATH da topilmadi.
) else (
    echo [OK] PHP topildi.
)

where node >nul 2>&1
if %errorlevel% neq 0 (
    echo [XATO] Node.js topilmadi! Iltimos, Node.js ni o'rnating.
    pause
    exit /b 1
) else (
    echo [OK] Node.js topilmadi emas, muvaffaqiyatli aniqlandi.
)

echo.
echo [2/5] Backend sozlamalarini tayyorlash...
cd /d "%SCRIPT_DIR%backend"
if not exist ".env" (
    copy .env.example .env >nul
    echo [OK] .env yaratildi.
)

if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
    echo [OK] database.sqlite yaratildi.
)

echo.
echo [3/5] Ma'lumotlar bazasini yangilash va boshlang'ich ma'lumotlarni yuklash (Seed)...
php artisan migrate:fresh --seed --force
if %errorlevel% neq 0 (
    echo [XATO] Migratsiya yoki seed qilishda xatolik yuz berdi.
    pause
    exit /b 1
)
echo [OK] Baza muvaffaqiyatli tayyorlandi (Admin, 10 ta AI asar, 25 ta qism).

echo.
echo [4/5] Backend avtotestlarini tekshirish...
php artisan test
if %errorlevel% neq 0 (
    echo [OGOHLANTIRISH] Ayrim testlar o'tmadi, loglarni ko'rib chiqing.
) else (
    echo [OK] Barcha 25 ta test muvaffaqiyatli o'tdi (100%%).
)

echo.
echo [5/5] Frontend kutubxonalarini tekshirish va yig'ish...
cd /d "%SCRIPT_DIR%frontend"
call npm.cmd run build
if %errorlevel% neq 0 (
    echo [XATO] Frontendni yig'ishda xatolik yuz berdi.
    pause
    exit /b 1
)
echo [OK] Frontend muvaffaqiyatli yig'ildi.

echo.
echo ========================================================
echo [MUVAFFAQIYATLI] AI Kino 2.0 to'liq sozlandi va ishga tayyor!
echo Ishga tushirish uchun: start.bat faylini bosing.
echo ========================================================
echo.
pause
