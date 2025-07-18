<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('priority_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->string('name'); // e.g., "Sangat Rendah"
                $table->integer('value'); // Numerical value for calculation
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('priority_levels');
        }
    };