# AI KINO 2.0 🎬

Faqat sun'iy intellekt (AI) yordamida yaratilgan film, serial va multfilmlarni tomosha qilish uchun **Telegram Mini App + Bot** platformasi.

---

## 🚀 Texnologik Stack

- **Backend:** Laravel 11.x (PHP 8.3), Sanctum (API Tokens), Eloquent ORM
- **Frontend:** Vue 3 (Composition API), Vite 6, Pinia, TailwindCSS, Telegram WebApp SDK
- **Lokalizatsiya:** O'zbekcha (uz) va Ruscha (ru) to'liq reaktiv i18n
- **Ma'lumotlar bazasi:** SQLite / PostgreSQL (15 ta normalizatsiya qilingan jadval)
- **To'lov tizimi:** Telegram Stars (`XTR` digital goods)
- **Xavfsizlik:** HMAC-SHA256 initData validatsiyasi, Webhook replay guard, `protect_content=true` video yetkazish
- **Boshqaruv:** To'liq Web Admin Paneli (`/admin`) va Rol almashtirish (Sandbox)

---

## ⚡ Bir marta bosishda ishga tushirish (Turnkey Quick Start)

### 1. Boshdan tayyorlash (Setup):
Loyihaning ildiz papkasidagi `setup.bat` faylini ishga tushiring:
```cmd
setup.bat
```
Bu skript:
* PHP va Node.js ni tekshiradi;
* Ma'lumotlar bazasini yangilab, Admin foydalanuvchi, 10 ta AI film/serial va 25 ta epizodni seed qiladi;
* Barcha 25 ta avtotestlarni ishga tushirib tekshiradi;
* Frontendni ishlab chiqarishga yig'adi.

### 2. Loyihani ishga tushirish (Start):
```cmd
start.bat
```
* **Foydalanuvchi interfeysi (Mini App):** [http://localhost:3000](http://localhost:3000)
* **Boshqaruv paneli (Admin Panel):** [http://localhost:3000/admin](http://localhost:3000/admin)
* **Backend REST API:** [http://127.0.0.1:8000/api/v1/works](http://127.0.0.1:8000/api/v1/works)

### 3. Telegramda haqiqiy Mini App sifatida sinash:
```cmd
start-tunnel.bat
```
Berilgan `https://xxxx.loca.lt` havolasini `@BotFather` ga Mini App URL qilib berishingiz kifoya!

---

## 👑 Admin Paneli va Rolni Almashtirish (Sandbox Mode)

Brauzerda test qilganda Telegram hisobingiz bo'lmasa ham tizimdan to'liq foydalanish mumkin:
1. Brauzerda [http://localhost:3000/profile](http://localhost:3000/profile) ga kiring.
2. **Sandbox test rejimi** bo'limida 1 marta bosish orqali rolni almashtiring:
   - **👤 Oddiy Tomoshabin (ID: 123456789):** Epizodlarni tomosha qilish, Stars orqali xarid qilish, davom ettirish ro'yxati.
   - **👑 Bosh Administrator (ID: 999999999):** Barcha asarlarni boshqarish (nashr qilish, yashirish, yangi asar qo'shish), Stars tushumlarini ko'rish, audit loglarini kuzatish.

---

## 📁 Loyiha Strukturasi

```
/backend              # Laravel 11 API, Telegram Bot xizmatlari, migratsiyalar, testlar
/frontend             # Vue 3 Telegram Mini App interfeysi va Admin paneli
/docs
  ├── IMPLEMENTATION_PLAN.md   # Bosqichma-bosqich reja va bajarilgan ishlar
  ├── ARCHITECTURE.md          # Arxitektura va foydalanuvchi oqimlari
  ├── ADR-001-data-model.md    # Ma'lumotlar modeli va polimorfizm qarorlari
  ├── openapi.yaml             # OpenAPI 3.1 REST API spetsifikatsiyasi
  ├── DATABASE.md              # 15 ta jadval sxemasi va indekslar
  ├── SECURITY.md              # Xavfsizlik va HMAC tekshiruv qoidalari
  ├── TEST_REPORT.md           # 25 ta test hisoboti (100% Pass)
  └── OPERATIONS_RUNBOOK.md    # O'rnatish, ishga tushirish va monitoring yo'riqnomasi
/start.bat            # API va Frontendni bir vaqtda ishga tushirish
/setup.bat            # Baza, migratsiya, seed va testlarni tayyorlash
/start-tunnel.bat     # Telegram Mini App uchun HTTPS tunnel ochish
/docker-compose.yml
/README.md
```

---

## 🧪 Sinovlar va Sifat Kafolati

```bash
cd backend
php artisan test
```
*Natija: 25 ta Unit & Feature testlar (114 ta assertion) 100% muvaffaqiyatli o'tgan.*

```bash
cd frontend
npm run build
```
*Natija: Production to'plami 2 soniyada xatosiz yig'ildi.*
