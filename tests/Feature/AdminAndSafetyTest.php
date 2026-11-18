<?php

namespace Tests\Feature;

use App\Enums\EmergencyStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\EmergencyRequests\Pages\ListEmergencyRequests;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\DataDeletionRequest;
use App\Models\EmergencyRequest;
use App\Models\Report;
use App\Models\User;
use App\Services\DataDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAndSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
    }

    public function test_only_active_admins_can_access_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
    }

    public function test_admin_resources_render(): void
    {
        $admin = User::factory()->admin()->create();
        $this->donor();

        foreach (['users', 'donor-profiles', 'emergency-requests', 'contact-requests', 'donation-histories', 'reports', 'donor-profile-views', 'data-deletion-requests', 'divisions', 'districts', 'upazilas', 'areas', 'blood-groups'] as $path) {
            $this->actingAs($admin)->get("/admin/{$path}")->assertOk();
        }
    }

    public function test_admin_can_verify_and_suspend_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(ListUsers::class)->callTableAction('verify', $user);
        $this->assertNotNull($user->fresh()->verified_at);
        $this->assertSame($admin->id, $user->fresh()->verified_by);

        Livewire::test(ListUsers::class)->callTableAction('suspend', $user, data: ['reason' => 'Spam']);
        $this->assertSame(UserStatus::Suspended, $user->fresh()->status);
    }

    public function test_admin_approval_activates_request_and_notifies_donors(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $this->donor('A+');
        $request = EmergencyRequest::factory()->pending()->create([
            'blood_group_id' => $this->bloodGroup('A+')->id,
            'division_id' => $this->division->id, 'district_id' => $this->district->id, 'upazila_id' => $this->upazila->id,
        ]);

        $this->actingAs($admin);
        Livewire::test(ListEmergencyRequests::class)->callTableAction('approve', $request);

        $request->refresh();
        $this->assertSame(EmergencyStatus::Active, $request->status);
        $this->assertSame($admin->id, $request->approved_by);
        $this->assertSame(1, $request->notified_count);
    }

    public function test_users_can_report_donors_once_while_open(): void
    {
        $reporter = User::factory()->create();
        $donor = $this->donor();

        $this->actingAs($reporter)->post('/reports', ['type' => 'donor', 'target' => $donor->public_id, 'reason' => 'scam', 'details' => 'Asked for money'])
            ->assertSessionHas('success');
        $this->actingAs($reporter)->post('/reports', ['type' => 'donor', 'target' => $donor->public_id, 'reason' => 'spam'])
            ->assertSessionHas('warning');

        $report = Report::firstOrFail();
        $this->assertTrue($report->reportable->is($donor->user));
        $this->assertSame(1, Report::count());
    }

    public function test_deletion_request_hides_profile_and_admin_processing_removes_data(): void
    {
        $donor = $this->donor();
        $user = $donor->user;

        $this->actingAs($user)->post('/profile/deletion-request', ['type' => 'account', 'password' => 'password'])
            ->assertSessionHas('success');
        $this->assertFalse($donor->fresh()->is_listed);

        $request = DataDeletionRequest::firstOrFail();
        app(DataDeletionService::class)->process($request, User::factory()->admin()->create());

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('donor_profiles', ['id' => $donor->id]);
        $this->assertSame('completed', $request->fresh()->status);
    }

    public function test_personal_data_deletion_anonymizes_account(): void
    {
        $donor = $this->donor();
        $user = $donor->user;
        $request = DataDeletionRequest::create(['user_id' => $user->id, 'email' => $user->email, 'type' => 'personal_data']);

        app(DataDeletionService::class)->process($request, User::factory()->admin()->create());

        $user->refresh();
        $this->assertSame('Deleted User', $user->name);
        $this->assertStringEndsWith('@deleted.invalid', $user->email);
        $this->assertNull($user->donorProfile);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_core_public_pages_render(): void
    {
        foreach (['/', '/about', '/privacy-policy', '/terms', '/donors', '/emergency', '/login', '/register', '/forgot-password'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_authenticated_pages_render(): void
    {
        $user = $this->donor()->user;
        foreach (['/dashboard', '/profile', '/donations', '/notifications', '/emergency/create', '/emergency?scope=mine'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }
}
