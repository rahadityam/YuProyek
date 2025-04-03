<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DifficultyLevel;
use App\Models\Project;
use Illuminate\Support\Facades\DB; // Optional

class DifficultyLevelSeeder extends Seeder
{
    public function run($data = null): void
{
    $targetProject = $data['project'] ?? Project::orderBy('id', 'asc')->first();

        if (!$targetProject) {
            // Coba ambil project pertama yang MEMILIKI ID
            // Urutkan berdasarkan ID untuk konsistensi jika ada multiple projects
            $targetProject = Project::orderBy('id', 'asc')->first();

            // ---> TAMBAHKAN DEBUG DI SINI <---
            if ($this->command) {
                $this->command->info("Debugging targetProject object...");
            }
            dd($targetProject); // Die and dump the object
        }

        // === Pengecekan Krusial ===
        // Pastikan $targetProject tidak null DAN $targetProject->id tidak null/kosong
        if (!$targetProject || !$targetProject->id) {
            if ($this->command) {
                // Pesan lebih spesifik jika project ditemukan tapi ID-nya null
                if ($targetProject && !$targetProject->id) {
                     $this->command->warn('Found a project but its ID is null/empty. Cannot seed difficulty levels without a valid Project ID.');
                } else {
                    $this->command->info('No valid projects with an ID found. Skipping DifficultyLevelSeeder.');
                }
            }
            return; // Hentikan eksekusi
        }

        // Jika sampai sini, $targetProject dan $targetProject->id valid
        if ($this->command) {
            $this->command->line("Seeding difficulty levels for project ID: <fg=cyan>{$targetProject->id}</> ('{$targetProject->name}')");
        }

        $levels = [
            ['name' => 'Sangat Ringan', 'value' => 1],
            ['name' => 'Ringan', 'value' => 2],
            ['name' => 'Normal', 'value' => 3],
            ['name' => 'Berat', 'value' => 4],
            ['name' => 'Sangat Berat', 'value' => 5],
        ];

        foreach ($levels as $level) {
            try {
                DifficultyLevel::updateOrCreate(
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
                    $this->command->error("Error seeding difficulty level '{$level['name']}' for project ID {$targetProject->id}: " . $e->getMessage());
                 }
                 // Anda bisa memilih untuk melanjutkan (continue) atau menghentikan loop (break) atau melempar ulang exception (throw $e)
                 // continue;
            }
        }

        // if ($this->command) {
        //     $this->command->info("Finished seeding difficulty levels for project ID: {$targetProject->id}.");
        // }
    }
}