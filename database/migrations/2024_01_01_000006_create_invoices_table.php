<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // FAC-2024-0001
            $table->foreignId('repair_order_id')->constrained()->onDelete('restrict');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            $table->json('lignes'); // [{description, quantite, prix_unitaire, type: 'piece'|'main_oeuvre'}]
            $table->decimal('sous_total', 10, 2);
            $table->decimal('remise_pct', 5, 2)->default(0);
            $table->decimal('remise_montant', 10, 2)->default(0);
            $table->decimal('total_ttc', 10, 2);

            $table->enum('statut', ['brouillon', 'validee', 'payee', 'annulee'])->default('brouillon');
            $table->date('date_facture');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
