<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bagian ini sudah benar dan bisa tetap ada
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'difficulty_weight')) {
                $table->unsignedTinyInteger('difficulty_weight')->default(65)->after('status');
            }
            if (!Schema::hasColumn('projects', 'priority_weight')) {
                $table->unsignedTinyInteger('priority_weight')->default(35)->after('difficulty_weight');
            }
        });

        // Modifikasi bagian ini di tabel 'tasks'
        Schema::table('tasks', function (Blueprint $table) {
            // HAPUS baris yang menambahkan difficulty_level_id dan priority_level_id

            // HANYA TAMBAHKAN kolom yang belum ada dari migrasi ini
            if (!Schema::hasColumn('tasks', 'achievement_percentage')) {
                $table->unsignedTinyInteger('achievement_percentage')->default(100)->after('priority_level_id');
            }
            if (!Schema::hasColumn('tasks', 'payment_id')) {
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete()->after('achievement_percentage');
            }
        });

        // Bagian ini juga bisa tetap ada
        Schema::table('payments', function (Blueprint $table) {
             if (!Schema::hasColumn('payments', 'payment_name')) {
                 $table->string('payment_name')->after('user_id');
             }
             if (!Schema::hasColumn('payments', 'bank_account')) {
                 $table->string('bank_account')->after('payment_name');
             }
             if (!Schema::hasColumn('payments', 'status')) {
                 $table->string('status')->default('pending')->after('notes');
             }
        });
    }

    public function down(): void
    {
        // Sesuaikan juga method 'down' untuk konsistensi
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['difficulty_weight', 'priority_weight']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Hapus HANYA kolom yang dibuat di method 'up' ini
            if (Schema::hasColumn('tasks', 'payment_id')) {
                $table->dropForeign(['payment_id']);
                $table->dropColumn('payment_id');
            }
            if (Schema::hasColumn('tasks', 'achievement_percentage')) {
                $table->dropColumn('achievement_percentage');
            }
        });
    }
};