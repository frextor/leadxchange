<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('type', 20)->default('text')->after('sender_id');
            $table->string('client_message_id', 80)->nullable()->after('type');
            $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
            $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
            $table->string('media_url', 2048)->nullable()->after('body');
            $table->string('caption', 1000)->nullable()->after('media_url');
            $table->string('filename')->nullable()->after('caption');
            $table->unsignedInteger('duration_ms')->nullable()->after('filename');
            $table->unique(['conversation_id', 'client_message_id'], 'messages_conv_client_id_unique');
        });

        // Make body nullable (requires raw SQL without doctrine/dbal)
        DB::statement('ALTER TABLE messages MODIFY COLUMN body TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->dropUnique('messages_conv_client_id_unique');
            $table->dropColumn(['type', 'client_message_id', 'receiver_id', 'media_url', 'caption', 'filename', 'duration_ms']);
        });

        DB::statement('ALTER TABLE messages MODIFY COLUMN body TEXT NOT NULL');
    }
};
