<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
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
        Route::get('/dashboard', function ($project) {
            return view('projects.dashboard', compact('project'));
        })->name('projects.dashboard');

        Route::get('/kanban', [TaskController::class, 'kanban'])->name('projects.kanban');
        Route::get('/penggajian', [PaymentController::class, 'index'])->name('projects.penggajian');
        Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('projects.pembayaran');
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('projects.pengaturan');
    });
    // Rute untuk Manajemen Tugas (Tasks)
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('/tasks/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');

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