<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Nama task
        $table->text('description')->nullable(); // Deskripsi task
        $table->string('status')->default('To Do'); // Status task (To Do, In Progress, Done)
        $table->integer('order')->default(0); // Urutan task
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang membuat task
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
