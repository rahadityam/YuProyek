<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_payment_calculation_type_to_projects_table.php

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
        Schema::table('projects', function (Blueprint $table) {
            // Tambah kolom untuk menyimpan tipe kalkulasi pembayaran
            // Default ke 'task' jika data sudah ada
            $table->enum('payment_calculation_type', ['termin', 'task', 'full'])
                  ->default('task')
                  ->after('status'); // Sesuaikan posisi kolom jika perlu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('payment_calculation_type');
        });
    }
};