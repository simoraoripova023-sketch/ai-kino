# ADR-001: Ma'lumotlar modeli, Polimorfizm va Telegram Stars (XTR) Narxlash

**Holat:** Qabul qilindi  
**Sana:** 2026-11-17  
**Kontekst:** AI Kino 2.0 platformasida foydalanuvchilar butun bir asarni (`work`) yoki alohida qismini (`episode`) Telegram Stars orqali xarid qilishlari mumkin.

---

## 1. Qaror: Target ID Polimorfizmi va Referential Integrity

### Muammo:
`purchase_intents` va `access_grants` jadvallari ham `works`, ham `episodes` jadvallariga murojaat qilishi kerak. Oddiy xorijiy kalit (`FOREIGN KEY`) ikkita turli jadvalga birdan bog'lana olmaydi.

### Tanlangan Yechim:
- Jadvallarda `target_type VARCHAR(32)` (`work` yoki `episode`) va `target_id BIGINT` ustunlari ishlatiladi.
- Biznes qatlamida (`PaymentService` va `EntitlementService`) hamda DB tranzaksiyalarida referential integrity qat'iy kafolatlanadi.
- `target_type` faqat cheklangan qiymatlarni (`work`, `episode`) qabul qiladi.

---

## 2. Qaror: Valyuta va Narx saqlash qoidasi

### Muammo:
To'lovlarda kasrli (`FLOAT`, `DOUBLE`) sonlar ishlatilsa, yaxlitlash xatoliklari sababli balans yo'qotilishi xavfi paydo bo'ladi.

### Tanlangan Yechim:
- Telegram Stars (`XTR`) faqat musbat butun son (`BIGINT` / `INTEGER`) sifatida saqlanadi.
- `FLOAT` va `DOUBLE` tiplari to'lov va narx ustunlarida qat'iyan taqiqlanadi.
- Har bir `purchase_intent` yaratilgan paytdagi asar yoki qism narxi `amount_stars` ustuniga snapshot qilib olinadi; keyinchalik admin narxni o'zgartirsa ham, mavjud buyurtma narxi o'zgarmaydi.

---

## 3. Qaror: Ko'p qismli 1-qism va Bitta faylli film istisnosi

- Ko'p qismli serial yoki shourtlarda tomoshabinni jalb qilish uchun **1-qism har doim bepul**.
- Bitta faylli to'liq metrajli filmlarda esa **butun film bepul bo'lib ketmasligi** uchun preview istisnosi joriy etildi: agar format film bo'lsa va epizodlar soni 1 ta bo'lsa, u faqat alohida treyler orqali bepul tomosha qilinadi, asosiy to'liq film pullik bo'lib qoladi.
