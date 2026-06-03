<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('presentation_video')->nullable()->after('avatar');
            $table->string('presentation_video_status', 20)->nullable()->after('presentation_video');
            $table->text('presentation_video_rejection_reason')->nullable()->after('presentation_video_status');
            $table->timestamp('presentation_video_uploaded_at')->nullable()->after('presentation_video_rejection_reason');
            $table->timestamp('presentation_video_reviewed_at')->nullable()->after('presentation_video_uploaded_at');
            $table->foreignId('presentation_video_reviewed_by')->nullable()->after('presentation_video_reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presentation_video_reviewed_by');
            $table->dropColumn([
                'presentation_video',
                'presentation_video_status',
                'presentation_video_rejection_reason',
                'presentation_video_uploaded_at',
                'presentation_video_reviewed_at',
            ]);
        });
    }
};
