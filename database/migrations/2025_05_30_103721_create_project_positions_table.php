<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nama posisi, e.g., "Programmer", "Designer"
            $table->unsignedInteger('count'); // Jumlah yang dibutuhkan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_positions');
    }
};