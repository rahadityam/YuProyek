<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Project; // Import Project model if needed for default order query
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('difficulty_levels', function (Blueprint $table) {
            $table->string('color', 7)->default('#cccccc')->after('value'); // Default light gray hex
            $table->unsignedInteger('display_order')->default(0)->after('color');
        });

        Schema::table('priority_levels', function (Blueprint $table) {
            $table->string('color', 7)->default('#cccccc')->after('value');
            $table->unsignedInteger('display_order')->default(0)->after('color');
        });

        // --- Set initial display_order based on current order (e.g., by value or ID) ---
        // This helps maintain a sensible order after migrating existing data.
        // Group by project to set order within each project.
        $projects = Project::with(['difficultyLevels', 'priorityLevels'])->get();

        foreach ($projects as $project) {
            // Difficulty Levels
            $difficultyLevels = $project->difficultyLevels()->orderBy('value', 'asc')->get();
            foreach ($difficultyLevels as $index => $level) {
                $level->update(['display_order' => $index + 1]); // Start order from 1
            }

            // Priority Levels
            $priorityLevels = $project->priorityLevels()->orderBy('value', 'asc')->get();
            foreach ($priorityLevels as $index => $level) {
                $level->update(['display_order' => $index + 1]);
            }
        }
        // ----------------------------------------------------------------------------
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('difficulty_levels', function (Blueprint $table) {
            $table->dropColumn(['color', 'display_order']);
        });

        Schema::table('priority_levels', function (Blueprint $table) {
            $table->dropColumn(['color', 'display_order']);
        });
    }
};