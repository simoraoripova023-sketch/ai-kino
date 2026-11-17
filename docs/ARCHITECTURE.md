# AI KINO 2.0 — ARCHITECTURE DOCUMENTATION

**Versiya:** 2.0 / MVP  
**Pattern:** Modular Monolith  
**Backend:** Laravel 11.x (PHP 8.3)  
**Frontend:** Vue 3 + Vite + Pinia + TailwindCSS (Telegram Mini App)  
**Database:** PostgreSQL 16+ (Local SQLite for fast in-memory CI/tests)  
**Queue & Cache:** Redis

---

## 1. Modullar va Mas'uliyatlar

```
┌──────────────────────────────────────────────────────────────────┐
│                     Telegram Mini App (Vue 3)                    │
│  - Telegram WebApp SDK (initData, haptics, safe areas)          │
│  - Catalog / Filter / Search / Movie detail / Episodes           │
│  - Purchase modal & Playback trigger                             │
└────────────────┬─────────────────────────────────────────────────┘
                 │ HTTPS (REST API v1 + Bearer Token)
                 ▼
┌──────────────────────────────────────────────────────────────────┐
│                      Laravel 11 Backend                          │
│                                                                  │
│  [Auth Module]                                                   │
│    - TelegramInitDataValidator (HMAC-SHA256, 5 min expiry)       │
│    - Sanctum Personal Access Tokens                              │
│                                                                  │
│  [Catalog Module]                                                │
│    - Works, Genres, Seasons, Episodes                            │
│    - Status & publish scheduling filters                         │
│                                                                  │
│  [Entitlements Module]                                           │
│    - Free 1st episode rule (multi-episode)                       │
│    - Single-file paid movie preview exception                     │
│    - Access decision (free / owned / locked)                     │
│                                                                  │
│  [Payment & Stars Module]                                        │
│    - Purchase intent with snapshot price                         │
│    - Telegram Stars (XTR) invoices                               │
│    - Pre-checkout validation (< 10 seconds)                      │
│    - Atomic successful_payment processing                        │
│                                                                  │
│  [Telegram Bot Integration]                                      │
│    - Webhook secret verification (X-Telegram-Bot-Api-Secret)     │
│    - Replay guard & idempotency (telegram_updates)               │
│    - copyMessage(protect_content=true) video delivery            │
│    - 429 rate limit retry-after & blocked bot handling           │
│                                                                  │
│  [Progress & Analytics Module]                                   │
│    - Continue watching (last_episode_id tracking)                │
│    - Event logging (open, view, delivery, payment)               │
└────────────────┬───────────────────────────────┬─────────────────┘
                 │                               │
                 ▼                               ▼
       PostgreSQL / SQLite Database          Telegram Bot API
       (15 normalized tables)               (Webhooks / Telegram Stars)
```

---

## 2. Foydalanuvchi Oqimlari (User Flows)

### 2.1. Autentifikatsiya Oqimi
1. Foydalanuvchi Telegram Mini Appni ochadi.
2. Frontend `Telegram.WebApp.initData` (raw query string) oladi.
3. `POST /api/v1/auth/telegram` chaqiriladi.
4. Backend bot token orqali HMAC-SHA256 tekshiruvini amalga oshiradi, `auth_date` 5 daqiqadan oshmaganligini tasdiqlaydi.
5. Foydalanuvchi bazada yangilanadi va Sanctum Bearer token qaytariladi.

### 2.2. Tomosha va Access Check Oqimi
1. Foydalanuvchi epizodni tanlaydi (`POST /api/v1/episodes/{id}/play`).
2. `EntitlementService` quyidagilarni tekshiradi:
   - Ko'p qismli asarning 1-qismimi? -> Ruxsat etiladi (bepul).
   - Bir qismli to'liq filmmi? -> Bepul emas (pullik).
   - Foydalanuvchida `access_grants` bormi? -> Ruxsat etiladi (owned).
3. Agar ruxsat bo'lmasa -> `403 PAYMENT_REQUIRED` bilan Stars narxi va target_type qaytariladi.
4. Agar ruxsat bo'lsa -> Telegram Bot orqali ombordan `copyMessage(protect_content=true)` bilan foydalanuvchiga uzatiladi.
5. `watch_progress` (last_episode_id) bazada yangilanadi.

### 2.3. Telegram Stars (XTR) To'lov Oqimi
1. Foydalanuvchi `POST /api/v1/purchase-intents` orqali to'lov niyatini bildiradi.
2. Backend narxni snapshot qiladi va Telegram chatga Stars invoice yuboradi.
3. Foydalanuvchi Stars to'lovini tasdiqlaydi.
4. Telegram `pre_checkout_query` yuboradi -> Backend 10 soniya ichida tekshirib `ok: true` beradi.
5. Telegram `successful_payment` yuboradi -> DB tranzaksiya orqali Payment yoziladi, `access_grants` beriladi.
6. Webhook takror yuborilsa ham, charge ID bo'yicha idempotency ta'minlanadi.
