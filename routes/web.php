<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WageStandardController;
use Illuminate\Support\Facades\Route;

// Halaman Utama
Route::get('/', function () {
    return view('welcome');
});


Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

// Dashboard (Hanya untuk pengguna yang terautentikasi)
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [ProjectController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Grup Rute untuk Pengguna yang Terautentikasi
Route::middleware('auth')->group(function () {
    
    // Manajemen Proyek
    Route::resource('projects', ProjectController::class);
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Halaman di dalam proyek
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/dashboard', [ProjectController::class, 'projectDashboard'])->name('projects.dashboard');

        Route::get('/kanban', [TaskController::class, 'kanban'])->name('projects.kanban');
        Route::get('/penggajian', [PaymentController::class, 'index'])->name('projects.penggajian');
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('projects.pengaturan');

        Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('projects.pembayaran');
        Route::post('/pembayaran/store', [PaymentController::class, 'storePayment'])->name('projects.storePayment');
        Route::patch('/pembayaran/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');
        Route::delete('/pembayaran/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Add these inside the prefix('projects/{project}') group
        Route::get('/team', [ProjectController::class, 'teamMembers'])->name('projects.team');
        Route::patch('/team/{user}/update-status', [ProjectApplicationController::class, 'updateStatus'])->name('projects.application.updateStatus');
        Route::delete('/team/{user}/remove', [ProjectApplicationController::class, 'removeMember'])->name('projects.team.remove');
        Route::get('/team/user/{user}', [ProjectApplicationController::class, 'viewProfile'])->name('projects.user.profile');

        // Activity Log Route
        Route::get('/aktivitas', [ActivityController::class, 'index'])->name('projects.activity');
        Route::get('/aktivitas/filter', [ActivityController::class, 'filter'])->name('projects.activity.filter');

        // Wage Standards routes
        Route::get('/wage-standards', [WageStandardController::class, 'index'])->name('projects.wage-standards.index');
        Route::get('/wage-standards/create', [WageStandardController::class, 'create'])->name('projects.wage-standards.create');
        Route::post('/wage-standards', [WageStandardController::class, 'store'])->name('projects.wage-standards.store');
        Route::get('/wage-standards/{wageStandard}/edit', [WageStandardController::class, 'edit'])->name('projects.wage-standards.edit');
        Route::put('/wage-standards/{wageStandard}', [WageStandardController::class, 'update'])->name('projects.wage-standards.update');
        Route::delete('/wage-standards/{wageStandard}', [WageStandardController::class, 'destroy'])->name('projects.wage-standards.destroy');
    });

    Route::get('/user/switch-role/{role}', [ProfileController::class, 'switchRole'])->name('user.switch-role');

    Route::get('/projects/{project}/apply', [ProjectApplicationController::class, 'create'])->name('projects.apply');
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'store'])->name('projects.apply.store');
    // Rute untuk Manajemen Tugas (Tasks)
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('/tasks/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');
    Route::post('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');

    // Rute untuk Penggajian
    Route::get('/penggajian', [PaymentController::class, 'index'])->name('penggajian.index');
    Route::post('/penggajian/{user}/pay', [PaymentController::class, 'pay'])->name('penggajian.pay');

    // Rute untuk Pembayaran
    Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('pembayaran.index');
    Route::post('/pembayaran/upload', [PaymentController::class, 'uploadProof'])->name('pembayaran.upload');

    // Rute untuk Pengaturan
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan/update', [SettingController::class, 'update'])->name('pengaturan.update');

    // Rute untuk Kanban Board
    Route::get('/kanban', [TaskController::class, 'index'])->name('kanban.index');

    // Rute untuk Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rute untuk Autentikasi (Login, Register, dll.)
require __DIR__ . '/auth.php';