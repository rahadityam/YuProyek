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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_term_id')) {
                 // Tambahkan foreign key ke payment_terms, nullable karena tidak semua payment bertipe termin
                 $table->foreignId('payment_term_id')->nullable()->after('payment_type')->constrained('payment_terms')->onDelete('set null');
            }
             // Hapus kolom period_start_date dan period_end_date jika tidak lagi diperlukan setelah migrasi sebelumnya
             // Jika masih diperlukan untuk historical, biarkan saja.
            // if (Schema::hasColumn('payments', 'period_start_date')) {
            //     $table->dropColumn('period_start_date');
            // }
            // if (Schema::hasColumn('payments', 'period_end_date')) {
            //     $table->dropColumn('period_end_date');
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
             if (Schema::hasColumn('payments', 'payment_term_id')) {
                  // Drop foreign key dulu sebelum drop kolom
                 try { // Try-catch untuk menghindari error jika constraint tidak ada
                     $table->dropForeign(['payment_term_id']);
                 } catch (\Exception $e) {
                     // Abaikan error jika constraint tidak ditemukan
                 }
                 $table->dropColumn('payment_term_id');
             }
             // Tambahkan kembali kolom period_start/end jika dihapus di 'up'
            // if (!Schema::hasColumn('payments', 'period_start_date')) {
            //     $table->date('period_start_date')->nullable();
            // }
            // if (!Schema::hasColumn('payments', 'period_end_date')) {
            //     $table->date('period_end_date')->nullable();
            // }
        });
    }
};