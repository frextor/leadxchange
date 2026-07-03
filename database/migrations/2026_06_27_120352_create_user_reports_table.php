<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', [
                'harcelement',       // §8.2 harcèlement/menace
                'fausses_infos',     // §8.2 fausses informations
                'usurpation',        // §8.2 usurpation d'identité
                'spam',              // §8.2 sollicitation non liée
                'lead_fictif',       // §8.2 lead fictif/mauvaise qualité
                'concurrence_deloy', // §8.2 concurrence déloyale
                'autre',
            ]);
            $table->text('details')->nullable();
            $table->enum('status', ['pending','reviewed','dismissed','actioned'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->unique(['reporter_id','reported_id','reason']); // évite doublons
            $table->index(['reported_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_reports'); }
};
