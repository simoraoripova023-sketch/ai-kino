# AI KINO 2.0 🎬

Faqat sun'iy intellekt (AI) yordamida yaratilgan film, serial va multfilmlarni tomosha qilish uchun **Telegram Mini App + Bot** platformasi.

---

## 🚀 Texnologik Stack

- **Backend:** Laravel 11.x (PHP 8.3), Sanctum (API Tokens), Eloquent ORM
- **Frontend:** Vue 3 (Composition API), Vite, Pinia, TailwindCSS, Telegram WebApp SDK
- **Ma'lumotlar bazasi:** PostgreSQL 16+ (15 ta normalizatsiya qilingan jadval)
- **To'lov tizimi:** Telegram Stars (`XTR` digital goods)
- **Xavfsizlik:** HMAC-SHA256 initData validatsiyasi, Webhook replay guard, `protect_content=true` video yetkazish

---

## 📁 Loyiha Strukturasi

```
/backend              # Laravel API, Telegram Bot handlerlari, migratsiyalar, testlar
/frontend             # Vue 3 Telegram Mini App interfeysi
/docs
  ├── IMPLEMENTATION_PLAN.md   # Bosqichma-bosqich reja va bajarilgan ishlar
  ├── ARCHITECTURE.md          # Arxitektura va foydalanuvchi oqimlari
  ├── ADR-001-data-model.md    # Ma'lumotlar modeli va polimorfizm qarorlari
  ├── openapi.yaml             # OpenAPI 3.1 REST API spetsifikatsiyasi
  ├── DATABASE.md              # 15 ta jadval sxemasi va indekslar
  ├── SECURITY.md              # Xavfsizlik va HMAC tekshiruv qoidalari
  ├── TEST_REPORT.md           # 22 ta test hisoboti (100% Pass)
  └── OPERATIONS_RUNBOOK.md    # O'rnatish, ishga tushirish va monitoring yo'riqnomasi
/docker-compose.yml
/.env.example
/README.md
```

---

## ⚡ Tezkor Ishga Tushirish

### 1. Backend
```bash
cd backend
php artisan migrate:fresh --seed
php artisan serve
```

### 2. Frontend
```bash
cd frontend
npm install
npm run dev
```

---

## 🧪 Sinovlar va Sifat Kafolati

```bash
cd backend
php artisan test
```
*Natija: 22 ta Unit & Feature testlar (84 ta assertion) muvaffaqiyatli o'tgan.*

```bash
cd frontend
npm run build
```
*Natija: Production to'plami 1.2 soniyada xatosiz yig'ildi.*
