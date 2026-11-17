# AI KINO 2.0 — SECURITY SPECIFICATION & HARDENING

**Versiya:** 2.0 / MVP  
**Sana:** 2026-11-17  

---

## 1. Telegram initData Autentifikatsiyasi

- Mini App `Telegram.WebApp.initData` raw satrini HTTPS orqali yuboradi.
- Server `hash_hmac('sha256', $botToken, 'WebAppData', true)` kaliti yordamida HMAC-SHA256 imzosi to'g'riligini tekshiradi.
- `auth_date` maksimal yoshi 300 soniya (5 daqiqa) bilan cheklanadi; replay va eskirgan ma'lumotlar qabul qilinmaydi.
- Imzo tekshiruvi vaqt hujumlariga (timing attacks) qarshi `hash_equals` constant-time funksiyasi orqali bajariladi.

---

## 2. Webhook Xavfsizligi va Replay Guard

- Telegram Bot Webhook faqat `X-Telegram-Bot-Api-Secret-Token` headeri bilan qabul qilinadi.
- Har bir kiruvchi hodisa `telegram_updates` jadvalida ro'yxatga olinadi; takroriy `update_id` kelganda avtomatik e'tiborsiz qoldiriladi (`duplicate_ignored`).

---

## 3. To'lov Idempotentligi

- `successful_payment` kelganda `telegram_payment_charge_id` bo'yicha global unique tekshiruv va database row-lock ishlatiladi.
- Bitta to'lov uchun ikki marta `access_grants` yaratilmaydi.

---

## 4. Media va Kontent Himoyasi

- Telegram bot orqali video uzatishda `protect_content=true` parametri uzatiladi; foydalanuvchilar videoni boshqa kanallarga forward qila olmaydi yoki mahalliy yuklab olish tugmasi ko'rinmaydi.
- Yopiq ombor kanali identifikatorlari (`storage_chat_id`, `storage_message_id`) va haqiqiy `file_id` Mini App klientiga hech qachon oshkor qilinmaydi.

---

## 5. PII va Maxfiylik

- Foydalanuvchilarning shaxsiy telefon raqamlari, bank kartalari yoki pasport ma'lumotlari talab qilinmaydi va saqlanmaydi.
- To'lovlar to'liq Telegram ichki Stars infratuzilmasi orqali amalga oshiriladi.
