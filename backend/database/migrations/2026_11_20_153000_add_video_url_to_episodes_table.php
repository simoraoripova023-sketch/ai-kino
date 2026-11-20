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
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('video_url', 1024)->nullable()->after('duration_sec');
            $table->string('file_id', 255)->nullable()->change();
            $table->bigInteger('storage_chat_id')->nullable()->change();
            $table->bigInteger('storage_message_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
