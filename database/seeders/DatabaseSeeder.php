<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users with specific roles
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $ceo = User::factory()->create([
            'name' => 'CEO User',
            'email' => 'ceo@example.com',
            'password' => Hash::make('password'),
            'role' => 'ceo',
            'status' => 'active',
        ]);

        $pm = User::factory()->create([
            'name' => 'Project Manager',
            'email' => 'pm@example.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager', // <-- PERBAIKAN DI SINI
            'status' => 'active',
        ]);

        $pw = User::factory()->create([
            'name' => 'Project Worker',
            'email' => 'pw@example.com',
            'password' => Hash::make('password'),
            'role' => 'worker',
            'status' => 'active',
        ]);

        // Create a Project owned by the PM
        // ProjectObserver akan otomatis menambahkan level default
        $project = Project::create([
            'name' => 'Proyek API Testing',
            'description' => 'Deskripsi untuk proyek yang dibuat via seeder.',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'budget' => 50000000,
            'status' => 'open',
            'owner_id' => $pm->id,
            'payment_calculation_type' => 'task',
        ]);

        // Add the worker to the project
        $project->workers()->attach($pw->id, ['status' => 'accepted', 'position' => 'Developer']);

        // Create a task for the project assigned to the worker
        Task::create([
            'project_id' => $project->id,
            'title' => 'Initial Task for Testing',
            'description' => 'This is a sample task.',
            'status' => 'To Do',
            'assigned_to' => $pw->id,
            'start_time' => now(),
            'end_time' => now()->addWeek(),
            'difficulty_level_id' => $project->difficultyLevels()->value('id'),
            'priority_level_id' => $project->priorityLevels()->value('id'),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->table(['Role', 'Email', 'Password'], [
            ['Admin', 'admin@example.com', 'password'],
            ['CEO', 'ceo@example.com', 'password'],
            ['Project Manager', 'pm@example.com', 'password'],
            ['Project Worker', 'pw@example.com', 'password'],
        ]);
    }
}