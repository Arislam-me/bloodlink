<?php

namespace Tests\Feature;

use App\Filament\Resources\DonationHistories\Pages\ManageDonationHistories;
use App\Models\DonationHistory;
use App\Models\User;
use App\Notifications\DonorAvailableAgain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class DonationCooldownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
        $this->travelTo(now()->setDate(2026, 11, 19)->setTime(10, 0));
    }

    public function test_logging_a_donation_makes_donor_unavailable_for_four_months(): void
    {
        $profile = $this->donor('O+');
        $this->assertTrue($profile->is_available);

        $this->actingAs($profile->user)->post('/donations', ['donated_on' => '2026-11-18'])
            ->assertSessionHas('success', fn ($m) => str_contains($m, '18 মার্চ 2027'));

        $profile->refresh();
        $this->assertFalse($profile->is_available);
        $this->assertTrue($profile->auto_unavailable);
        $this->assertSame('2027-03-18', $profile->available_from->toDateString());
    }

    public function test_an_old_donation_does_not_trigger_the_cooldown(): void
    {
        $profile = $this->donor('O+');

        $this->actingAs($profile->user)->post('/donations', ['donated_on' => '2026-05-01']);

        $this->assertTrue($profile->fresh()->is_available);
        $this->assertNull($profile->fresh()->available_from);
    }

    public function test_registration_with_recent_donation_starts_unavailable(): void
    {
        $profile = $this->donor('A+', ['last_donation_date' => '2026-10-01']);

        $this->assertFalse($profile->fresh()->is_available);
        $this->assertSame('2027-02-01', $profile->fresh()->available_from->toDateString());
    }

    public function test_availability_is_automatic_and_cannot_be_changed_by_the_donor(): void
    {
        $profile = $this->donor('O+', ['last_donation_date' => '2026-11-01']);

        // The settings endpoint was removed: there is no manual availability toggle.
        $this->actingAs($profile->user)->patch('/profile/settings', ['is_available' => 1])->assertNotFound();

        $this->assertFalse($profile->fresh()->is_available);
        $this->actingAs($profile->user)->get('/dashboard')->assertSee('রক্তদানের পর বিশ্রামে আছেন');
    }

    public function test_scheduler_restores_availability_and_notifies_donor(): void
    {
        Notification::fake();
        $profile = $this->donor('O+', ['last_donation_date' => '2026-11-01']);

        // Day before the cooldown ends: still resting.
        $this->travelTo(now()->setDate(2027, 2, 28));
        $this->artisan('bloodlink:restore-availability')->assertSuccessful();
        $this->assertFalse($profile->fresh()->is_available);

        $this->travelTo(now()->setDate(2027, 3, 1));
        $this->artisan('bloodlink:restore-availability')->assertSuccessful();

        $profile->refresh();
        $this->assertTrue($profile->is_available);
        $this->assertFalse($profile->auto_unavailable);
        $this->assertNull($profile->available_from);
        Notification::assertSentTo($profile->user, DonorAvailableAgain::class);
    }

    public function test_scheduler_leaves_donors_who_chose_unavailable_alone(): void
    {
        $profile = $this->donor('O+', ['is_available' => false]); // manual choice, no donation

        $this->travelTo(now()->addYear());
        $this->artisan('bloodlink:restore-availability');

        $this->assertFalse($profile->fresh()->is_available);
    }

    public function test_donor_requests_removal_and_only_admin_deletes_which_lifts_the_cooldown(): void
    {
        $profile = $this->donor('O+');
        $this->actingAs($profile->user)->post('/donations', ['donated_on' => '2026-11-18']);
        $donation = DonationHistory::where('user_id', $profile->user_id)->firstOrFail();

        // Donors can no longer delete entries directly.
        $this->actingAs($profile->user)->delete('/donations/'.$donation->id)->assertNotFound();

        $this->actingAs($profile->user)->post(route('donations.removal-request', $donation), ['reason' => 'ভুল তারিখ দিয়েছিলাম'])
            ->assertSessionHas('success');
        $this->assertNotNull($donation->fresh()->removal_requested_at);
        $this->assertFalse($profile->fresh()->is_available, 'entry stays until an admin approves');

        // A second request for the same entry, or a request on someone else's entry, is refused.
        $this->actingAs($profile->user)->post(route('donations.removal-request', $donation), ['reason' => 'আবার'])->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('donations.removal-request', $donation), ['reason' => 'অন্যের'])->assertForbidden();

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(ManageDonationHistories::class)->callTableAction('approve_removal', $donation);

        $this->assertModelMissing($donation);
        $profile->refresh();
        $this->assertTrue($profile->is_available);
        $this->assertNull($profile->available_from);
    }

    public function test_resting_donors_are_hidden_from_available_search(): void
    {
        $this->donor('B-', ['last_donation_date' => '2026-11-10'], null, ['name' => 'Resting Rahman']);

        $this->get('/donors?availability=available')->assertDontSee('Resting R.');
        $this->get('/donors')->assertSee('Resting R.'); // no default filter: all donors are listed
        $this->get('/donors?availability=any')->assertSee('Resting R.')->assertSee('বিশ্রামে আছেন');
    }
}
