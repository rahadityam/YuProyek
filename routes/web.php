<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PaymentController; // Tetap gunakan PaymentController
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WageStandardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminDashboardController;


/* ... Route::get('/', ...), Route::get('/projects', ...) */

// Halaman Utama
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return view('auth.login');
});

// Daftar Proyek Publik (jika ada)
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');


// Dashboard Global
// Route::get('/dashboard', [ProjectController::class, 'dashboard'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');
Route::get('/dashboard', function () {
    return redirect()->route('projects.my-projects');
})->middleware(['auth', 'verified'])->name('dashboard');


// Grup Rute untuk Pengguna yang Terautentikasi
Route::middleware('auth')->group(function () {

    // Manajemen Proyek
    Route::resource('projects', ProjectController::class)->except(['index', 'show']);
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    // Aplikasi ke Proyek
    Route::get('/projects/{project}/apply', [ProjectApplicationController::class, 'create'])->name('projects.apply');
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'store'])->name('projects.apply.store');

    // Ganti Peran Pengguna
    Route::get('/user/switch-role/{role}', [ProfileController::class, 'switchRole'])->name('user.switch-role');

    // ---------------------------------------------------------------------
    // Rute Spesifik di Dalam Proyek
    // ---------------------------------------------------------------------
    Route::prefix('projects/{project}')->name('projects.')->group(function () {

        Route::get('/dashboard', [ProjectController::class, 'projectDashboard'])->name('dashboard');
        Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
        Route::resource('/wage-standards', WageStandardController::class)->except(['show'])->names('wage-standards');
        Route::get('/team', [ProjectController::class, 'teamMembers'])->name('team');
        Route::patch('/team/{user}/update-status', [ProjectApplicationController::class, 'updateStatus'])->name('application.updateStatus');
        Route::delete('/team/{user}/remove', [ProjectApplicationController::class, 'removeMember'])->name('team.remove');
        Route::get('/team/user/{user}', [ProjectApplicationController::class, 'viewProfile'])->name('user.profile');
        Route::patch('/team/{user}/wage', [ProjectController::class, 'updateMemberWage'])->name('team.updateWage');
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
        Route::get('/activity/filter', [ActivityController::class, 'filter'])->name('activity.filter');


        // Penggajian & Slip Gaji
        Route::get('/payroll/calculate', [PaymentController::class, 'showPayrollCalculation'])->name('payroll.calculate');

        // Route untuk form pembuatan slip gaji (jika masih ada halaman terpisah, jika tidak, ini bisa dihapus/redirect)
        // Route::get('/payslips/create', [PaymentController::class, 'createAndDraftPayslip'])->name('payslips.create');
        // Sekarang, route 'payslips.create' mungkin tidak lagi relevan sebagai halaman terpisah.
        // Jika ada link yang masih mengarah ke sana, pastikan PaymentController@createAndDraftPayslip
        // me-redirect ke halaman yang sesuai (misal, payroll.calculate).

        Route::post('/payslips', [PaymentController::class, 'storePayslip'])->name('payslips.store'); // Untuk submit dari modal
        Route::get('/payslips', [PaymentController::class, 'payslipList'])->name('payslips.history'); // Menggantikan history, menampilkan list gabungan
        Route::get('/payslips/{payslip}', [PaymentController::class, 'showPayslipDetail'])->name('payslips.show');
        Route::patch('/payslips/{payslip}/approve', [PaymentController::class, 'approvePayslip'])->name('payslips.approve');
        Route::delete('/payslips/{payslip}', [PaymentController::class, 'destroy'])->name('payslips.destroy');

       // Pengaturan Proyek
       Route::get('/settings', [SettingController::class, 'index'])->name('pengaturan');
       // Pisahkan update
       Route::patch('/settings/info', [SettingController::class, 'updateProjectInfo'])->name('pengaturan.info.update');
       Route::patch('/settings/payment-type', [SettingController::class, 'updatePaymentCalculationType'])->name('pengaturan.payment.update');
       // --- BARU: Route untuk update terms ---
       Route::patch('/settings/terms', [SettingController::class, 'updatePaymentTerms'])->name('settings.terms.update');
       // --- END BARU ---
       // Bobot WSM
       Route::get('/settings/weights', [SettingController::class, 'editWeights'])->name('settings.weights.edit'); // Ganti nama view jika perlu
       Route::patch('/settings/weights', [SettingController::class, 'updateWeights'])->name('settings.weights.update');
       // Level Dinamis
       Route::get('/settings/levels', [SettingController::class, 'manageLevels'])->name('settings.levels.manage'); // Ganti nama view jika perlu
       Route::post('/settings/levels/difficulty', [SettingController::class, 'storeDifficultyLevel'])->name('settings.levels.difficulty.store');
       Route::patch('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'updateDifficultyLevel'])->name('settings.levels.difficulty.update');
       Route::delete('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'destroyDifficultyLevel'])->name('settings.levels.difficulty.destroy');
       Route::post('/settings/levels/priority', [SettingController::class, 'storePriorityLevel'])->name('settings.levels.priority.store');
       Route::patch('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'updatePriorityLevel'])->name('settings.levels.priority.update');
       Route::delete('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'destroyPriorityLevel'])->name('settings.levels.priority.destroy');
       Route::patch('/settings/levels/order', [SettingController::class, 'updateOrder'])->name('settings.levels.order');
       Route::patch('/settings/team/{user}/wage', [SettingController::class, 'updateMemberWageStandard'])->name('settings.team.wage.update'); // Ajax update

       Route::post('/team/invite', [App\Http\Controllers\ProjectApplicationController::class, 'inviteWorker'])->name('team.invite');
        // Route untuk PM membatalkan undangan atau worker menerima/menolak
        Route::patch('/team/invitations/{user}/status', [App\Http\Controllers\ProjectApplicationController::class, 'updateInvitationStatus'])->name('invitations.updateStatus');
    }); // Akhir dari prefix projects/{project}

    // ---------------------------------------------------------------------
    // Rute Manajemen Tugas (Tasks) - Tetap
    // ---------------------------------------------------------------------
    Route::resource('tasks', TaskController::class)->except(['index', 'show']); // Show diganti details
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('/tasks/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');
    Route::post('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');
    Route::get('/tasks/{task}/details', [TaskController::class, 'show'])->name('tasks.show.details'); // Detail JSON
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::get('/tasks/{task}/history', [TaskController::class, 'getHistory'])->name('tasks.history');

    // ---------------------------------------------------------------------
    // Rute Profil Pengguna (Tetap)
    // ---------------------------------------------------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// ---------------------------------------------------------------------
    // Rute Admin
    // ---------------------------------------------------------------------
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/projects', [AdminProjectController::class, 'index'])->name('admin.projects.index');
    Route::get('/admin/projects/{id}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('admin.projects.toggleStatus');
    Route::post('/admin/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
    Route::post('/admin/projects/{id}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('admin.projects.toggleStatus');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users/store', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users/sidebar', function () { return view('admin.users.sidebaradmin');})->name('admin.users.sidebar');


}); // Akhir dari middleware('auth')

// Rute Autentikasi Bawaan
require __DIR__ . '/auth.php';