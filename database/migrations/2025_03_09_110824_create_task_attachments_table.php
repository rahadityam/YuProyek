<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang upload
            $table->string('file_path'); // Path relatif di storage
            $table->string('file_name'); // Nama asli file
            $table->string('file_type')->nullable(); // MIME type
            $table->unsignedBigInteger('size')->nullable(); // Ukuran file dalam bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};