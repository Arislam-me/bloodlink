<?php

namespace Tests\Feature;

use App\Enums\EmergencyStatus;
use App\Models\EmergencyRequest;
use App\Models\Upazila;
use App\Models\User;
use App\Notifications\EmergencyBloodRequestNotification;
use App\Notifications\EmergencyResponseReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmergencyRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
        Notification::fake();
    }

    private function payload(array $overrides = []): array
    {
        return [
            'blood_group_id' => $this->bloodGroup('A+')->id,
            'hospital_name' => 'Dhaka Medical College Hospital',
            'hospital_address' => 'Ward 5, Bakshibazar',
            'division_id' => $this->division->id,
            'district_id' => $this->district->id,
            'upazila_id' => $this->upazila->id,
            'latitude' => '23.7257',
            'longitude' => '90.3976',
            'units_needed' => 2,
            'needed_at' => now()->addHours(10)->format('Y-m-d H:i'),
            'contact_phone' => '01811111111',
            'details' => 'Surgery tomorrow',
            'confirm_genuine' => '1',
            ...$overrides,
        ];
    }

    public function test_verified_requester_goes_live_and_notifies_compatible_nearby_donors_only(): void
    {
        $requester = User::factory()->verifiedDonor()->create();

        $compatibleSameThana = $this->donor('O-');                           // O- can give to A+
        $exactOtherThana = $this->donor('A+', [], $this->otherUpazila);       // same district
        $incompatible = $this->donor('B+');                                   // B+ cannot give to A+
        $unavailable = $this->donor('A+', ['is_available' => false]);
        $recentDonor = $this->donor('A+', ['last_donation_date' => now()->subDays(20)]);
        $otherDistrict = $this->donor('A+', [], Upazila::where('name', 'Gazipur Sadar')->first());

        $this->actingAs($requester)->post('/emergency', $this->payload())->assertRedirect();

        $request = EmergencyRequest::firstOrFail();
        $this->assertSame(EmergencyStatus::Active, $request->status);
        $this->assertSame($this->district->id, $request->district_id);
        $this->assertSame(2, $request->notified_count);

        Notification::assertSentTo([$compatibleSameThana->user, $exactOtherThana->user], EmergencyBloodRequestNotification::class);
        foreach ([$incompatible, $unavailable, $recentDonor, $otherDistrict] as $p) {
            Notification::assertNotSentTo($p->user, EmergencyBloodRequestNotification::class);
        }
        Notification::assertNotSentTo($requester, EmergencyBloodRequestNotification::class);
    }

    public function test_requests_from_unverified_members_are_auto_approved(): void
    {
        $requester = User::factory()->create(); // phone verified but not admin-verified
        $donor = $this->donor('A+');

        $this->actingAs($requester)->post('/emergency', $this->payload());
        $request = EmergencyRequest::firstOrFail();

        $this->assertSame(EmergencyStatus::Active, $request->status);
        Notification::assertSentTo($donor->user, EmergencyBloodRequestNotification::class);

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('emergency.show', $request))->assertOk();
        $this->actingAs($stranger)->get('/emergency')->assertSee('Dhaka Medical College Hospital');
    }

    public function test_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/emergency', $this->payload([
            'needed_at' => now()->subHour()->format('Y-m-d H:i'),
            'units_needed' => 50,
            'latitude' => '51.5', 'longitude' => '-0.12',  // London
            'contact_phone' => null,
            'confirm_genuine' => null,
        ]))->assertSessionHasErrors(['needed_at', 'units_needed', 'latitude', 'longitude', 'contact_phone', 'confirm_genuine']);
    }

    public function test_requires_verified_mobile(): void
    {
        $user = User::factory()->create(['phone_verified_at' => null]);
        $this->actingAs($user)->post('/emergency', $this->payload())->assertRedirect(route('phone.verify.notice'));
        $this->assertDatabaseCount('emergency_requests', 0);
    }

    public function test_only_owner_or_admin_can_fulfil_or_cancel(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        $this->actingAs($owner)->post('/emergency', $this->payload());
        $request = EmergencyRequest::firstOrFail();

        $this->actingAs(User::factory()->create())->post(route('emergency.cancel', $request))->assertForbidden();

        $this->actingAs($owner)->post(route('emergency.fulfil', $request))->assertSessionHas('success');
        $this->assertSame(EmergencyStatus::Fulfilled, $request->fresh()->status);

        // Closed requests can't be changed again.
        $this->actingAs($owner)->post(route('emergency.cancel', $request))->assertForbidden();
    }

    public function test_donor_response_notifies_requester_once(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        $this->actingAs($owner)->post('/emergency', $this->payload());
        $request = EmergencyRequest::firstOrFail();
        $donor = $this->donor('A+');

        $this->actingAs($donor->user)->post(route('emergency.respond', $request), ['message' => 'On my way'])->assertSessionHas('success');
        $this->actingAs($donor->user)->post(route('emergency.respond', $request));

        $this->assertSame(1, $request->responses()->count());
        Notification::assertSentToTimes($owner, EmergencyResponseReceived::class, 1);
        $this->actingAs($owner)->post(route('emergency.respond', $request))->assertForbidden();
    }

    public function test_open_request_limit_per_user(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        foreach (range(1, 3) as $i) {
            $this->actingAs($owner)->post('/emergency', $this->payload(['hospital_name' => "Hospital $i"]));
        }
        $this->actingAs($owner)->post('/emergency', $this->payload())->assertSessionHasErrors('blood_group_id');
        $this->assertDatabaseCount('emergency_requests', 3);
    }

    public function test_newest_request_is_listed_first(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        $this->actingAs($owner)->post('/emergency', $this->payload(['hospital_name' => 'Older Hospital', 'needed_at' => now()->addHours(3)->format('Y-m-d H:i')]));
        $this->travel(5)->minutes();
        $this->actingAs($owner)->post('/emergency', $this->payload(['hospital_name' => 'Newer Hospital', 'needed_at' => now()->addDays(3)->format('Y-m-d H:i')]));

        $this->get('/emergency')->assertSeeInOrder(['Newer Hospital', 'Older Hospital']);
    }

    public function test_contact_phone_is_shown_only_to_phone_verified_members(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        $this->actingAs($owner)->post('/emergency', $this->payload(['contact_phone' => '01822222222']));
        $request = EmergencyRequest::firstOrFail();

        $this->actingAs(User::factory()->create())->get(route('emergency.show', $request))->assertSee('01822222222');
        $this->actingAs(User::factory()->create(['phone_verified_at' => null]))->get(route('emergency.show', $request))->assertDontSee('01822222222');
    }

    public function test_expire_command_closes_overdue_requests(): void
    {
        $owner = User::factory()->verifiedDonor()->create();
        $this->actingAs($owner)->post('/emergency', $this->payload());
        $request = EmergencyRequest::firstOrFail();

        $this->travelTo($request->expires_at->copy()->addMinute());
        $this->artisan('bloodlink:expire')->assertSuccessful();

        $this->assertSame(EmergencyStatus::Expired, $request->fresh()->status);
    }
}
