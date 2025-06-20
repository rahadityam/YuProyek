<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function created(Project $project): void
    {
        // Data default untuk Tingkat Kesulitan
        $defaultDifficulties = [
            ['name' => 'Sangat Ringan',      'value' => 1,  'color' => '#4ade80', 'display_order' => 1], // green-400
            ['name' => 'Ringan',             'value' => 2,  'color' => '#22c55e', 'display_order' => 2], // green-500
            ['name' => 'Cukup Ringan',       'value' => 3,  'color' => '#3b82f6', 'display_order' => 3], // blue-500
            ['name' => 'Agak Ringan',        'value' => 4,  'color' => '#2563eb', 'display_order' => 4], // blue-600
            ['name' => 'Sedang',             'value' => 5,  'color' => '#eab308', 'display_order' => 5], // yellow-500
            ['name' => 'Agak Berat',         'value' => 6,  'color' => '#ea580c', 'display_order' => 6], // orange-600
            ['name' => 'Cukup Berat',        'value' => 7,  'color' => '#c2410c', 'display_order' => 7], // orange-700
            ['name' => 'Berat',              'value' => 8,  'color' => '#dc2626', 'display_order' => 8], // red-600
            ['name' => 'Sangat Berat',       'value' => 9,  'color' => '#b91c1c', 'display_order' => 9], // red-700
            ['name' => 'Sangat Berat Sekali','value' => 10, 'color' => '#7f1d1d', 'display_order' => 10],// red-900
        ];

        // Data default untuk Prioritas
        $defaultPriorities = [
            ['name' => 'Rendah',         'value' => 2,  'color' => '#6b7280', 'display_order' => 1], // gray-500
            ['name' => 'Cukup Rendah',   'value' => 4,  'color' => '#3b82f6', 'display_order' => 2], // blue-500
            ['name' => 'Sedang',         'value' => 6,  'color' => '#eab308', 'display_order' => 3], // yellow-500
            ['name' => 'Cukup Tinggi',   'value' => 8,  'color' => '#ea580c', 'display_order' => 4], // orange-600
            ['name' => 'Tinggi',         'value' => 10, 'color' => '#dc2626', 'display_order' => 5], // red-600
        ];

        // Loop dan buat record untuk setiap level kesulitan
        foreach ($defaultDifficulties as $level) {
            $project->difficultyLevels()->create($level);
        }

        // Loop dan buat record untuk setiap level prioritas
        foreach ($defaultPriorities as $level) {
            $project->priorityLevels()->create($level);
        }
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }
}