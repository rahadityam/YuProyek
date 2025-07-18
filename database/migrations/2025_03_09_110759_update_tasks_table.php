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
        Schema::table('tasks', function (Blueprint $table) {
            // HANYA TAMBAHKAN KOLOM BARU

            $table->foreignId('project_id')->after('order')->constrained('projects')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->after('project_id')->constrained('users')->onDelete('set null');
            
            // Hapus kolom integer lama jika ada (dari migrasi yang salah sebelumnya)
            // Ini aman untuk ditinggalkan, karena migrate:fresh akan membuat skema dari awal
            // Namun, untuk kebersihan, kita bisa cek dan hapus.
            if (Schema::hasColumn('tasks', 'difficulty_level')) {
                $table->dropColumn('difficulty_level');
            }
            if (Schema::hasColumn('tasks', 'priority_level')) {
                $table->dropColumn('priority_level');
            }

            // Tambahkan kolom foreign key baru
            $table->foreignId('difficulty_level_id')->nullable()->constrained('difficulty_levels')->nullOnDelete()->after('assigned_to');
            $table->foreignId('priority_level_id')->nullable()->constrained('priority_levels')->nullOnDelete()->after('difficulty_level_id');

            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->text('notes')->nullable();

            // Kolom-kolom baru lainnya
            $table->unsignedTinyInteger('achievement_percentage')->default(100)->after('priority_level_id');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete()->after('achievement_percentage');
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('achievement_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Logika untuk membalikkan perubahan
            $columnsToDrop = [
                'project_id', 'assigned_to', 'difficulty_level_id', 'priority_level_id',
                'start_time', 'end_time', 'notes', 'achievement_percentage',
                'payment_id', 'progress_percentage'
            ];
            
            // Urutan drop foreign key penting
            $foreignKeysToDrop = ['project_id', 'assigned_to', 'difficulty_level_id', 'priority_level_id', 'payment_id'];
            foreach($foreignKeysToDrop as $fk) {
                if(Schema::hasColumn('tasks', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }

            // Drop kolom biasa
            foreach($columnsToDrop as $col) {
                if(Schema::hasColumn('tasks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};