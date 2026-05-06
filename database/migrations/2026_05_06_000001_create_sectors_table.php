<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('sectors')->insert(array_map(
            fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()],
            [
                'Agriculture', 'Construction', 'Consulting', 'Design', 'E-commerce',
                'Éducation', 'Finance', 'Immobilier', 'Industrie', 'Intelligence artificielle',
                'Juridique', 'Logistique', 'Marketing', 'Médias', 'Ressources humaines',
                'Santé', 'Startup', 'Technologie', 'Tourisme', 'Ventes', 'Autre',
            ]
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
