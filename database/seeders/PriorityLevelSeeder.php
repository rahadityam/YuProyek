<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriorityLevel; // Ganti model
use App\Models\Project;
use Illuminate\Support\Facades\DB; // Optional

class PriorityLevelSeeder extends Seeder
{
    public function run(Project $project = null): void
    {
        $targetProject = $project;

        if (!$targetProject) {
            // Coba ambil project pertama yang MEMILIKI ID
            $targetProject = Project::orderBy('id', 'asc')->first();
        }

        // === Pengecekan Krusial ===
        if (!$targetProject || !$targetProject->id) {
            if ($this->command) {
                if ($targetProject && !$targetProject->id) {
                     $this->command->warn('Found a project but its ID is null/empty. Cannot seed priority levels without a valid Project ID.');
                } else {
                    $this->command->info('No valid projects with an ID found. Skipping PriorityLevelSeeder.');
                }
            }
            return;
        }

         // Jika sampai sini, $targetProject dan $targetProject->id valid
         if ($this->command) {
            $this->command->line("Seeding priority levels for project ID: <fg=cyan>{$targetProject->id}</> ('{$targetProject->name}')");
        }


        $levels = [
            ['name' => 'Sangat Rendah', 'value' => 1], // Data prioritas
            ['name' => 'Rendah', 'value' => 2],
            ['name' => 'Normal', 'value' => 3],
            ['name' => 'Tinggi', 'value' => 4],
            ['name' => 'Sangat Tinggi', 'value' => 5],
        ];

        foreach ($levels as $level) {
            try {
                PriorityLevel::updateOrCreate( // Ganti model
                    [
                        'project_id' => $targetProject->id, // ID valid
                        'name' => $level['name']
                    ],
                    [
                        'value' => $level['value']
                    ]
                );
            } catch (\Exception $e) {
                 if ($this->command) {
                    // Menampilkan project ID di pesan error
                    $this->command->error("Error seeding priority level '{$level['name']}' for project ID {$targetProject->id}: " . $e->getMessage());
                 }
                // continue;
            }
        }

        // if ($this->command) {
        //     $this->command->info("Finished seeding priority levels for project ID: {$targetProject->id}.");
        // }
    }
}