<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. genres
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name_uz', 255);
            $table->string('name_ru', 255);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. works
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('format', 32); // film, series, short
            $table->string('style', 32); // realistic, animation_2d, animation_3d, mixed
            $table->string('language', 8)->default('uz');
            $table->string('age_rating', 8); // 0+, 6+, 12+, 16+
            $table->string('poster_path', 512);
            $table->string('cover_path', 512)->nullable();
            $table->string('trailer_file_id', 255)->nullable();
            $table->string('status', 32)->default('draft'); // draft, published, hidden
            $table->string('pricing_mode', 32)->default('free'); // free, work, episode
            $table->unsignedBigInteger('price_stars')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['format', 'style']);
        });

        // 3. work_genre
        Schema::create('work_genre', function (Blueprint $table) {
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->primary(['work_id', 'genre_id']);
        });

        // 4. seasons
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->integer('number');
            $table->string('title', 255)->nullable();
            $table->timestamps();

            $table->unique(['work_id', 'number']);
        });

        // 5. episodes
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->nullOnDelete();
            $table->integer('number');
            $table->string('title', 255)->nullable();
            $table->integer('duration_sec')->default(0);
            $table->string('file_id', 255);
            $table->string('file_unique_id', 255)->nullable();
            $table->bigInteger('storage_chat_id');
            $table->bigInteger('storage_message_id');
            $table->string('status', 32)->default('draft'); // draft, published, hidden
            $table->timestamp('publish_at')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->unsignedBigInteger('price_stars')->nullable();
            $table->timestamps();

            $table->index(['status', 'publish_at']);
        });

        // 6. upload_sessions
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('admin_user_id');
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->nullOnDelete();
            $table->string('status', 32)->default('pending'); // pending, completed, expired
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // 7. purchase_intents
        Schema::create('purchase_intents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 32); // work, episode
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('amount_stars');
            $table->string('currency', 8)->default('XTR');
            $table->string('status', 32)->default('pending'); // pending, paid, expired, cancelled, refunded
            $table->string('invoice_payload', 255)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // 8. payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('intent_id')->unique();
            $table->foreign('intent_id')->references('id')->on('purchase_intents')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('telegram_payment_charge_id', 255)->unique();
            $table->unsignedBigInteger('amount_stars');
            $table->string('currency', 8)->default('XTR');
            $table->timestamp('paid_at');
            $table->string('status', 32)->default('paid'); // paid, refunded
            $table->json('raw_event_reference')->nullable();
            $table->timestamps();
        });

        // 9. telegram_updates (Idempotency replay guard)
        Schema::create('telegram_updates', function (Blueprint $table) {
            $table->bigInteger('update_id')->primary();
            $table->string('kind', 64);
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('status', 32)->default('received'); // received, processed, failed, ignored
            $table->text('error')->nullable();
        });

        // 10. access_grants
        Schema::create('access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 32); // work, episode
            $table->unsignedBigInteger('target_id');
            $table->string('source_type', 32)->default('purchase'); // purchase, admin
            $table->string('source_id', 255);
            $table->string('status', 32)->default('active'); // active, revoked
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'target_type', 'target_id', 'status']);
        });

        // 11. watch_progress
        Schema::create('watch_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->foreignId('last_episode_id')->constrained('episodes')->cascadeOnDelete();
            $table->bigInteger('player_message_id')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('updated_at');
            $table->primary(['user_id', 'work_id']);
        });

        // 12. analytics_events
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_name', 64);
            $table->foreignId('work_id')->nullable()->constrained('works')->nullOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained('episodes')->nullOnDelete();
            $table->json('properties')->nullable();
            $table->timestamp('occurred_at');
            $table->uuid('request_id')->nullable();

            $table->index(['event_name', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
        });

        // 13. admin_audit_logs
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 64);
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at');
        });

        // 14. settings
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->json('value');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('watch_progress');
        Schema::dropIfExists('access_grants');
        Schema::dropIfExists('telegram_updates');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('purchase_intents');
        Schema::dropIfExists('upload_sessions');
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('work_genre');
        Schema::dropIfExists('works');
        Schema::dropIfExists('genres');
    }
};
