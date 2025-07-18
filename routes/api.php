<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectApplicationController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WageStandardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ceo\ceoDashboardController;
use App\Http\Controllers\ceo\ceoListProjectController;
use App\Http\Controllers\ceo\ceoListUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// IT-01, IT-02: Public Login
Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    if ($user->status === 'blocked') {
        return response()->json(['message' => 'Your account has been blocked.'], 403);
    }
    $token = $user->createToken('api-token-for-'.$user->email)->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user->only(['id', 'name', 'email', 'role'])]);
})->name('api.login');


// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // IT-03: Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('api.logout');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('api.profile.show'); // IT-44
    Route::patch('/profile', [ProfileController::class, 'update'])->name('api.profile.update'); // IT-45

    // Project Routes
    Route::apiResource('projects', ProjectController::class)->except(['create', 'edit']); // IT-04, IT-05, IT-06, IT-07
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('api.projects.my-projects'); // IT-40
    Route::get('/projects/{project}/dashboard', [ProjectController::class, 'projectDashboard'])->name('api.projects.dashboard'); // IT-37, IT-38
    Route::get('/projects/{project}/activity', [ActivityController::class, 'index'])->name('api.projects.activity'); // IT-15, IT-16
    Route::get('/projects/{project}/team', [ProjectController::class, 'teamMembers'])->name('api.projects.team'); // IT-42
    Route::post('/projects/{project}/team/invite', [ProjectApplicationController::class, 'inviteWorker'])->name('api.projects.team.invite'); // IT-41
    Route::patch('/projects/{project}/team/invitations/{user}/status', [ProjectApplicationController::class, 'updateInvitationStatus'])->name('api.projects.invitations.updateStatus'); // IT-43
    Route::patch('/projects/{project}/status', [ProjectController::class, 'update'])->name('api.projects.status.update'); // IT-31

    // Task Routes
    Route::post('/tasks/search', [TaskController::class, 'search'])->name('api.tasks.search'); // IT-11
    Route::post('/tasks/batch-update', [TaskController::class, 'batchUpdate'])->name('api.tasks.batchUpdate');
    Route::apiResource('tasks', TaskController::class)->except(['index']); // IT-08, IT-09, IT-10
    Route::get('/tasks/{task}/details', [TaskController::class, 'show'])->name('api.tasks.details');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('api.tasks.comments.store'); // IT-13
    Route::patch('/tasks/{task}/status', [TaskController::class, 'update'])->name('api.tasks.status.update'); // IT-14
    Route::get('/projects/{project}/tasks/recap', [TaskController::class, 'recap'])->name('api.tasks.recap.download'); // IT-12

    // Payroll and Payslip Routes
    Route::get('/projects/{project}/payroll/calculate', [PaymentController::class, 'showPayrollCalculation'])->name('api.payroll.calculate'); // IT-20
    Route::get('/projects/{project}/payslips', [PaymentController::class, 'payslipList'])->name('api.payslips.index'); // IT-22, IT-23
    Route::post('/projects/{project}/payslips', [PaymentController::class, 'storePayslip'])->name('api.payslips.store'); // IT-17, IT-18, IT-19
    Route::get('/projects/{project}/payslips/{payslip}', [PaymentController::class, 'showPayslipDetail'])->name('api.payslips.show'); // IT-21
    Route::post('/projects/{project}/payslips/{payslip}/approve', [PaymentController::class, 'approvePayslip'])->name('api.payslips.approve'); // IT-24 is approval, not settings
    Route::get('/projects/{project}/payslips/{payslip}/download', [PaymentController::class, 'downloadPayslip'])->name('api.payslips.download'); // IT-25

    // Wage Standards and Settings
    Route::post('/projects/{project}/wage-standards', [WageStandardController::class, 'store'])->name('api.wage-standards.store'); // IT-26
    Route::patch('/projects/{project}/team/wages/batch-update', [SettingController::class, 'batchUpdateMemberWageStandards'])->name('api.settings.team.wages.batch-update'); // IT-27
    Route::patch('/projects/{project}/settings/payment-type', [SettingController::class, 'updatePaymentCalculationType'])->name('api.settings.payment.update'); // IT-28
    Route::get('/projects/{project}/settings/weights', [SettingController::class, 'editWeights'])->name('api.settings.weights.show'); // IT-29
    Route::patch('/projects/{project}/settings/weights', [SettingController::class, 'updateWeights'])->name('api.settings.weights.update'); // IT-30

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index'); // IT-32, IT-33, IT-34, IT-35

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // IT-39
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index'); // IT-49
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store'); // IT-46
        Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus'); // IT-47, IT-48
        Route::post('/projects/{id}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('projects.toggleStatus'); // IT-50, IT-51
    });

    // CEO Routes
    Route::middleware('ceo')->prefix('ceo')->name('api.ceo.')->group(function () {
        Route::get('/users', [ceoListUserController::class, 'index'])->name('users.index'); // IT-52
        Route::get('/projects', [ceoListProjectController::class, 'index'])->name('projects.index'); // IT-53, IT-55
        Route::get('/projects/{project}/kanban', [TaskController::class, 'kanban'])->name('projects.kanban'); // IT-54
    });
});