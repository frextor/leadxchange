<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbox_items', function (Blueprint $table) {
            $table->id();
            $table->enum('kind', [
                'lead-received', 'lead-accepted', 'lead-rejected',
                'lead-converted', 'lead-reminder', 'message',
                'network', 'connection', 'deadline',
            ]);
            $table->enum('type', ['action', 'info', 'message', 'alert'])->default('info');
            $table->boolean('read')->default(false);
            $table->boolean('archived')->default(false);
            $table->timestamp('ts')->useCurrent();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_title')->nullable();
            $table->string('actor_company')->nullable();
            $table->string('lead_ref')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('title');
            $table->string('preview');
            $table->text('body');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_items');
    }
};
