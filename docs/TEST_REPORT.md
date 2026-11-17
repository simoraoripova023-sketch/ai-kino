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
| **Laravel Default Tests** | 2 | 2 | PASSED |
| **JAMI (Backend)** | **22** | **84** | **100% PASSED** |
| **Frontend Production Build** | Vite v6.4.6 | 37 modules | **SUCCESS (1.20s)** |

---

## 2. Tafsilotlar

### 2.1. TelegramInitDataValidatorTest
- `test_valid_init_data_passes_verification`: To'g'ri HMAC-SHA256 imzosi bilan autentifikatsiyadan o'tish tekshirildi.
- `test_tampered_init_data_fails_with_invalid_hash`: O'zgartirilgan ma'lumot 401 xatosi berishi tasdiqlandi.
- `test_expired_init_data_fails`: 5 daqiqadan oshgan initData rad etildi.
- `test_missing_hash_fails`: Hashsiz so'rovlar to'xtatildi.

### 2.2. EntitlementServiceTest
- `test_multi_episode_series_first_episode_is_free`: Ko'p qismli serialning 1-qismi bepul ochilishi, 2-qismi esa qulflanishi isbotlandi.
- `test_single_episode_paid_film_is_not_free`: Bir qismli to'liq film bepul bo'lib ketmasligi (pullik qolishi) tasdiqlandi.
- `test_user_with_work_grant_has_access_to_all_episodes`: Butun asarni sotib olgan foydalanuvchiga barcha qismlar ochilishi tekshirildi.

### 2.3. PaymentServiceTest
- `test_create_intent_snapshots_price`: To'lov niyati yaratilganda narx snapshot qilib saqlanishi tekshirildi.
- `test_pre_checkout_validation`: Telegram `pre_checkout_query` narxi, foydalanuvchisi va muddati tekshirildi.
- `test_successful_payment_idempotency_and_grant`: Takroriy charge ID kelganda ikkinchi to'lov yoki ikkinchi grant yozilmasligi (idempotency) isbotlandi.

### 2.4. TelegramBotServiceTest
- `test_record_update_prevents_duplicate_replays`: Webhook duplicate replay himoyasi tekshirildi.
- `test_deliver_video_calls_copy_message_with_protect_content`: Telegram omboridan `protect_content=true` bilan uzatilishi tasdiqlandi.
- `test_deliver_video_handles_429_rate_limit`: Telegram 429 xatosi va retry-after vaqti qabul qilinishi tekshirildi.
- `test_send_stars_invoice_uses_xtr_and_empty_provider`: XTR valyutasi va bo'sh provider_token bilan invoice jo'natilishi tekshirildi.

### 2.5. ApiEndpointsTest (Feature)
- `test_get_genres_returns_active_genres`: 5 ta aktiv janr qaytishi tekshirildi.
- `test_get_works_filtering_by_format`: Format bo'yicha filtrlash tekshirildi.
- `test_get_work_detail_returns_episodes_with_access_state`: Epizodlar bilan asar detallari tekshirildi.
- `test_play_locked_episode_returns_403_payment_required`: Qulflangan qism uchun 403 qaytishi tekshirildi.
- `test_play_free_episode_delivers_video_and_updates_progress`: Bepul qismni ochish va `watch_progress`ni yangilash tekshirildi.
- `test_purchase_intent_and_webhook_payment_flow`: To'liq to'lov oqimi (intent -> pre_checkout -> successful_payment -> grant) integratsion tarzda sinovdan o'tdi.
