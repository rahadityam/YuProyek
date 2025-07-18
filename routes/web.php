<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WageStandardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ceo\ceoDashboardController;
use App\Http\Controllers\ceo\ceoListProjectController;
use App\Http\Controllers\ceo\ceoListUserController;
use App\Http\Controllers\NotificationController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

Route::get('/dashboard', function () {
    return redirect()->route('projects.my-projects');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grup Rute untuk Pengguna yang Terautentikasi (selain admin dan ceo)
Route::middleware('auth')->group(function () {
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::resource('projects', ProjectController::class)->except(['index', 'show']);

    Route::get('/projects/{project}/apply', [ProjectApplicationController::class, 'create'])->name('projects.apply');
    Route::post('/projects/{project}/apply', [ProjectApplicationController::class, 'store'])->name('projects.apply.store');
    Route::get('/user/switch-role/{role}', [ProfileController::class, 'switchRole'])->name('user.switch-role');

    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::get('/dashboard', [ProjectController::class, 'projectDashboard'])->name('dashboard');
        Route::get('/kanban', [TaskController::class, 'kanban'])->name('kanban');
        Route::get('/tasks/recap', [TaskController::class, 'recap'])->name('tasks.recap');
        Route::resource('/wage-standards', WageStandardController::class)->except(['show'])->names('wage-standards');
        Route::get('/team', [ProjectController::class, 'teamMembers'])->name('team');
        Route::patch('/team/{user}/update-status', [ProjectApplicationController::class, 'updateStatus'])->name('application.updateStatus');
        Route::delete('/team/{user}/remove', [ProjectApplicationController::class, 'removeMember'])->name('team.remove');
        Route::get('/team/user/{user}', [ProjectApplicationController::class, 'viewProfile'])->name('user.profile');
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity');

        Route::get('/payroll/calculate', [PaymentController::class, 'showPayrollCalculation'])->name('payroll.calculate');
        Route::post('/payslips', [PaymentController::class, 'storePayslip'])->name('payslips.store');
        Route::get('/payslips', [PaymentController::class, 'payslipList'])->name('payslips.history');
        Route::get('/payslips/{payslip}', [PaymentController::class, 'showPayslipDetail'])->name('payslips.show');
        Route::patch('/payslips/{payslip}/approve', [PaymentController::class, 'approvePayslip'])->name('payslips.approve');
        Route::delete('/payslips/{payslip}', [PaymentController::class, 'destroy'])->name('payslips.destroy');

        Route::get('/settings', [SettingController::class, 'index'])->name('pengaturan');
        Route::patch('/settings/info', [SettingController::class, 'updateProjectInfo'])->name('pengaturan.info.update');
        Route::patch('/settings/payment-type', [SettingController::class, 'updatePaymentCalculationType'])->name('pengaturan.payment.update');
        Route::patch('/settings/terms', [SettingController::class, 'updatePaymentTerms'])->name('settings.terms.update');
        Route::get('/settings/weights', [SettingController::class, 'editWeights'])->name('settings.weights.edit');
        Route::patch('/settings/weights', [SettingController::class, 'updateWeights'])->name('settings.weights.update');
        Route::get('/settings/levels', [SettingController::class, 'manageLevels'])->name('settings.levels.manage');
        Route::post('/settings/levels/difficulty', [SettingController::class, 'storeDifficultyLevel'])->name('settings.levels.difficulty.store');
        Route::patch('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'updateDifficultyLevel'])->name('settings.levels.difficulty.update');
        Route::delete('/settings/levels/difficulty/{difficultyLevel}', [SettingController::class, 'destroyDifficultyLevel'])->name('settings.levels.difficulty.destroy');
        Route::post('/settings/levels/priority', [SettingController::class, 'storePriorityLevel'])->name('settings.levels.priority.store');
        Route::patch('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'updatePriorityLevel'])->name('settings.levels.priority.update');
        Route::delete('/settings/levels/priority/{priorityLevel}', [SettingController::class, 'destroyPriorityLevel'])->name('settings.levels.priority.destroy');
        Route::patch('/settings/levels/order', [SettingController::class, 'updateOrder'])->name('settings.levels.order');
        Route::patch('/team/wages/batch-update', [SettingController::class, 'batchUpdateMemberWageStandards'])->name('settings.team.wages.batch-update');
        Route::patch('/settings/team/{user}/wage', [SettingController::class, 'updateMemberWageStandard'])->name('settings.team.wage.update');
        Route::post('/settings/files', [SettingController::class, 'storeProjectFile'])->name('settings.files.store');
        Route::delete('/settings/files/{projectFile}', [SettingController::class, 'destroyProjectFile'])->name('settings.files.destroy');

        Route::post('/team/invite', [ProjectApplicationController::class, 'inviteWorker'])->name('team.invite');
        Route::patch('/team/invitations/{user}/status', [ProjectApplicationController::class, 'updateInvitationStatus'])->name('invitations.updateStatus');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    });

    Route::resource('tasks', TaskController::class)->except(['index', 'show']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::patch('/tasks/order', [TaskController::class, 'updateOrder'])->name('tasks.updateOrder');
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('tasks.batchUpdate');
    Route::post('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');
    Route::get('/tasks/{task}/details', [TaskController::class, 'show'])->name('tasks.show.details');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::get('/tasks/{task}/history', [TaskController::class, 'getHistory'])->name('tasks.history');
    Route::patch('/tasks/{task}/update-progress', [TaskController::class, 'updateProgress'])->name('tasks.updateProgress');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');
});

// ---------------------------------------------------------------------
// Rute Admin (DIPROTEKSI DENGAN MIDDLEWARE 'admin')
// ---------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('users.store');
    Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::get('/projects', [AdminProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects/{id}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('projects.toggleStatus');
});


// ---------------------------------------------------------------------
// Rute CEO (DIPROTEKSI DENGAN MIDDLEWARE 'ceo')
// ---------------------------------------------------------------------
Route::middleware(['auth', 'ceo'])->prefix('ceo')->name('ceo.')->group(function () {
    Route::get('/dashboard', [ceoDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [ceoListUserController::class, 'index'])->name('user_list');
    Route::get('/projects', [ceoListProjectController::class, 'index'])->name('project_list');
    // Note: this route seems out of place, but keeping it as is for now.
    // It should probably be /ceo/projects/{project}/kanban
    Route::get('/projects/{project}/kanban', [TaskController::class, 'kanban'])->name('kanban');
});

require_once __DIR__ . '/auth.php';