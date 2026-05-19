<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });

        Schema::table('group_posts', function (Blueprint $table) {
            $table->enum('type', ['post', 'activity'])->default('post')->after('body');
            $table->string('photo_path')->nullable()->after('type');
            $table->string('activity_title', 150)->nullable()->after('photo_path');
            $table->dateTime('activity_date')->nullable()->after('activity_title');
        });
    }

    public function down(): void
    {
        Schema::table('group_posts', function (Blueprint $table) {
            $table->dropColumn(['type', 'photo_path', 'activity_title', 'activity_date']);
        });
        Schema::dropIfExists('group_invitations');
    }
};
