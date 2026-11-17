# AI KINO 2.0 — DATABASE SCHEMA DESIGN

**RDBMS:** PostgreSQL 16+  
**Valyuta:** Telegram Stars (`XTR`), faqat musbat `BIGINT` (float qat'iyan taqiqlangan)  
**Vaqt:** Barcha timestamps UTC (`TIMESTAMPTZ`)

---

## 1. Jadvallar tuzilmasi (15 ta asosiy jadval)

### 1. `users`
- `id`: BIGSERIAL PRIMARY KEY
- `telegram_id`: BIGINT UNIQUE NOT NULL
- `username`: VARCHAR(255) NULL
- `first_name`: VARCHAR(255) NOT NULL
- `language`: VARCHAR(2) DEFAULT 'uz' NOT NULL
- `is_blocked`: BOOLEAN DEFAULT FALSE NOT NULL
- `last_seen_at`: TIMESTAMPTZ NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL

### 2. `genres`
- `id`: BIGSERIAL PRIMARY KEY
- `slug`: VARCHAR(64) UNIQUE NOT NULL
- `name_uz`: VARCHAR(255) NOT NULL
- `name_ru`: VARCHAR(255) NOT NULL
- `sort_order`: INT DEFAULT 0 NOT NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL

### 3. `works`
- `id`: BIGSERIAL PRIMARY KEY
- `title`: VARCHAR(255) NOT NULL
- `description`: TEXT NOT NULL
- `format`: VARCHAR(32) NOT NULL CHECK (format IN ('film', 'series', 'short'))
- `style`: VARCHAR(32) NOT NULL CHECK (style IN ('realistic', 'animation_2d', 'animation_3d', 'mixed'))
- `language`: VARCHAR(8) NOT NULL DEFAULT 'uz'
- `age_rating`: VARCHAR(8) NOT NULL CHECK (age_rating IN ('0+', '6+', '12+', '16+'))
- `poster_path`: VARCHAR(512) NOT NULL
- `cover_path`: VARCHAR(512) NULL
- `trailer_file_id`: VARCHAR(255) NULL
- `status`: VARCHAR(32) DEFAULT 'draft' NOT NULL CHECK (status IN ('draft', 'published', 'hidden'))
- `pricing_mode`: VARCHAR(32) NOT NULL CHECK (pricing_mode IN ('free', 'work', 'episode'))
- `price_stars`: BIGINT NULL CHECK (price_stars IS NULL OR price_stars > 0)
- `published_at`: TIMESTAMPTZ NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL
- **Indekslar:** `(status, published_at)`, `(format, style)`

### 4. `work_genre`
- `work_id`: BIGINT REFERENCES works(id) ON DELETE CASCADE
- `genre_id`: BIGINT REFERENCES genres(id) ON DELETE CASCADE
- PRIMARY KEY (`work_id`, `genre_id`)

### 5. `seasons`
- `id`: BIGSERIAL PRIMARY KEY
- `work_id`: BIGINT REFERENCES works(id) ON DELETE CASCADE
- `number`: INT NOT NULL
- `title`: VARCHAR(255) NULL
- UNIQUE (`work_id`, `number`)

### 6. `episodes`
- `id`: BIGSERIAL PRIMARY KEY
- `work_id`: BIGINT REFERENCES works(id) ON DELETE CASCADE
- `season_id`: BIGINT NULL REFERENCES seasons(id) ON DELETE SET NULL
- `number`: INT NOT NULL
- `title`: VARCHAR(255) NULL
- `duration_sec`: INT DEFAULT 0 NOT NULL
- `file_id`: VARCHAR(255) NOT NULL
- `file_unique_id`: VARCHAR(255) NULL
- `storage_chat_id`: BIGINT NOT NULL
- `storage_message_id`: BIGINT NOT NULL
- `status`: VARCHAR(32) DEFAULT 'draft' NOT NULL CHECK (status IN ('draft', 'published', 'hidden'))
- `publish_at`: TIMESTAMPTZ NULL
- `is_preview`: BOOLEAN DEFAULT FALSE NOT NULL
- `price_stars`: BIGINT NULL CHECK (price_stars IS NULL OR price_stars > 0)
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL
- **Indekslar:** 
  - `(work_id, season_id, number)` where season_id IS NOT NULL UNIQUE
  - `(work_id, number)` where season_id IS NULL UNIQUE (Partial unique index)
  - `(status, publish_at)`

### 7. `upload_sessions`
- `id`: UUID PRIMARY KEY
- `admin_user_id`: BIGINT NOT NULL
- `work_id`: BIGINT REFERENCES works(id) ON DELETE CASCADE
- `season_id`: BIGINT NULL REFERENCES seasons(id) ON DELETE SET NULL
- `status`: VARCHAR(32) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'completed', 'expired'))
- `expires_at`: TIMESTAMPTZ NOT NULL
- `used_at`: TIMESTAMPTZ NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL

### 8. `purchase_intents`
- `id`: UUID PRIMARY KEY
- `user_id`: BIGINT REFERENCES users(id) ON DELETE CASCADE
- `target_type`: VARCHAR(32) NOT NULL CHECK (target_type IN ('work', 'episode'))
- `target_id`: BIGINT NOT NULL
- `amount_stars`: BIGINT NOT NULL CHECK (amount_stars > 0)
- `currency`: VARCHAR(8) DEFAULT 'XTR' NOT NULL
- `status`: VARCHAR(32) DEFAULT 'pending' NOT NULL CHECK (status IN ('pending', 'paid', 'expired', 'cancelled', 'refunded'))
- `invoice_payload`: VARCHAR(255) UNIQUE NOT NULL
- `expires_at`: TIMESTAMPTZ NOT NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL
- **Indeks:** `(user_id, status)`

### 9. `payments`
- `id`: BIGSERIAL PRIMARY KEY
- `intent_id`: UUID UNIQUE REFERENCES purchase_intents(id) ON DELETE RESTRICT
- `user_id`: BIGINT REFERENCES users(id) ON DELETE CASCADE
- `telegram_payment_charge_id`: VARCHAR(255) UNIQUE NOT NULL
- `amount_stars`: BIGINT NOT NULL CHECK (amount_stars > 0)
- `currency`: VARCHAR(8) DEFAULT 'XTR' NOT NULL
- `paid_at`: TIMESTAMPTZ NOT NULL
- `status`: VARCHAR(32) DEFAULT 'paid' NOT NULL CHECK (status IN ('paid', 'refunded'))
- `raw_event_reference`: JSONB NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL

### 10. `telegram_updates` (Idempotency & Replay Guard)
- `update_id`: BIGINT PRIMARY KEY
- `kind`: VARCHAR(64) NOT NULL
- `received_at`: TIMESTAMPTZ NOT NULL
- `processed_at`: TIMESTAMPTZ NULL
- `status`: VARCHAR(32) DEFAULT 'received' NOT NULL CHECK (status IN ('received', 'processed', 'failed', 'ignored'))
- `error`: TEXT NULL

### 11. `access_grants`
- `id`: BIGSERIAL PRIMARY KEY
- `user_id`: BIGINT REFERENCES users(id) ON DELETE CASCADE
- `target_type`: VARCHAR(32) NOT NULL CHECK (target_type IN ('work', 'episode'))
- `target_id`: BIGINT NOT NULL
- `source_type`: VARCHAR(32) NOT NULL CHECK (source_type IN ('purchase', 'admin'))
- `source_id`: VARCHAR(255) NOT NULL
- `status`: VARCHAR(32) DEFAULT 'active' NOT NULL CHECK (status IN ('active', 'revoked'))
- `granted_at`: TIMESTAMPTZ NOT NULL
- `revoked_at`: TIMESTAMPTZ NULL
- `reason`: TEXT NULL
- `created_at`, `updated_at`: TIMESTAMPTZ NOT NULL
- **Indeks:** `(user_id, target_type, target_id, status)`

### 12. `watch_progress`
- `user_id`: BIGINT REFERENCES users(id) ON DELETE CASCADE
- `work_id`: BIGINT REFERENCES works(id) ON DELETE CASCADE
- `last_episode_id`: BIGINT REFERENCES episodes(id) ON DELETE CASCADE
- `player_message_id`: BIGINT NULL
- `opened_at`: TIMESTAMPTZ NOT NULL
- `updated_at`: TIMESTAMPTZ NOT NULL
- PRIMARY KEY (`user_id`, `work_id`)

### 13. `analytics_events`
- `id`: BIGSERIAL PRIMARY KEY
- `user_id`: BIGINT NULL REFERENCES users(id) ON DELETE SET NULL
- `event_name`: VARCHAR(64) NOT NULL
- `work_id`: BIGINT NULL REFERENCES works(id) ON DELETE SET NULL
- `episode_id`: BIGINT NULL REFERENCES episodes(id) ON DELETE SET NULL
- `properties`: JSONB NOT NULL DEFAULT '{}'
- `occurred_at`: TIMESTAMPTZ NOT NULL
- `request_id`: UUID NULL
- **Indekslar:** `(event_name, occurred_at)`, `(user_id, occurred_at)`

### 14. `admin_audit_logs`
- `id`: BIGSERIAL PRIMARY KEY
- `actor_id`: BIGINT NOT NULL
- `action`: VARCHAR(64) NOT NULL
- `entity_type`: VARCHAR(64) NOT NULL
- `entity_id`: BIGINT NOT NULL
- `old_values`: JSONB NULL
- `new_values`: JSONB NULL
- `created_at`: TIMESTAMPTZ NOT NULL

### 15. `settings`
- `key`: VARCHAR(64) PRIMARY KEY
- `value`: JSONB NOT NULL
- `updated_at`: TIMESTAMPTZ NOT NULL
