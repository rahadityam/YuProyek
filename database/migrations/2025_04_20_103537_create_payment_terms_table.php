<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name'); // Nama Termin (e.g., "Termin 1", "Fase A")
            $table->date('start_date');
            $table->date('end_date');
            // $table->integer('sequence')->nullable(); // Opsional: untuk urutan manual jika perlu
            $table->timestamps();

            // Constraint unik untuk nama termin per proyek
            $table->unique(['project_id', 'name']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
    }
};