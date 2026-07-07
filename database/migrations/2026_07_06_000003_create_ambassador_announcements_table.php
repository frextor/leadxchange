<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassador_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('subject', 255);
            $table->text('body');
            $table->enum('type', ['event_reminder', 'welcome', 'networking', 'general'])->default('general');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambassador_announcements');
    }
};
