<?php

use Illuminate\Support\Facades\Route;

// --- Import Controller yang MASIH DIPAKAI ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WageStandardController;

// --- Import Komponen Livewire ---
// Pastikan path dan nama kelas ini BENAR dan file-nya ADA
use App\Livewire\Project\Dashboard as ProjectDashboard;
use App\Livewire\Project\KanbanBoard as ProjectKanbanBoard;
use App\Livewire\Project\ProjectSettings;
use App\Livewire\Project\TeamMembers;
use App\Livewire\Project\ActivityLog as ProjectActivityLog;
use App\Livewire\Project\PayrollCalculate;
use App\Livewire\Project\PayslipCreateDraft;
use App\Livewire\Project\PayslipHistory;
use App\Livewire\Project\PayslipDetail;
use App\Livewire\Project\WageStandardIndex;
use App\Livewire\Project\WageStandardCreate;
use App\Livewire\Project\WageStandardEdit;
// use App\Http\Livewire\Project\UserProfile;

/* ... Route Global ... */
Route::get('/', function () { return view('welcome'); });
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/dashboard', [ProjectController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    // Profile, My Projects, Project Show, Apply, Project CRUD -> Controller Biasa (Tetap)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // ... route profile lainnya ...
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show'); // Detail publik
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    // ... route CRUD Project lainnya ...
    Route::get('/projects/{project}/apply', [ProjectApplicationController::class, 'create'])->name('projects.apply');
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'store'])->name('projects.apply.store');


    // ==========================================================
    // === Rute Spesifik di Dalam Konteks Satu Proyek ===
    // ==========================================================
    Route::prefix('projects/{project}')->name('projects.')->group(function () {

        // --- TAMPILAN HALAMAN (Gunakan Sintaks Standar untuk Komponen Full-Page) ---
        // Laravel akan tahu cara merender komponen Livewire jika sintaksnya benar
        Route::get('/dashboard', ProjectDashboard::class)->name('dashboard');
        Route::get('/kanban', ProjectKanbanBoard::class)->name('kanban');
        Route::get('/settings', ProjectSettings::class)->name('pengaturan');
        Route::get('/team', TeamMembers::class)->name('team');
        Route::get('/activity', ProjectActivityLog::class)->name('activity');
        Route::get('/payroll/calculate', PayrollCalculate::class)->name('payroll.calculate');
        Route::get('/payslips/create', PayslipCreateDraft::class)->name('payslips.create');
        Route::get('/payslips/history', PayslipHistory::class)->name('payslips.history');
        Route::get('/payslips/{payslip}', PayslipDetail::class)->name('payslips.show'); // {payslip} akan di-bind ke mount
        Route::get('/wage-standards', WageStandardIndex::class)->name('wage-standards.index');
        Route::get('/wage-standards/create', WageStandardCreate::class)->name('wage-standards.create');
        Route::get('/wage-standards/{wageStandard}/edit', WageStandardEdit::class)->name('wage-standards.edit'); // {wageStandard} akan di-bind ke mount
        Route::get('/team/user/{user}', [ProjectApplicationController::class, 'viewProfile'])->name('user.profile'); // Tetap Controller (atau jadi Livewire)


        // --- ACTIONS / UPDATES (Tetap Menggunakan Controller Biasa) ---
        // (Tidak ada perubahan di bagian actions)
        Route::post('/payslips', [PaymentController::class, 'storePayslip'])->name('payslips.store');
        Route::patch('/payslips/{payslip}/approve', [PaymentController::class, 'approvePayslip'])->name('payslips.approve');
        Route::delete('/payslips/{payslip}', [PaymentController::class, 'destroy'])->name('payslips.destroy');
        // ... route actions lainnya (settings, wage standards, team) ...
        Route::patch('/settings/info', [SettingController::class, 'updateProjectInfo'])->name('pengaturan.info.update');
        Route::patch('/settings/payment-type', [SettingController::class, 'updatePaymentCalculationType'])->name('pengaturan.payment.update');
        Route::patch('/settings/terms', [SettingController::class, 'updatePaymentTerms'])->name('settings.terms.update');
        Route::patch('/settings/weights', [SettingController::class, 'updateWeights'])->name('settings.weights.update');
        Route::post('/settings/levels/difficulty', [SettingController::class, 'storeDifficultyLevel'])->name('settings.levels.difficulty.store');
        Route::patch('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'updateDifficultyLevel'])->name('settings.levels.difficulty.update');
        Route::delete('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'destroyDifficultyLevel'])->name('settings.levels.difficulty.destroy');
        Route::post('/settings/levels/priority', [SettingController::class, 'storePriorityLevel'])->name('settings.levels.priority.store');
        Route::patch('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'updatePriorityLevel'])->name('settings.levels.priority.update');
        Route::delete('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'destroyPriorityLevel'])->name('settings.levels.priority.destroy');
        Route::patch('/settings/levels/order', [SettingController::class, 'updateOrder'])->name('settings.levels.order');
        Route::patch('/settings/team/{user}/wage', [SettingController::class, 'updateMemberWageStandard'])->name('settings.team.wage.update');
        Route::post('/wage-standards', [WageStandardController::class, 'store'])->name('wage-standards.store');
        Route::put('/wage-standards/{wageStandard}', [WageStandardController::class, 'update'])->name('wage-standards.update');
        Route::delete('/wage-standards/{wageStandard}', [WageStandardController::class, 'destroy'])->name('wage-standards.destroy');
        Route::patch('/team/{user}/update-status', [ProjectApplicationController::class, 'updateStatus'])->name('application.updateStatus');
        Route::delete('/team/{user}/remove', [ProjectApplicationController::class, 'removeMember'])->name('team.remove');


    }); // Akhir dari prefix('projects/{project}')

    // ==========================================================
    // === Rute API/AJAX untuk Task (Tetap Controller Biasa) ===
    // ==========================================================
     // (Tidak ada perubahan di bagian Task API routes)
    Route::get('/user/switch-role/{role}', [ProfileController::class, 'switchRole'])->name('user.switch-role');
    Route::get('/tasks/{task}/details', [TaskController::class, 'show'])->name('tasks.show.details');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::get('/tasks/{task}/history', [TaskController::class, 'getHistory'])->name('tasks.history');


}); // Akhir dari middleware('auth')

// --- Rute Autentikasi Bawaan Laravel ---
require __DIR__.'/auth.php';