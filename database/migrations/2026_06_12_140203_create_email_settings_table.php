<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default')->unique();
            $table->string('driver')->default('smtp');
            $table->string('host')->default('smtp.ionos.com');
            $table->unsignedSmallInteger('port')->default(587);
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('username')->nullable();
            $table->text('password')->nullable();       // encrypted via Crypt
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
