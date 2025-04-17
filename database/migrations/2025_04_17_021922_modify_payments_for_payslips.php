<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini jika perlu ALTER ENUM

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Opsi 1: Ubah ke string (lebih aman untuk migrasi antar DB)
            $table->string('status', 50)->default('draft')->change(); // Ubah default ke draft

            // Opsi 2: Jika Anda yakin menggunakan enum dan DB support (misal PostgreSQL)
            // DB::statement("ALTER TYPE payment_status ADD VALUE IF NOT EXISTS 'draft';"); // Tambahkan nilai baru jika belum ada
            // $table->enum('status', ['pending', 'completed', 'rejected', 'draft', 'approved'])->default('draft')->change(); // Tambahkan status baru
             // Ganti 'payment_status' dengan nama constraint enum Anda jika berbeda

            // Tambahkan kolom baru
            $table->string('signature_path')->nullable()->after('notes');
            $table->enum('signature_type', ['digital', 'scanned'])->nullable()->after('signature_path');
            $table->date('period_start_date')->nullable()->after('signature_type');
            $table->date('period_end_date')->nullable()->after('period_start_date');

            // Buat proof_image nullable
            $table->string('proof_image')->nullable()->change();

            // Index untuk status
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Kembalikan status ke string default 'pending' atau enum lama
            $table->string('status', 50)->default('pending')->change();
            // Atau kembalikan enum:
            // $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending')->change();

            $table->dropIndex(['status']); // Hapus index

            $table->dropColumn(['signature_path', 'signature_type', 'period_start_date', 'period_end_date']);

            // Kembalikan proof_image ke not nullable jika sebelumnya begitu
            // $table->string('proof_image')->nullable(false)->change();
        });

         // Jika menggunakan enum dan perlu menghapus nilai 'draft', 'approved' (lebih kompleks, hati-hati)
         // DB::statement("ALTER TYPE payment_status RENAME TO old_payment_status;");
         // DB::statement("CREATE TYPE payment_status AS ENUM('pending', 'completed', 'rejected');");
         // DB::statement("ALTER TABLE payments ALTER COLUMN status TYPE payment_status USING status::text::payment_status;");
         // DB::statement("DROP TYPE old_payment_status;");
    }
};