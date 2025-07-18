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
        // KOSONGKAN METHOD INI KARENA KOLOM SUDAH DITAMBAHKAN DI MIGRASI SEBELUMNYA
        // Schema::table('tasks', function (Blueprint $table) {
        //     $table->unsignedTinyInteger('progress_percentage')->default(0)->after('achievement_percentage');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // KOSONGKAN METHOD INI JUGA UNTUK KONSISTENSI
        // Schema::table('tasks', function (Blueprint $table) {
        //     $table->dropColumn('progress_percentage');
        // });
    }
};