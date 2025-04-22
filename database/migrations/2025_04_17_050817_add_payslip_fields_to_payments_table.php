<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Jika menggunakan DB::statement()

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Pastikan kolom belum ada sebelum menambah/mengubah (Supaya aman)

            // Status (gunakan string saja untuk fleksibilitas dan kemudahan migrasi)
            if (Schema::hasColumn('payments', 'status')) {
                $table->string('status', 50)->default('draft')->change(); // Ubah tipe jika sudah ada
            } else {
                $table->string('status', 50)->default('draft')->after('notes');
            }

            // Kolom Path dan Tipe Signature
            if (!Schema::hasColumn('payments', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('payments', 'signature_type')) {
                $table->enum('signature_type', ['digital', 'scanned'])->nullable()->after('signature_path');
            }

             // Tambahkan kolom tanggal periode, dan tanggal approved
             if (!Schema::hasColumn('payments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('signature_type'); // Waktu disetujui
             }

            // Siapa yang menyetujui (foreign key)
            if (!Schema::hasColumn('payments', 'approved_by')) {
                 $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_at');
            }

              if (!Schema::hasColumn('payments', 'period_start_date')) {
                $table->date('period_start_date')->nullable()->after('signature_type');
              }
             if (!Schema::hasColumn('payments', 'period_end_date')) {
                $table->date('period_end_date')->nullable()->after('period_start_date');
              }


            // Buat proof_image nullable (jika ada)
            if (Schema::hasColumn('payments', 'proof_image')) {
                $table->string('proof_image')->nullable()->change();
            }

            // Index untuk status
            if (!Schema::hasIndex('payments', 'payments_status_index')) {
                $table->index('status', 'payments_status_index'); // Beri nama index
            }

             // Pastikan kolom approved_by ada sebelum membuat FK dan index
             if (Schema::hasColumn('payments', 'approved_by') && !Schema::hasIndex('payments', 'payments_approved_by_index')) {
                $table->index('approved_by', 'payments_approved_by_index');
            }
            if (!Schema::hasIndex('payments', 'payments_approved_at_index')) {
                $table->index('approved_at', 'payments_approved_at_index');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Hapus index dulu (Jika Ada)
            $table->dropIndexIfExists('payments_status_index');
            $table->dropIndexIfExists('payments_approved_at_index');
            $table->dropIndexIfExists('payments_approved_by_index');

             // Drop Foreign Key Constraint Sebelum Drop Kolom (Jika ada)
             if(Schema::hasColumn('payments', 'approved_by')) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $prefix = Schema::getConnection()->getTablePrefix();
                if ($sm->listTableForeignKeys($prefix.'payments')) {
                  $table->dropForeign(['approved_by']);
                }
             }


            // Hapus kolom jika ada
            $table->dropColumnIfExists('signature_path');
            $table->dropColumnIfExists('signature_type');
            $table->dropColumnIfExists('period_start_date');
            $table->dropColumnIfExists('period_end_date');
            $table->dropColumnIfExists('approved_at');
            $table->dropColumnIfExists('approved_by');


            // Kembalikan status dan proof_image hanya jika kolom itu ada
            if (Schema::hasColumn('payments', 'status')) {
                $table->string('status', 50)->default('completed')->change(); //Default ke completed, silahkan sesuaikan
            }
            if (Schema::hasColumn('payments', 'proof_image')) {
                $table->string('proof_image')->nullable(false)->change(); // Sesuaikan nullable state sebelumnya
            }

         });

    }
};