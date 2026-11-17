# AI KINO 2.0 — TEST EXECUTION REPORT

**Sana:** 2026-11-17  
**Holat:** Barcha testlar muvaffaqiyatli o'tdi (100% Pass)  
**Muhit:** PHP 8.3.36, Laravel 11.x, Node.js 22.14.0, SQLite (in-memory test runner)

---

## 1. Testlar Xulosasi

| To'plam (Test Suite) | Testlar soni | Tasdiqlar (Assertions) | Natija |
| :--- | :--- | :--- | :--- |
| **TelegramInitDataValidatorTest** | 4 | 10 | PASSED |
| **EntitlementServiceTest** | 3 | 11 | PASSED |
| **PaymentServiceTest** | 3 | 14 | PASSED |
| **TelegramBotServiceTest** | 4 | 11 | PASSED |
| **ApiEndpointsTest (Feature)** | 6 | 36 | PASSED |
| **AdminContentWorkflowTest (Feature)** | 3 | 30 | PASSED |
| **Laravel Default Tests** | 2 | 2 | PASSED |
| **JAMI (Backend)** | **25** | **114** | **100% PASSED** |
| **Frontend Production Build** | Vite v6.4.6 | 37 modules | **SUCCESS (1.20s)** |

---

## 2. Admin & Content Moderation Sinovlari (Yangi)

### 2.1. AdminContentWorkflowTest
- `test_non_admin_cannot_access_admin_routes`: Oddiy foydalanuvchi `/admin/*` yo'llariga murojaat qilganda `403 ADMIN_ACCESS_REQUIRED` bilan to'xtatilishi tekshirildi.
- `test_admin_work_creation_and_publish_validation`:
  1. Admin asarni `draft` holatida yaratishi.
  2. Qismlarsiz nashr etishga uringanda `422 NO_HEALTHY_EPISODES` xatosi bilan to'xtatilishi.
  3. Upload session (`/upload <uuid>`) yaratilishi.
  4. Telegram bot orqali video yuklanganda (`copyMessage` yordamida omborga nusxalanishi, epizod yaratilishi va upload session `completed` bo'lishi) tekshirildi.
  5. Asar to'liq muvaffaqiyatli nashr etilib, `admin_audit_logs`ga yozilishi tasdiqlandi.
- `test_admin_analytics_overview`: DAU, MAU, gross Stars tushumi, tranzaksiyalar va pullik konversiya ko'rsatkichlari qaytishi tekshirildi.
