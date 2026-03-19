<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->text('contenu');
            $table->string('photo_path')->nullable();
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut')->nullable();
            $table->timestamps();

            $table->index('repair_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_notes');
    }
};
