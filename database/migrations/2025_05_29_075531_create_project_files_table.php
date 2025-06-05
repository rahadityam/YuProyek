<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User yang mengunggah
            $table->string('file_name'); // Nama asli file
            $table->string('file_path'); // Path penyimpanan di disk
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // Ukuran file dalam bytes
            $table->string('display_name')->nullable(); // Nama yang bisa diedit untuk ditampilkan
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};