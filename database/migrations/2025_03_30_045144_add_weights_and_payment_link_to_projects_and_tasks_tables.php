<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add weights to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('difficulty_weight')->default(65)->after('status');
            $table->unsignedTinyInteger('priority_weight')->default(35)->after('difficulty_weight');
        });

        // Modify tasks table
        Schema::table('tasks', function (Blueprint $table) {
            // Drop old columns if they exist (adjust based on your actual old column names)
            // Schema::table('tasks', function (Blueprint $table) {
            //     $table->dropColumn(['difficulty_level', 'priority_level']);
            // });


            // Add new foreign keys (make sure old columns are dropped or renamed first)
            $table->foreignId('difficulty_level_id')->nullable()->constrained('difficulty_levels')->nullOnDelete()->after('assigned_to');
            $table->foreignId('priority_level_id')->nullable()->constrained('priority_levels')->nullOnDelete()->after('difficulty_level_id');
            $table->unsignedTinyInteger('achievement_percentage')->default(100)->after('priority_level_id'); // Default 100% completion

            // Link task to a specific payment record
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete()->after('achievement_percentage');

            // Rename old columns to avoid conflicts if needed, before dropping
            // $table->renameColumn('difficulty_level', 'old_difficulty_level');
            // $table->renameColumn('priority_level', 'old_priority_level');
        });

         // Add columns to payments table (if not already present)
         Schema::table('payments', function (Blueprint $table) {
             if (!Schema::hasColumn('payments', 'payment_name')) {
                 $table->string('payment_name')->after('user_id');
             }
             if (!Schema::hasColumn('payments', 'bank_account')) {
                 // Assuming this is the WORKER's bank account for payment destination
                 $table->string('bank_account')->after('payment_name');
             }
              if (!Schema::hasColumn('payments', 'status')) {
                 $table->string('status')->default('pending')->after('notes'); // pending, completed, rejected
             }
         });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['difficulty_weight', 'priority_weight']);
        });

        Schema::table('tasks', function (Blueprint $table) {
             // Drop foreign key constraints before columns
            $table->dropForeign(['difficulty_level_id']);
            $table->dropForeign(['priority_level_id']);
            $table->dropForeign(['payment_id']);

            $table->dropColumn(['difficulty_level_id', 'priority_level_id', 'achievement_percentage', 'payment_id']);

             // If you renamed old columns, rename them back or add them back
            // $table->renameColumn('old_difficulty_level', 'difficulty_level');
            // $table->renameColumn('old_priority_level', 'priority_level');
        });

        // Schema::table('payments', function (Blueprint $table) {
        //     $table->dropColumn(['payment_name', 'bank_account', 'status']); // Be careful dropping columns if they were needed elsewhere
        // });
    }
};