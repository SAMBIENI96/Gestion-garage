<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // OR-2024-0001
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('vehicle_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->text('description_panne');
            $table->text('notes_patron')->nullable();
            $table->text('pieces_estimees')->nullable();
            $table->decimal('cout_estime', 10, 2)->nullable();

            $table->enum('statut', [
                'nouveau',
                'en_attente_pieces',
                'en_cours',
                'termine',
                'probleme',
                'annule'
            ])->default('nouveau');

            $table->enum('urgence', ['normal', 'urgent', 'vip'])->default('normal');

            $table->integer('kilometrage_entree')->nullable();
            $table->date('date_entree');
            $table->date('date_sortie_prevue')->nullable();
            $table->datetime('date_sortie_effective')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('urgence');
            $table->index('date_entree');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
