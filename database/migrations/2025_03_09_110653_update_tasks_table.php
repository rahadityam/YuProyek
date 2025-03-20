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
            // Cek dan hapus kolom yang ingin diubah jika ada
            if (Schema::hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('tasks', 'user_id')) {
                // Cek apakah ada foreign key pada kolom user_id
                $table->dropForeign(['user_id']);

                $table->dropColumn('user_id');
            }
            
            // Tambahkan kolom baru
            if (!Schema::hasColumn('tasks', 'status')) {
                $table->enum('status', ['to_do', 'in_progress', 'done'])->default('to_do');
            }
            
            if (!Schema::hasColumn('tasks', 'project_id')) {
                $table->unsignedBigInteger('project_id')->after('order');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('tasks', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('project_id');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('tasks', 'difficulty_level')) {
                $table->integer('difficulty_level')->default(1)->comment('1-5 from easy to hard');
            }
            
            if (!Schema::hasColumn('tasks', 'priority_level')) {
                $table->integer('priority_level')->default(1)->comment('1-5 from low to high');
            }
            
            if (!Schema::hasColumn('tasks', 'start_time')) {
                $table->dateTime('start_time')->nullable();
            }
            
            if (!Schema::hasColumn('tasks', 'end_time')) {
                $table->dateTime('end_time')->nullable();
            }
            
            if (!Schema::hasColumn('tasks', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Hapus foreign key jika ada
            if (Schema::hasColumn('tasks', 'project_id')) {
                $table->dropForeign(['project_id']);
            }
            
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
            
            // Hapus kolom yang ditambahkan
            $columns = [
                'status', 'project_id', 'assigned_to', 'difficulty_level', 
                'priority_level', 'start_time', 'end_time', 'notes'
            ];
            
            $columnsToRemove = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
            
            // Tambahkan kembali kolom lama jika perlu
            if (!Schema::hasColumn('tasks', 'status')) {
                $table->string('status')->default('To Do');
            }
            
            if (!Schema::hasColumn('tasks', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
        });
    }
    
    /**
     * Get list of foreign keys for a table
     */
    private function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();
        
        $foreignKeys = [];
        
        if (method_exists($conn, 'listTableForeignKeys')) {
            $tableForeignKeys = $conn->listTableForeignKeys($table);
            
            foreach ($tableForeignKeys as $key) {
                $foreignKeys[] = [
                    'name' => $key->getName(),
                    'columns' => $key->getLocalColumns(),
                ];
            }
        }
        
        return $foreignKeys;
    }
};