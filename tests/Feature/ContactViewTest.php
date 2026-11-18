<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\DonorProfileView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Livewire\Livewire;
use Tests\TestCase;

class ContactViewTest extends TestCase
{
    use RefreshDatabase;

    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReference();
        $this->viewer = User::factory()->create()->fresh();
        $this->travelTo(now()->setDate(2026, 11, 15)->setTime(12, 0));
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function unlock($donor)
    {
        return $this->actingAs($this->viewer)->post(route('donors.unlock', $donor));
    }

    public function test_unlocking_reveals_name_phone_and_social_links(): void
    {
        $donor = $this->donor('A+', ['facebook_url' => 'https://www.facebook.com/nusrat.j', 'whatsapp_number' => '01955555555'], null,
            ['name' => 'Nusrat Jahan', 'phone' => '01733333333']);

        $this->actingAs($this->viewer)->get(route('donors.show', $donor))
            ->assertDontSee('Nusrat Jahan')->assertDontSee('01733333333')->assertDontSee('nusrat.j');

        $this->unlock($donor)->assertRedirect(route('donors.show', $donor));
        $this->actingAs($this->viewer)->get(route('donors.show', $donor))
            ->assertSee('Nusrat Jahan')->assertSee('01733333333')
            ->assertSee('https://www.facebook.com/nusrat.j')->assertSee('https://wa.me/8801955555555')
            ->assertSee('সতর্কীকরণ');
    }

    public function test_age_and_weight_are_shown_only_after_opening_and_birth_date_never(): void
    {
        $donor = $this->donor('B+', ['date_of_birth' => '1996-11-15', 'weight_kg' => 67]); // 30 years old on 2026-11-15

        $this->actingAs($this->viewer)->get(route('donors.show', $donor))->assertDontSee('30 বছর')->assertDontSee('67 কেজি');

        $this->unlock($donor);
        $this->actingAs($this->viewer)->get(route('donors.show', $donor))
            ->assertSee('30 বছর')->assertSee('67 কেজি')->assertDontSee('1996-11-15');
    }

    public function test_there_is_no_limit_on_opening_numbers(): void
    {
        $donors = collect(range(1, 15))->map(fn () => $this->donor());

        $donors->each(fn ($d) => $this->unlock($d)->assertSessionHas('success'));

        $this->actingAs($this->viewer)->get(route('donors.show', $donors->last()))->assertSee($donors->last()->user->phone);
        $this->assertSame(15, DonorProfileView::where('viewer_id', $this->viewer->id)->count());
    }

    public function test_reopening_the_same_donor_is_counted_once(): void
    {
        $donor = $this->donor();
        $this->unlock($donor);
        $this->unlock($donor);

        $this->assertSame(1, DonorProfileView::where('viewer_id', $this->viewer->id)->count());
    }

    public function test_admins_are_notified_once_when_a_member_passes_fifty_numbers_in_a_month(): void
    {
        config(['bloodlink.view_alert_threshold' => 3]);
        $admin = User::factory()->admin()->create();
        $donors = collect(range(1, 6))->map(fn () => $this->donor());

        $donors->take(3)->each(fn ($d) => $this->unlock($d));
        $this->assertSame(0, $admin->notifications()->count(), 'no alert at the threshold itself');

        $this->unlock($donors[3]);
        $this->assertSame(1, $admin->notifications()->count(), 'alert once the threshold is passed');
        $this->assertStringContainsString($this->viewer->name, $admin->notifications()->first()->data['title']);

        $this->unlock($donors[4]);
        $this->unlock($donors[5]);
        $this->assertSame(1, $admin->notifications()->count(), 'only one alert per member per month');
    }

    public function test_admin_users_table_shows_numbers_viewed(): void
    {
        $donors = collect(range(1, 2))->map(fn () => $this->donor());
        $donors->each(fn ($d) => $this->unlock($d));

        // A view from last month counts toward the total but not this month.
        DonorProfileView::create(['viewer_id' => $this->viewer->id, 'donor_profile_id' => $this->donor()->id, 'period' => '2026-10']);

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(ListUsers::class)
            ->assertTableColumnStateSet('numbers_viewed_this_month', 2, $this->viewer)
            ->assertTableColumnStateSet('numbers_viewed_total', 3, $this->viewer);
    }

    public function test_unverified_members_cannot_open_numbers(): void
    {
        $unverified = User::factory()->create(['phone_verified_at' => null]);

        $this->actingAs($unverified)->post(route('donors.unlock', $this->donor()))->assertRedirect(route('phone.verify.notice'));
        $this->assertDatabaseCount('donor_profile_views', 0);
    }

    public function test_guests_cannot_open_numbers(): void
    {
        $this->post(route('donors.unlock', $this->donor()))->assertRedirect(route('login'));
    }
}
