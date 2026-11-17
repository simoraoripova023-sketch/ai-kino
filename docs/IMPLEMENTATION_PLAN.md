# AI KINO 2.0 — IMPLEMENTATION PLAN & AUDIT

**Versiya:** 2.0 / MVP  
**Sana:** 2026-11-17  
**Holat:** Dastlabki rejalashtirish va arxitektura tasdiqlash

---

## 1. REPO AUDIT SUMMARY (MAVJUD HOLAT)

| Element | Holat | Izoh |
| :--- | :--- | :--- |
| **Ishchi papka** | `c:\Users\777\ai kino` | Bo'sh papka, `git init` bajarildi. |
| **Backend Framework** | Mavjud emas | Laravel 11.x o'rnatilishi rejalashtirildi (`/backend`). |
| **Frontend Framework** | Mavjud emas | Vue 3 + Vite + Pinia + TailwindCSS o'rnatilishi rejalashtirildi (`/frontend`). |
| **Node.js muhiti** | Mavjud (`v22.14.0`) | `npm.cmd` (10.9.2) orqali Vue 3 Mini App build qilinadi. |
| **PHP & Composer** | Tizimda global o'rnatilmagan | Windows winget (`PHP.PHP.8.3`) yoki portativ PHP + composer orqali ta'minlanadi. |
| **Git** | Mavjud (`2.55.0`) | Versiyalash boshlandi. |
| **Telegram Credentiallar** | Hozircha kiritilmagan | Tokenlar xavfsiz `.env` orqali boshqariladi; mock-based testlar yoziladi. |

---

## 2. EXISTING VS REQUIRED GAP-TABLE

| Funksiya / Komponent | Texnik Topshiriq (Talab) | Mavjud holat | Bajariladigan ish (Action Items) |
| :--- | :--- | :--- | :--- |
| **Monorepo tuzilmasi** | `/backend`, `/frontend`, `/docs`, `docker-compose.yml` | Bo'sh | Standart modulli monorepo strukturasi yaratiladi. |
| **Telegram initData Auth** | HMAC-SHA256 tekshiruv, 5 daqiqa max age, session/token | Yo'q | Laravel Auth moduli + Sanctum token generator yaratiladi. |
| **Kino Katalogi** | Qidiruv, janr, format, style, til filtrlari, pagination | Yo'q | Postgres migratsiyalar, Eloquent modellar, `/api/v1/works` endpoint. |
| **Asar Sahifasi** | Poster, tavsif, metadata, epizodlar, lock/price, treyler | Yo'q | Mini App film sahifasi, Vue komponentlari, API v1 detail endpoint. |
| **Bot Playback** | Bot orqali `copyMessage`/`sendVideo`, access check, protect_content | Yo'q | Laravel bot handler, webhook controller, playback dispatche. |
| **Telegram Stars (XTR)** | Invoice, pre_checkout, successful_payment, DB tranzaksiya | Yo'q | OrderPayments moduli, idempotent webhook, grant yaratish. |
| **Davom ettirish** | Oxirgi ochilgan epizod (`last_episode_id`) | Yo'q | `watch_progress` jadvali, progress controller va Mini App banneri. |
| **Admin Panel** | Kontent CRUD, upload session, narx, nashr, audit log | Yo'q | Filament yoki Laravel Admin API + Audit log xizmati. |
| **Analitika** | Tadbirlar (open, play, buy), konversiya, retention hisoboti | Yo'q | `analytics_events` jadvali va aggregation dashboard. |

---

## 3. POC CHECKLIST + BLOCKERS

### 3.1. PoC Sinovlari (Proof of Concept)
- [ ] **PoC-1: Telegram initData HMAC tekshiruvi** — Haqiqiy va soxta hashlar bilan xavfsizlik sinovi.
- [ ] **PoC-2: Telegram Bot Storage & Playback** — Admin botga yuborgan videoni yopiq omborga `copyMessage` qilish va foydalanuvchiga `protect_content=true` bilan uzatish.
- [ ] **PoC-3: Telegram Stars (XTR) to'lov oqimi** — `sendInvoice` (currency: XTR) -> `pre_checkout_query` (<10s javob) -> `successful_payment` -> atomik `access_grants` kiritish.
- [ ] **PoC-4: Mini App xavfsiz aloqasi** — CORS, CSP, no-secret leaks, raw initData uzatish.

### 3.2. Blockerlar va Ularning Yechimi
1. **Telegram Bot Token va Private Storage Channel ID:**
   - *Holat:* Real token hozircha mavjud emas.
   - *Yechim:* Barcha unit va feature testlar Telegram Bot API HTTP Mock (Faker / Http::fake) orqali to'liq qamrab olinadi; real credential berilgach darhol jonli tekshiriladi.
2. **PHP runtime muhiti:**
   - *Holat:* PHP CLI tizim PATH ro'yxatida yo'q.
   - *Yechim:* `winget install PHP.PHP.8.3` yoki portativ runtime o'rnatish orqali backend va migratsiyalar yurgiziladi.

---

## 4. ARCHITECTURE & DATA MODEL ADR (ADR-001)

### 4.1. Arxitektura qarori
- **Pattern:** Modular Monolith (Laravel 11 Backend + Vue 3 Frontend).
- **Ma'lumotlar bazasi:** PostgreSQL 16 (JSONB, bigserial/bigint, partial unique indexes).
- **Kesh va Navbat:** Redis (Webhook idempotentlik, video jo'natish queue, rate limiting).
- **Front-end:** Telegram WebApp JS SDK, Vue 3 Composition API, dark cinema UX.

### 4.2. Database Target ID Polimorfizmi haqida qaror
- `purchase_intents` va `access_grants` jadvallarida `target_type ('work'|'episode')` va `target_id (bigint)` ishlatiladi.
- Tranzaksiyalar darajasida va Service Layer'da qat'iy referential integrity ta'minlanadi; agar `target_type == 'work'` bo'lsa `works.id`, `target_type == 'episode'` bo'lsa `episodes.id` mavjudligi tekshiriladi.
- Stars valyutasi faqat butun son (`bigint`, `integer`) bo'ladi, float qat'iyan taqiqlangan.

---

## 5. BOSQICHLAR (MILESTONES & ROADMAP)

### Phase 0: Audit + PoC + Infratuzilma
- Loyiha skeletini shakllantirish: `/backend`, `/frontend`, `/docs`.
- Standart OpenAPI 3.1 spetsifikatsiyasi (`docs/openapi.yaml`).
- Docker Compose, `.env.example`, `.gitignore`.

### Phase 1: Foundation (Backend Core & Auth)
- PostgreSQL migratsiyalari (15 ta jadval).
- Telegram `initData` HMAC-SHA256 validator servisi va testlar.
- Telegram Webhook secret token tekshiruvi va Replay guard (`telegram_updates`).
- Rate limiting va xavfsizlik middleware.

### Phase 2: Catalog & Admin Content Workflow
- Genres, Works, Seasons, Episodes modellari va CRUD.
- Upload Sessions: vaqtinchalik token bilan Telegram botga video yuklash oqimi.
- Qoidalar: Multi-episode 1-qism bepul, bitta faylli film preview istisnosi.
- Vue 3 Mini App katalog ekranlari (qidiruv, filtr, janrlar, responsive layout).

### Phase 3: Playback & Bot Delivery
- Access decision servisi (`EntitlementService`).
- Playback dispatche: ombor kanalidan foydalanuvchiga xavfsiz video yuborish.
- Davom ettirish (`watch_progress`): oxirgi ochilgan qismni qayd qilish va tiklash.
- Bot xatoliklari fallbacki: 429 retry-after, bot blocked, private chat missing.

### Phase 4: Telegram Stars Payments (XTR)
- Purchase Intent yaratish va narxni snapshot qilish.
- Telegram Bot `sendInvoice`, `pre_checkout_query` va `successful_payment` handlerlari.
- Idempotent tranzaksiya: Charge ID bo'yicha yagona to'lov va bitta grant berilishi.
- Refund hodisasi va grantni bekor qilish.

### Phase 5: Analitika, Hardening & QA
- `analytics_events` tracking va admin dashboard ko'rsatkichlari.
- Xavfsizlik auditlari, PII maskalash, CSP headerlari.
- To'liq test to'plami (Unit, Feature, Mock E2E).
- `docs/TEST_REPORT.md` va `docs/OPERATIONS_RUNBOOK.md`.

---

## 6. QABUL QILISH MEZONLARI (ACCEPTANCE CRITERIA)
- **AC-01..AC-14** to'liq qoplanadi.
- Har bir bosqich test dalili va hisoboti bilan topshiriladi.
