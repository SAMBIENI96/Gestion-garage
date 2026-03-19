<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('immatriculation')->unique();
            $table->string('marque');
            $table->string('modele');
            $table->year('annee')->nullable();
            $table->integer('kilometrage')->nullable();
            $table->string('couleur')->nullable();
            $table->string('numero_chassis')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('immatriculation');
            $table->index(['marque', 'modele']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
