<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('project_users', function (Blueprint $table) {
            $table->foreignId('wage_standard_id')->nullable()->constrained('wage_standards')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('project_users', function (Blueprint $table) {
            $table->dropForeign(['wage_standard_id']);
            $table->dropColumn('wage_standard_id');
        });
    }
};
