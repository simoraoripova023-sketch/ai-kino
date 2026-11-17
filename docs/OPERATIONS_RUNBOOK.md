# AI KINO 2.0 — OPERATIONS & DEPLOYMENT RUNBOOK

---

## 1. Lokal Ishga Tushirish (Local Setup)

### 1.1. Backend (Laravel API & Bot)
```bash
cd backend
# Muhit faylini yaratish
copy .env.example .env

# Migratsiyalarni yurgizish va 10 ta namuna AI asarni kiritish
php artisan migrate:fresh --seed

# API serverni ishga tushirish (port 8000)
php artisan serve
```

### 1.2. Frontend (Vue 3 Telegram Mini App)
```bash
cd frontend
# Bog'liqliklarni o'rnatish
npm install

# Dev serverni ishga tushirish (port 3000)
npm run dev

# Production build tayyorlash
npm run build
```

---

## 2. Telegram Bot & Webhook Sozlash

1. Telegramda `@BotFather` orqali yangi bot yarating yoki mavjud bot tokenini oling.
2. Bot menyusida Mini App URL manzilini ko'rsating: `/newapp` -> WebApp URL: `https://sizning-domeningiz.uz`.
3. Webhookni HTTPS orqali ro'yxatdan o'tkazing:
```bash
curl -X POST "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url": "https://sizning-domeningiz.uz/api/v1/telegram/webhook", "secret_token": "<TELEGRAM_WEBHOOK_SECRET>"}'
```
4. Ombordagi video kanali (`storage_chat_id`): Botni yopiq kanalga admin qilib qo'shing.

---

## 3. Database Backup & Restore

### Backup olish:
```bash
pg_dump -U postgres -d aikino -F c -b -v -f /backups/aikino_$(date +%Y%m%d_%H%M%S).dump
```

### Tiklash (Restore drill):
```bash
pg_restore -U postgres -d aikino -v /backups/aikino_backup.dump
```

---

## 4. Telegram Stars To'lovlarni Qaytarish (Refunds) & Reconciliation

1. Foydalanuvchi to'lovni qaytarishni so'raganda, Telegram Bot API `refundStarPayment` chaqiriladi:
```bash
curl -X POST "https://api.telegram.org/bot<BOT_TOKEN>/refundStarPayment" \
     -d "user_id=<USER_TELEGRAM_ID>&telegram_payment_charge_id=<CHARGE_ID>"
```
2. Muvaffaqiyatli refund bo'lgach, serverda `Payment` statusi `refunded`ga o'tkaziladi va tegishli `AccessGrant` `revoked` qilinadi.
