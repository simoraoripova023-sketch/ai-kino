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
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 16)->unique()->nullable()->after('username');
            $table->foreignId('referrer_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('bonus_stars')->default(0)->after('referrer_id');
            $table->unsignedInteger('referrals_count')->default(0)->after('bonus_stars');
        });

        Schema::create('referral_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('reward_stars')->default(5);
            $table->unsignedInteger('referred_reward_stars')->default(2);
            $table->timestamps();

            $table->index('referrer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referral_code', 'referrer_id', 'bonus_stars', 'referrals_count']);
        });
    }
};
