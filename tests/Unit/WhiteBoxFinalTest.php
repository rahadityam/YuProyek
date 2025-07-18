<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Payment;
use App\Models\WageStandard;
use App\Models\ProjectUser;
use App\Models\PaymentTerm;
use App\Notifications\OverdueTerminPaymentNotification;
use App\Notifications\UserInvitedToProjectNotification;
use App\Notifications\ProfileUpdatedNotification;
use App\Notifications\WipLimitExceededNotification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;

class WhiteBoxFinalTest extends TestCase
{
    use RefreshDatabase;

    private User $pm;
    private User $worker;
    private User $admin;
    private User $ceo;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->pm = User::factory()->create(['role' => 'project_owner']);
        $this->worker = User::factory()->create(['role' => 'worker']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->ceo = User::factory()->create(['role' => 'ceo']);
        $this->project = Project::factory()->create(['owner_id' => $this->pm->id]);
    }

    #[Test]
    public function utw_01_fr_02_authentication_and_authorization_by_role(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($this->ceo)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->pm)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->worker)->get(route('admin.dashboard'))->assertForbidden();
    }

    #[Test]
    public function utw_02_fr_05_project_financials_are_locked_after_payment(): void
    {
        $this->actingAs($this->pm)->patch(route('projects.pengaturan.info.update', $this->project), [
            'name' => 'New Name Before Lock', 'budget' => 2000000,
            'description' => $this->project->description, 'status' => 'in_progress',
            'start_date' => $this->project->start_date->format('Y-m-d'), 'end_date' => $this->project->end_date->format('Y-m-d'),
        ])->assertSessionHasNoErrors()->assertRedirect();
        
        Payment::factory()->create(['project_id' => $this->project->id, 'status' => 'approved']);
        
        $this->actingAs($this->pm)->patch(route('projects.pengaturan.info.update', $this->project), [
            'name' => 'Attempted Name Change', 'budget' => 9999999,
            'description' => 'Final description.', 'status' => 'completed',
        ])->assertSessionHasNoErrors()->assertRedirect();
        
        $this->project->refresh();
        $this->assertEquals('Attempted Name Change', $this->project->name);
        $this->assertEquals(2000000, $this->project->budget);
    }
    
    #[Test]
    public function utw_03_fr_07_visual_progress_display(): void
    {
        Task::factory(3)->create(['project_id' => $this->project->id, 'status' => 'Done']);
        Task::factory(7)->create(['project_id' => $this->project->id, 'status' => 'In Progress']);

        $response = $this->actingAs($this->pm)->get(route('projects.dashboard', $this->project));
        
        $response->assertOk();
        $response->assertViewHas('taskStats', function ($stats) {
            return $stats['total'] === 10 && $stats['done'] === 3;
        });
    }

    #[Test]
    public function utw_04_fr_08_create_new_task(): void
    {
        $taskData = [
            'title' => 'Design Landing Page', 'description' => 'A new task.',
            'project_id' => $this->project->id, 'status' => 'To Do', 'assigned_to' => $this->worker->id,
        ];
        $this->actingAs($this->pm)->postJson(route('tasks.store'), $taskData)->assertOk();
        $this->assertDatabaseHas('tasks', ['title' => 'Design Landing Page']);
    }

    #[Test]
    public function utw_05_fr_10_delete_task(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id, 'payment_id' => null]);
        $this->actingAs($this->pm)->delete(route('tasks.destroy', $task));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    #[Test]
    public function utw_06_fr_15_activity_log(): void
    {
        $this->actingAs($this->pm);
        ActivityLogger::log('created', $this->project->id, 'Project was created', $this->project);
        $this->assertDatabaseHas('activity_logs', ['description' => 'Project was created']);
        $this->get(route('projects.activity', $this->project))->assertSee('Project was created');
    }

    #[Test]
    public function utw_07_fr_17_create_payslip_draft(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id, 'assigned_to' => $this->worker->id, 'status' => 'Done']);
        $payslipData = [ 'user_id' => $this->worker->id, 'payment_type' => 'task', 'payment_name' => 'Gaji Task', 'task_ids' => [$task->id] ];
        $this->actingAs($this->pm)->postJson(route('projects.payslips.store', $this->project), $payslipData)->assertOk();
        $this->assertDatabaseHas('payments', ['payment_name' => 'Gaji Task', 'status' => 'draft']);
    }
    
    #[Test]
    public function utw_08_fr_18_and_19_payslip_logic_adapts_to_payment_type(): void
    {
        $projectTermin = Project::factory()->create(['owner_id' => $this->pm->id, 'payment_calculation_type' => 'termin']);
        $term = PaymentTerm::factory()->create(['project_id' => $projectTermin->id, 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $task = Task::factory()->create(['project_id' => $projectTermin->id, 'assigned_to' => $this->worker->id, 'status' => 'Done', 'updated_at' => now()]);
        
        $this->actingAs($this->pm)->postJson(route('projects.payslips.store', $projectTermin), [
            'user_id' => $this->worker->id, 'payment_type' => 'termin', 'payment_term_id' => $term->id,
            'payment_name' => 'Gaji Termin', 'task_ids' => [$task->id],
        ])->assertOk();
        $this->assertDatabaseHas('payments', ['payment_name' => 'Gaji Termin', 'payment_term_id' => $term->id]);

        $projectFull = Project::factory()->create(['owner_id' => $this->pm->id, 'payment_calculation_type' => 'full']);
        $this->actingAs($this->pm)->postJson(route('projects.payslips.store', $projectFull), [
            'user_id' => $this->worker->id, 'payment_type' => 'full', 'payment_name' => 'Gaji Penuh', 'amount' => 500000,
        ])->assertOk();
        $this->assertDatabaseHas('payments', ['payment_name' => 'Gaji Penuh', 'amount' => 500000]);
    }

    #[Test]
    public function utw_09_fr_20_task_value_is_calculated_correctly_with_wsm(): void
    {
        $project = Project::factory()->create(['difficulty_weight' => 70, 'priority_weight' => 30]);
        $wage = WageStandard::factory()->create(['project_id' => $project->id, 'task_price' => 50000]);
        ProjectUser::factory()->create(['project_id' => $project->id, 'user_id' => $this->worker->id, 'wage_standard_id' => $wage->id]);
        $difficulty = $project->difficultyLevels()->where('value', 8)->first();
        $priority = $project->priorityLevels()->where('value', 6)->first();
        $task = Task::factory()->create(['project_id' => $project->id, 'assigned_to' => $this->worker->id, 'difficulty_level_id' => $difficulty->id, 'priority_level_id' => $priority->id, 'achievement_percentage' => 95]);

        $task->refresh();
        $this->assertEquals(7.03, $task->wsm_score);
        $this->assertEquals(351500, $task->calculated_value);
    }
    
    #[Test]
    public function utw_10_fr_21_payslip_detail_page_is_authorized_and_shows_data(): void
    {
        $otherWorker = User::factory()->create();
        $payslip = Payment::factory()->create(['project_id' => $this->project->id, 'user_id' => $this->worker->id]);

        $this->actingAs($this->pm)->get(route('projects.payslips.show', [$this->project, $payslip]))->assertOk();
        $this->actingAs($this->worker)->get(route('projects.payslips.show', [$this->project, $payslip]))->assertOk();
        $this->actingAs($otherWorker)->get(route('projects.payslips.show', [$this->project, $payslip]))->assertForbidden();
    }

    #[Test]
    public function utw_11_fr_26_manage_and_lock_wage_standards(): void
    {
        // Skenario 1: PM bisa membuat standar upah jika proyek belum terkunci
        $this->actingAs($this->pm)->post(route('projects.wage-standards.store', $this->project), [
            'job_category' => 'Senior Developer', 'task_price' => 150000,
        ])->assertRedirect();
        $this->assertDatabaseHas('wage_standards', ['job_category' => 'Senior Developer']);
        
        $wageStandard = WageStandard::first();
        
        // Skenario 2: PM tidak bisa menghapus standar upah jika proyek sudah terkunci
        Payment::factory()->create(['project_id' => $this->project->id, 'status' => 'approved']);
        $this->actingAs($this->pm)->delete(route('projects.wage-standards.destroy', [$this->project, $wageStandard]));
        $this->assertDatabaseHas('wage_standards', ['id' => $wageStandard->id]);
    }

    #[Test]
    public function utw_12_fr_28_payment_type_cannot_be_changed_after_payment_approved(): void
    {
        $project = Project::factory()->create([
            'owner_id' => $this->pm->id, 
            'payment_calculation_type' => 'task'
        ]);
        
        Payment::factory()->create(['project_id' => $project->id, 'status' => 'approved']);
        
        // Coba ubah payment type - seharusnya gagal atau diabaikan
        $response = $this->actingAs($this->pm)->patch(route('projects.pengaturan.payment.update', $project), [
            'payment_calculation_type' => 'termin'
        ]);
        
        // Bisa jadi ada error validation atau request berhasil tapi data tidak berubah
        $project->refresh();
        $this->assertEquals('task', $project->payment_calculation_type);
    }

    #[Test]
    public function utw_13_fr_30_pm_can_update_wsm_weights(): void
    {
        $this->actingAs($this->pm)->patch(route('projects.settings.weights.update', $this->project), ['difficulty_weight' => 70, 'priority_weight' => 30]);
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'difficulty_weight' => 70, 'priority_weight' => 30]);
    }
    
    #[Test]
    public function utw_14_fr_35_ceo_is_notified_of_overdue_payment(): void
    {
        $overdueTerm = PaymentTerm::factory()->create([
            'project_id' => $this->project->id, 
            'end_date' => Carbon::yesterday()->subDay()
        ]);
        
        // Buat payment yang terkait dengan term ini
        Payment::factory()->create([
            'project_id' => $this->project->id,
            'payment_term_id' => $overdueTerm->id,
            'status' => 'pending' // atau 'draft'
        ]);
        
        $this->artisan('app:check-overdue-termins');
        
        Notification::assertSentTo($this->ceo, OverdueTerminPaymentNotification::class);
    }
    
    #[Test]
    public function utw_15_fr_41_and_34_pm_can_invite_worker_to_project(): void
    {
        $this->actingAs($this->pm)->postJson(route('projects.team.invite', $this->project), ['email' => $this->worker->email]);
        Notification::assertSentTo($this->worker, UserInvitedToProjectNotification::class);
    }

    #[Test]
    public function utw_16_fr_33_and_34_pm_is_notified_when_wip_limit_is_exceeded(): void
    {
        $projectWithWip = Project::factory()->create(['owner_id' => $this->pm->id, 'wip_limits' => 1]);
        ProjectUser::factory()->create(['project_id' => $projectWithWip->id, 'user_id' => $this->worker->id, 'status' => 'accepted']);
        Task::factory()->create(['project_id' => $projectWithWip->id, 'assigned_to' => $this->worker->id, 'status' => 'In Progress']);
        $taskToMove = Task::factory()->create(['project_id' => $projectWithWip->id, 'assigned_to' => $this->worker->id, 'status' => 'To Do']);
        $this->actingAs($this->worker)->postJson(route('tasks.batchUpdate'), ['project_id' => $projectWithWip->id, 'data' => ['In Progress' => [['id' => $taskToMove->id, 'order' => 0]]]]);
        Notification::assertSentTo($projectWithWip->owner, WipLimitExceededNotification::class);
    }
    
    #[Test]
    public function utw_17_fr_36_user_is_notified_on_profile_update(): void
    {
        $this->project->workers()->attach($this->worker->id, ['status' => 'accepted']);
        $this->actingAs($this->worker)->patch(route('profile.update'), ['name' => 'New Name', 'email' => $this->worker->email]);
        Notification::assertSentTo($this->worker, ProfileUpdatedNotification::class);
        Notification::assertSentTo($this->pm, ProfileUpdatedNotification::class);
    }

    #[Test]
    public function utw_18_fr_37_38_39_dashboards_show_visual_data(): void
    {
        $responsePm = $this->actingAs($this->pm)->get(route('projects.dashboard', $this->project));
        $responsePm->assertOk()->assertViewHas('isOwner', true);
        
        $responseWorker = $this->actingAs($this->worker)->get(route('projects.dashboard', $this->project));
        $responseWorker->assertOk()->assertViewHas('isOwner', false);
        
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $responseAdmin->assertOk()->assertViewHas('totalUsers');
    }

    #[Test]
    public function utw_19_fr_43_worker_can_accept_and_decline_invitation(): void
    {
        ProjectUser::factory()->create(['project_id' => $this->project->id, 'user_id' => $this->worker->id, 'status' => 'invited']);
        $this->actingAs($this->worker)->patch(route('projects.invitations.updateStatus', [$this->project, $this->worker]), ['action' => 'accept']);
        $this->assertDatabaseHas('project_users', ['user_id' => $this->worker->id, 'status' => 'accepted']);
    }

    #[Test]
    public function utw_20_fr_47_and_48_admin_can_toggle_user_status(): void
    {
        $this->actingAs($this->admin)->post(route('admin.users.toggleStatus', $this->worker->id));
        $this->assertDatabaseHas('users', ['id' => $this->worker->id, 'status' => 'blocked']);
    }

    #[Test]
    public function utw_21_fr_50_and_51_admin_can_toggle_project_status(): void
    {
        $this->actingAs($this->admin)->post(route('admin.projects.toggleStatus', $this->project->id));
        $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'status' => 'blocked']);
    }

    #[Test]
    public function utw_22_fr_29_wsm_criteria_is_displayed_in_settings(): void
    {
        $this->actingAs($this->pm)->get(route('projects.pengaturan', $this->project))
            ->assertOk()
            ->assertSee('Bobot Kesulitan');
    }
}