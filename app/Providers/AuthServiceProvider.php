<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Uncomment jika Anda menggunakan Gate
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Impor model Anda
use App\Models\Project;
use App\Models\Task;
use App\Models\Payment; // Model Slip Gaji Anda

// Impor policy Anda
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\PaymentPolicy; // Policy Slip Gaji Anda

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Payment::class => PaymentPolicy::class, // Daftarkan policy untuk slip gaji
        // Daftarkan policy lain di sini jika ada
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Di sini Anda juga bisa mendaftarkan Gate jika diperlukan
        // Gate::define('edit-settings', function (User $user) {
        //     return $user->isAdmin();
        // });
    }
}