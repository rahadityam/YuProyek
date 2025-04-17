<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PaymentController; // Controller untuk Penggajian & Pembayaran
use App\Http\Controllers\SettingController; // Controller untuk Pengaturan Proyek (termasuk bobot & level)
use App\Http\Controllers\WageStandardController; // Controller untuk Master Upah
use App\Http\Controllers\ActivityController; // Controller untuk Log Aktivitas

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Halaman Utama
Route::get('/', function () {
    return view('welcome');
});

// Daftar Proyek Publik (jika ada)
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

// Dashboard Global (Menampilkan proyek user & mungkin proyek publik)
Route::get('/dashboard', [ProjectController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Grup Rute untuk Pengguna yang Terautentikasi
Route::middleware('auth')->group(function () {

    // Manajemen Proyek (Resourceful & custom routes)
    Route::resource('projects', ProjectController::class)->except(['index', 'show']); // Kecualikan index & show jika ditangani di bawah
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show'); // Detail proyek publik/umum

    // Aplikasi ke Proyek
    Route::get('/projects/{project}/apply', [ProjectApplicationController::class, 'create'])->name('projects.apply');
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'store'])->name('projects.apply.store');

    // Ganti Peran Pengguna
    Route::get('/user/switch-role/{role}', [ProfileController::class, 'switchRole'])->name('user.switch-role');

    // ---------------------------------------------------------------------
    // Rute Spesifik di Dalam Proyek (Menggunakan Prefix & Route Model Binding)
    // ---------------------------------------------------------------------
    Route::prefix('projects/{project}')->name('projects.')->group(function () {

        // Dashboard Proyek Internal
        Route::get('/dashboard', [ProjectController::class, 'projectDashboard'])->name('dashboard');

        // Kanban Board
        Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban'); // Menggunakan metode kanban di TaskController

        // --- Penggajian ---
        // [BARU] 1. Halaman Perhitungan Gaji (WSM Score)
        Route::get('/pembayaran/calculate', [PaymentController::class, 'showPayrollCalculation'])->name('pembayaran.calculate');
        // [LAMA/DIPERTAHANKAN] 2. Master Standar Upah (Resourceful, kecuali show)
        Route::resource('/wage-standards', WageStandardController::class)->except(['show'])->names('wage-standards');
        // [LAMA/DIKOMENTARI] - Rute lama penggajian mungkin digantikan oleh payroll/calculate dan payments
        Route::get('/penggajian', [PaymentController::class, 'index'])->name('penggajian.index'); // <-- Rute Lama

        // --- Pembayaran ---
        // [BARU/REVISI] 1. Halaman Upload Pembayaran & Riwayat Pembayaran
        Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('pembayaran'); // Menampilkan form upload & list riwayat (menggantikan /pembayaran lama)
        // [BARU/REVISI] 2. Proses Simpan Upload Pembayaran (termasuk link task)
        Route::post('/payments', [PaymentController::class, 'storePayment'])->name('storePayment'); // Menggantikan /pembayaran/store lama
        // [BARU] 3. Lihat Detail Pembayaran (termasuk task terkait)
        Route::get('/payments/{payment}', [PaymentController::class, 'showPaymentDetail'])->name('payment.detail');
        // [BARU/REVISI] 4. Update Status Pembayaran (pending, completed, rejected)
        Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus'); // Menggunakan path /payments
        // [BARU/REVISI] 5. Hapus Pembayaran (termasuk unlink task)
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy'); // Menggunakan path /payments
        // [LAMA/DIKOMENTARI] - Rute lama pembayaran
        // Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('projects.pembayaran'); // <-- Rute Lama (Path sama dengan yg baru)
        Route::post('/pembayaran/store', [PaymentController::class, 'storePayment'])->name('projects.storePayment'); // <-- Rute Lama (Path sama dengan yg baru)
        Route::patch('/pembayaran/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus'); // <-- Rute Lama (Path berbeda)
        Route::delete('/pembayaran/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy'); // <-- Rute Lama (Path berbeda)

        // --- Pengaturan Proyek ---
        // [LAMA/DIPERTAHANKAN] Halaman utama pengaturan
        Route::get('/settings', [SettingController::class, 'index'])->name('pengaturan');
        Route::patch('/settings', [SettingController::class, 'update'])->name('pengaturan.update');
        // [BARU] 1. Pengaturan Bobot WSM
        Route::get('/settings/weights', [SettingController::class, 'editWeights'])->name('settings.weights.edit');
        Route::patch('/settings/weights', [SettingController::class, 'updateWeights'])->name('settings.weights.update');
        // [BARU] 2. Pengaturan Level Dinamis (Kesulitan & Prioritas)
        Route::get('/settings/levels', [SettingController::class, 'manageLevels'])->name('settings.levels.manage');
        // [BARU] CRUD Difficulty Levels
        // CRUD Difficulty
        Route::post('/settings/levels/difficulty', [SettingController::class, 'storeDifficultyLevel'])->name('settings.levels.difficulty.store');
        Route::patch('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'updateDifficultyLevel'])->name('settings.levels.difficulty.update');
        Route::delete('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'destroyDifficultyLevel'])->name('settings.levels.difficulty.destroy');
        // CRUD Priority
        Route::post('/settings/levels/priority', [SettingController::class, 'storePriorityLevel'])->name('settings.levels.priority.store');
        Route::patch('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'updatePriorityLevel'])->name('settings.levels.priority.update');
        Route::delete('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'destroyPriorityLevel'])->name('settings.levels.priority.destroy');
        Route::patch('/settings/levels/order', [SettingController::class, 'updateOrder'])->name('settings.levels.order');

        Route::patch('/settings/team/{user}/wage', [SettingController::class, 'updateMemberWageStandard'])->name('settings.team.wage.update');
        Route::patch('/settings/info', [SettingController::class, 'updateProjectInfo'])->name('pengaturan.info.update');
        Route::patch('/settings/payment-type', [SettingController::class, 'updatePaymentCalculationType'])->name('pengaturan.payment.update');

        // Rute untuk Wage Standards CRUD (Ini link ke controller WageStandard, bukan SettingController)
        Route::resource('/wage-standards', App\Http\Controllers\WageStandardController::class)
             ->except(['show'])
             ->names('wage-standards'); // Pastikan ini mengarah ke controller yang benar

        // --- Manajemen Tim ---
        // [LAMA/DIPERTAHANKAN] Rute-rute manajemen tim
        Route::get('/team', [ProjectController::class, 'teamMembers'])->name('team');
        Route::patch('/team/{user}/update-status', [ProjectApplicationController::class, 'updateStatus'])->name('application.updateStatus'); // Status aplikasi worker (accepted/rejected)
        Route::delete('/team/{user}/remove', [ProjectApplicationController::class, 'removeMember'])->name('team.remove'); // Hapus worker dari tim
        Route::get('/team/user/{user}', [ProjectApplicationController::class, 'viewProfile'])->name('user.profile'); // Lihat profil worker dalam konteks tim
        Route::patch('/team/{user}/wage', [ProjectController::class, 'updateMemberWage'])->name('team.updateWage'); // Update standar upah worker

        // --- Log Aktivitas ---
        // [LAMA/DIPERTAHANKAN] Rute-rute log aktivitas (direname ke 'activity')
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity'); // Nama 'activity' lebih umum dari 'aktivitas'
        Route::get('/activity/filter', [ActivityController::class, 'filter'])->name('activity.filter');
        // [LAMA/DIKOMENTARI] - Rute lama aktivitas jika namanya berbeda
        Route::get('/aktivitas', [ActivityController::class, 'index'])->name('projects.activity');
        Route::get('/aktivitas/filter', [ActivityController::class, 'filter'])->name('projects.activity.filter');


    }); // Akhir dari prefix projects/{project}

    // ---------------------------------------------------------------------
    // Rute Manajemen Tugas (Tasks) - Diluar prefix proyek jika berlaku global
    // (Dipertahankan sesuai kode asli Anda)
    // ---------------------------------------------------------------------
    // [LAMA/DIPERTAHANKAN] Resourceful route untuk Task (kecuali index)
    
    Route::resource('tasks', TaskController::class);
    Route::resource('tasks', TaskController::class)->except(['index']); // 'index' mungkin diganti oleh kanban view
    // [LAMA/DIPERTAHANKAN] Update Status Task
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    // [LAMA/DIPERTAHANKAN] Update Urutan Task
    Route::patch('/tasks/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
    // [LAMA/DIPERTAHANKAN] Update Batch
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');
    // [LAMA/DIPERTAHANKAN] Pencarian Task
    Route::post('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');

    // Route untuk mendapatkan detail lengkap task (JSON) untuk modal
    Route::get('/tasks/{task}/details', [TaskController::class, 'show'])->name('tasks.show.details'); // NEW

    // Route untuk komentar task
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store'); // NEW

    // Route untuk attachment task
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store'); // NEW
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy'); // NEW

    // Route untuk history task (jika memuat via AJAX)
    Route::get('/tasks/{task}/history', [TaskController::class, 'getHistory'])->name('tasks.history'); // NEW
    // ---------------------------------------------------------------------
    // Rute Lama Global (Di Luar Prefix Proyek) - Dikomentari
    // ---------------------------------------------------------------------
    // [LAMA/DIKOMENTARI] Rute global penggajian lama
    // Route::get('/penggajian', [PaymentController::class, 'index'])->name('penggajian.index');
    Route::post('/penggajian/{user}/pay', [PaymentController::class, 'pay'])->name('penggajian.pay');

    // [LAMA/DIKOMENTARI] Rute global pembayaran lama
    // Route::get('/pembayaran', [PaymentController::class, 'payment'])->name('pembayaran.index');
    // Route::post('/pembayaran/upload', [PaymentController::class, 'uploadProof'])->name('pembayaran.upload');

    // [LAMA/DIKOMENTARI] Rute global pengaturan lama
    // Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    // Route::post('/pengaturan/update', [SettingController::class, 'update'])->name('pengaturan.update');

    // [LAMA/DIKOMENTARI] Rute global kanban lama
    Route::get('/kanban', [TaskController::class, 'index'])->name('kanban.index');

    // ---------------------------------------------------------------------
    // Rute Profil Pengguna (Dipertahankan)
    // ---------------------------------------------------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

}); // Akhir dari middleware('auth')

// Rute Autentikasi Bawaan Laravel Breeze/UI
require __DIR__ . '/auth.php';