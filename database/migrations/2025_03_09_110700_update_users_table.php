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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom baru jika belum ada
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('password');
            }
            
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone_number');
            }
            
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('gender');
            }
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('birth_date');
            }
            
            if (!Schema::hasColumn('users', 'description')) {
                $table->text('description')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('users', 'cv_path')) {
                $table->string('cv_path')->nullable()->after('profile_photo_path');
            }
            
            if (!Schema::hasColumn('users', 'portfolio_path')) {
                $table->string('portfolio_path')->nullable()->after('cv_path');
            }
            
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'project_owner', 'worker', 'ceo'])->default('worker')->after('portfolio_path');
            }
             if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'blocked'])->default('active')->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'phone_number',
                'gender',
                'birth_date',
                'address',
                'description',
                'profile_photo_path',
                'cv_path',
                'portfolio_path',
                'role'
            ]);
        });
    }
};